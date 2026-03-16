<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

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

        $priceId = $interval === 'yearly'
            ? $plan->stripe_yearly_price_id
            : $plan->stripe_monthly_price_id;

        if (!$priceId) {
            return back()->withErrors(['plan' => 'This plan is not available for checkout.']);
        }

        // If user already has a subscription, swap to the new plan
        if ($user->subscribed('default')) {
            $user->subscription('default')->swap($priceId);

            return redirect()->route('dashboard')->with('success', 'Plan changed successfully!');
        }

        // New subscription - create Stripe checkout session
        return $user->newSubscription('default', $priceId)
            ->checkout([
                'success_url' => route('plans.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('plans.index'),
                'customer_email' => $user->email,
            ]);
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
