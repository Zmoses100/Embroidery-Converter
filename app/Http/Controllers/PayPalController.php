<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\PaypalTransaction;
use App\Services\PayPalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayPalController extends Controller
{
    protected PayPalService $paypalService;

    public function __construct(PayPalService $paypalService)
    {
        $this->paypalService = $paypalService;
    }

    /**
     * Initiate PayPal subscription checkout.
     * 
     * This creates a subscription order in PayPal and redirects the user
     * to PayPal's approval page.
     */
    public function checkout(Request $request, Plan $plan)
    {
        $user = $request->user();
        $interval = $request->input('interval', 'monthly');

        // Validate request
        if ($plan->isFree()) {
            return redirect()->route('plans.index')
                ->withErrors(['plan' => 'Cannot use PayPal for free plans.']);
        }

        if (!in_array($interval, ['monthly', 'yearly'])) {
            return redirect()->route('plans.index')
                ->withErrors(['interval' => 'Invalid billing interval.']);
        }

        // Check PayPal configuration
        if (!config('services.paypal.client_id') || !config('services.paypal.secret')) {
            Log::error('PayPal not configured', ['user_id' => $user->id]);
            
            $errorMsg = 'PayPal payment is not available at this time.';
            if ($user->isAdmin()) {
                $errorMsg .= ' [Admin: Configure PAYPAL_CLIENT_ID and PAYPAL_CLIENT_SECRET in .env]';
            }
            
            return redirect()->route('plans.index')
                ->withErrors(['plan' => $errorMsg]);
        }

        // Create subscription order in PayPal
        $order = $this->paypalService->createSubscriptionOrder(
            $plan,
            $interval,
            route('paypal.success'),
            route('paypal.cancel')
        );

        if (!$order) {
            Log::error('PayPal subscription order creation failed', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
            ]);

            $errorMsg = 'Failed to start PayPal checkout. Please try again.';
            if ($user->isAdmin()) {
                $errorMsg .= ' [Check PayPal credentials and logs]';
            }

            return redirect()->route('plans.index')
                ->withErrors(['plan' => $errorMsg]);
        }

        // Store pending transaction record
        PaypalTransaction::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'subscription_id' => $order['subscription_id'],
            'billing_plan_id' => $plan->{"paypal_plan_id_" . $interval},
            'interval' => $interval,
            'amount' => $interval === 'yearly' ? $plan->price_yearly : $plan->price_monthly,
            'currency' => config('services.paypal.currency', 'USD'),
            'status' => 'pending',
        ]);

        Log::info('PayPal checkout initiated', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'subscription_id' => $order['subscription_id'],
        ]);

        // Redirect to PayPal approval
        return redirect($order['approval_url']);
    }

    /**
     * Handle successful PayPal subscription approval.
     * 
     * User returns here after approving the subscription on PayPal.
     */
    public function success(Request $request)
    {
        $user = $request->user();
        $subscriptionId = $request->query('subscription_id');

        if (!$subscriptionId) {
            Log::warning('PayPal success called without subscription_id', [
                'user_id' => $user->id,
            ]);
            
            return redirect()->route('dashboard')
                ->withErrors(['payment' => 'Invalid PayPal response.']);
        }

        // Verify subscription exists in our database
        $transaction = PaypalTransaction::where('user_id', $user->id)
            ->where('subscription_id', $subscriptionId)
            ->first();

        if (!$transaction) {
            Log::warning('PayPal success: transaction not found', [
                'user_id' => $user->id,
                'subscription_id' => $subscriptionId,
            ]);
            
            return redirect()->route('dashboard')
                ->withErrors(['payment' => 'Payment record not found.']);
        }

        // Get subscription details from PayPal
        $subscriptionDetails = $this->paypalService->getSubscription($subscriptionId);
        if (!$subscriptionDetails) {
            Log::error('PayPal success: failed to fetch subscription details', [
                'user_id' => $user->id,
                'subscription_id' => $subscriptionId,
            ]);
            
            return redirect()->route('dashboard')
                ->withErrors(['payment' => 'Failed to verify subscription. Please contact support.']);
        }

        $status = $subscriptionDetails['status'] ?? null;

        // If subscription is approved but not yet active, activate it
        if ($status === 'APPROVAL_PENDING') {
            if (!$this->paypalService->activateSubscription($subscriptionId)) {
                Log::error('PayPal success: failed to activate subscription', [
                    'user_id' => $user->id,
                    'subscription_id' => $subscriptionId,
                ]);
                
                return redirect()->route('dashboard')
                    ->withErrors(['payment' => 'Failed to activate subscription. Please try again.']);
            }
            
            // Refresh subscription details
            $subscriptionDetails = $this->paypalService->getSubscription($subscriptionId);
        }

        // Check final status
        if ($subscriptionDetails['status'] !== 'ACTIVE') {
            Log::warning('PayPal success: subscription not active', [
                'user_id' => $user->id,
                'subscription_id' => $subscriptionId,
                'status' => $subscriptionDetails['status'],
            ]);
            
            return redirect()->route('dashboard')
                ->withErrors(['payment' => 'Subscription is not active. Status: ' . $subscriptionDetails['status']]);
        }

        // Update transaction record
        $transaction->update([
            'status' => 'active',
            'payer_email' => $subscriptionDetails['subscriber']['email_address'] ?? null,
            'payer_id' => $subscriptionDetails['subscriber']['payer_id'] ?? null,
            'activated_at' => now(),
            'metadata' => $subscriptionDetails,
        ]);

        // Cancel any existing Stripe subscription
        if ($user->subscribed('default')) {
            try {
                $user->subscription('default')->cancel();
                Log::info('Existing Stripe subscription cancelled', ['user_id' => $user->id]);
            } catch (\Exception $e) {
                Log::warning('Failed to cancel existing Stripe subscription', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('PayPal subscription activated successfully', [
            'user_id' => $user->id,
            'plan_id' => $transaction->plan_id,
            'subscription_id' => $subscriptionId,
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Subscription activated! Welcome to your new plan.');
    }

    /**
     * Handle PayPal subscription cancellation.
     * 
     * User cancelled the subscription approval on PayPal.
     */
    public function cancel(Request $request)
    {
        $user = $request->user();
        
        Log::info('PayPal checkout cancelled by user', ['user_id' => $user->id]);

        return redirect()->route('plans.index')
            ->with('info', 'PayPal checkout cancelled. You can try again anytime.');
    }

    /**
     * Handle webhook from PayPal for subscription status changes.
     * 
     * This is called by PayPal when subscription status changes (e.g., payment failed, renewed, cancelled).
     */
    public function webhook(Request $request)
    {
        $data = $request->json()->all();
        
        Log::debug('PayPal webhook received', [
            'event_type' => $data['event_type'] ?? null,
        ]);

        // Verify webhook is from PayPal
        // TODO: Implement webhook verification using PayPal's signature verification

        $eventType = $data['event_type'] ?? null;
        $resource = $data['resource'] ?? [];
        $subscriptionId = $resource['id'] ?? null;

        if (!$subscriptionId) {
            return response()->json(['status' => 'ok'], 200);
        }

        // Find transaction
        $transaction = PaypalTransaction::where('subscription_id', $subscriptionId)->first();
        if (!$transaction) {
            Log::warning('PayPal webhook: transaction not found', [
                'subscription_id' => $subscriptionId,
                'event_type' => $eventType,
            ]);
            return response()->json(['status' => 'ok'], 200);
        }

        // Handle different event types
        switch ($eventType) {
            case 'BILLING.SUBSCRIPTION.CREATED':
                $transaction->update(['status' => 'pending']);
                break;

            case 'BILLING.SUBSCRIPTION.ACTIVATED':
                $transaction->markAsActive();
                break;

            case 'BILLING.SUBSCRIPTION.PAYMENT.SUCCESS':
                // Payment was successful, update next billing date if available
                $nextBillingTime = $resource['billing_agreement_id'] ?? null;
                if (isset($resource['agreement_details'])) {
                    $transaction->update([
                        'next_billing_at' => $resource['agreement_details']['next_billing_time'] ?? null,
                    ]);
                }
                break;

            case 'BILLING.SUBSCRIPTION.PAYMENT.FAILED':
                Log::error('PayPal webhook: subscription payment failed', [
                    'subscription_id' => $subscriptionId,
                    'user_id' => $transaction->user_id,
                ]);
                $transaction->markAsFailed('Payment failed on PayPal');
                break;

            case 'BILLING.SUBSCRIPTION.CANCELLED':
                $transaction->markAsCancelled();
                break;

            case 'BILLING.SUBSCRIPTION.EXPIRED':
                $transaction->update(['status' => 'expired']);
                break;

            case 'BILLING.SUBSCRIPTION.SUSPENDED':
                $transaction->update(['status' => 'suspended']);
                break;
        }

        Log::info('PayPal webhook processed', [
            'event_type' => $eventType,
            'subscription_id' => $subscriptionId,
            'status' => $transaction->status,
        ]);

        return response()->json(['status' => 'ok'], 200);
    }
}
