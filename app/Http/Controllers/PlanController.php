<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlanController extends Controller
{
    /**
     * Show all available plans.
     */
    public function index()
    {
        $plans = Plan::active()->orderBy('sort_order')->orderBy('price_monthly')->get();

        return view('plans.index', compact('plans'));
    }

    /**
     * Handle subscription checkout via Stripe.
     */
    public function checkout(Request $request, Plan $plan)
    {
        $user     = $request->user();
        $interval = $request->input('interval', 'monthly');

        // Free plans don't require Stripe checkout
        if ($plan->isFree()) {
            if ($user->subscribed('default')) {
                $user->subscription('default')->cancel();
            }

            return redirect()->route('dashboard')->with('success', 'You are now on the Free plan.');
        }

        $priceId = $interval === 'yearly'
            ? $plan->stripe_yearly_price_id
            : $plan->stripe_monthly_price_id;

        if (!$priceId) {
            // Log the missing price ID for admin debugging
            Log::warning('Stripe price ID missing', [
                'plan_id' => $plan->id,
                'plan_slug' => $plan->slug,
                'interval' => $interval,
                'user_id' => $user->id,
            ]);

            // Show user-friendly error
            $errorMsg = "The {$plan->name} plan is not yet available for purchase. "
                . "Please contact support or check back soon.";

            // Show admin-specific error
            if ($user->isAdmin()) {
                $errorMsg .= " [Admin: Configure Stripe price IDs in Admin > Plans for {$plan->name} ({$interval}).] "
                    . "Monthly ID: {$plan->stripe_monthly_price_id}, Yearly ID: {$plan->stripe_yearly_price_id}";
            }

            return back()->withErrors(['plan' => $errorMsg]);
        }

        // Verify Stripe is configured
        if (!config('cashier.key') || !config('cashier.secret')) {
            Log::error('Stripe configuration missing', [
                'has_key' => (bool) config('cashier.key'),
                'has_secret' => (bool) config('cashier.secret'),
            ]);

            $errorMsg = 'Stripe is not properly configured. Please try again later.';
            if ($user->isAdmin()) {
                $errorMsg .= ' [Admin: Set STRIPE_KEY and STRIPE_SECRET in .env]';
            }

            return back()->withErrors(['plan' => $errorMsg]);
        }

        // If user already has a subscription, swap to the new plan
        if ($user->subscribed('default')) {
            try {
                $user->subscription('default')->swap($priceId);
                return redirect()->route('dashboard')->with('success', 'Plan changed successfully!');
            } catch (\Exception $e) {
                Log::error('Stripe plan swap failed', [
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'error' => $e->getMessage(),
                ]);

                $errorMsg = 'Failed to change your plan. Please try again.';
                if ($user->isAdmin()) {
                    $errorMsg .= ' [Error: ' . $e->getMessage() . ']';
                }

                return back()->withErrors(['plan' => $errorMsg]);
            }
        }

        // New subscription - create Stripe checkout session
        try {
            return $user->newSubscription('default', $priceId)
                ->checkout([
                    'success_url' => route('subscription.success') . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url'  => route('plans.index'),
                    'customer_email' => $user->email,
                ]);
        } catch (\Exception $e) {
            Log::error('Stripe checkout creation failed', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'price_id' => $priceId,
                'error' => $e->getMessage(),
            ]);

            $errorMsg = 'Failed to start checkout. Please try again.';
            if ($user->isAdmin()) {
                $errorMsg .= ' [Error: ' . $e->getMessage() . ']';
            }

            return back()->withErrors(['plan' => $errorMsg]);
        }
    }

    /**
     * Handle successful subscription.
     */
    public function success(Request $request)
    {
        return redirect()->route('dashboard')->with('success', 'Subscription activated! Welcome to your new plan.');
    }

    /**
     * Cancel current subscription.
     */
    public function cancel(Request $request)
    {
        $user = $request->user();

        if ($user->subscribed('default')) {
            $user->subscription('default')->cancel();

            return redirect()->route('profile.edit')
                ->with('success', 'Subscription cancelled. You will retain access until the end of the billing period.');
        }

        return back()->with('error', 'No active subscription found.');
    }

    /**
     * Resume a cancelled subscription.
     */
    public function resume(Request $request)
    {
        $user = $request->user();

        if ($user->subscription('default')?->onGracePeriod()) {
            $user->subscription('default')->resume();

            return redirect()->route('profile.edit')->with('success', 'Subscription resumed!');
        }

        return back()->with('error', 'Subscription cannot be resumed.');
    }

    /**
     * Handle Stripe webhook.
     */
    public function webhook()
    {
        // Handled by Laravel Cashier's webhook route automatically
        return response('OK', 200);
    }
}
