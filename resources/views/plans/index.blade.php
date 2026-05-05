@extends('layouts.app')
@section('title', 'Plans')
@section('content')
<div class="max-w-5xl mx-auto space-y-8">
    <!-- Admin Warning: Missing Stripe Configuration -->
    @auth
        @if(auth()->user()->isAdmin())
            @php
                $stripeNotConfigured = !config('cashier.key') || !config('cashier.secret');
                $missingPriceIds = [];
                
                foreach($plans as $plan) {
                    if (!$plan->isFree() && (!$plan->stripe_monthly_price_id || !$plan->stripe_yearly_price_id)) {
                        $missingPriceIds[] = $plan->name;
                    }
                }
            @endphp

            @if($stripeNotConfigured)
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-800">
                    <strong>⚠️ Admin Alert:</strong> Stripe credentials are not configured. 
                    Please set <code class="bg-white px-2 py-1 rounded">STRIPE_KEY</code> and 
                    <code class="bg-white px-2 py-1 rounded">STRIPE_SECRET</code> in your .env file.
                    <a href="{{ route('admin.settings.index') }}" class="ml-2 underline font-medium">View Settings →</a>
                </div>
            @endif

            @if(!empty($missingPriceIds))
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-800">
                    <strong>⚠️ Admin Alert:</strong> The following plans are missing Stripe price IDs: 
                    <strong>{{ implode(', ', $missingPriceIds) }}</strong>. 
                    Users cannot subscribe until these are configured.
                    <a href="{{ route('admin.plans.index') }}" class="ml-2 underline font-medium">Configure Plans →</a>
                </div>
            @endif
        @endif
    @endauth

    <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900">Choose Your Plan</h1>
        <p class="text-gray-500 mt-2">Simple, transparent pricing. Start free today.</p>
    </div>

    <div class="grid md:grid-cols-{{ $plans->count() }} gap-6 items-stretch">
        @foreach($plans as $plan)
            <div class="relative bg-white rounded-2xl shadow-sm border {{ $plan->is_featured ? 'border-primary-400 ring-2 ring-primary-400' : 'border-gray-200' }} p-7 flex flex-col">
                @if($plan->is_featured)
                    <div class="absolute -top-3.5 left-1/2 -translate-x-1/2">
                        <span class="inline-flex items-center px-3 py-1 bg-primary-600 text-white text-xs font-semibold rounded-full">
                            Most Popular
                        </span>
                    </div>
                @endif

                <div class="mb-6">
                    <h3 class="text-xl font-bold text-gray-900">{{ $plan->name }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ $plan->description }}</p>
                </div>

                <div class="mb-6">
                    @if($plan->price_monthly == 0)
                        <div class="text-4xl font-extrabold text-gray-900">Free</div>
                        <p class="text-sm text-gray-400 mt-1">Forever</p>
                    @else
                        <div class="text-4xl font-extrabold text-gray-900">${{ $plan->price_monthly }}</div>
                        <p class="text-sm text-gray-400 mt-1">per month · or ${{ $plan->price_yearly }}/yr (save {{ round((1 - ($plan->price_yearly / ($plan->price_monthly * 12))) * 100) }}%)</p>
                    @endif
                </div>

                <!-- Features -->
                <ul class="space-y-2 mb-8 flex-1">
                    @foreach(($plan->features ?? []) as $feature)
                        <li class="flex items-center gap-2 text-sm text-gray-700">
                            <svg class="w-4 h-4 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            {{ $feature }}
                        </li>
                    @endforeach
                </ul>

                <!-- CTA -->
                @auth
                    @if($plan->price_monthly == 0)
                        @php $activePlan = auth()->user()->activePlan(); @endphp
                        @if(!$activePlan || $activePlan->id === $plan->id)
                            <button disabled class="w-full py-3 bg-gray-100 text-gray-500 text-sm font-medium rounded-xl cursor-not-allowed">
                                Current Plan
                            </button>
                        @else
                            <form method="POST" action="{{ route('subscription.cancel') }}">
                                @csrf
                                <button type="submit"
                                        class="w-full py-3 bg-gray-100 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-200 transition-colors">
                                    Downgrade to Free
                                </button>
                            </form>
                        @endif
                    @else
                        <!-- Billing Interval Selector -->
                        <div class="mb-4 flex gap-2 bg-gray-100 rounded-lg p-1">
                            <button type="button" onclick="setInterval(this, 'monthly', {{ $plan->id }})"
                                    class="flex-1 py-2 px-3 rounded-md text-xs font-medium transition-colors monthly-btn-{{ $plan->id }} bg-white text-gray-900 shadow-sm">
                                Monthly
                            </button>
                            <button type="button" onclick="setInterval(this, 'yearly', {{ $plan->id }})"
                                    class="flex-1 py-2 px-3 rounded-md text-xs font-medium transition-colors yearly-btn-{{ $plan->id }} text-gray-700 hover:bg-gray-50">
                                Yearly
                            </button>
                        </div>

                        <!-- Payment Method Selector -->
                        <div class="space-y-2" id="payment-methods-{{ $plan->id }}">
                            <!-- Stripe Payment (Existing) -->
                            <form method="POST" action="{{ route('subscription.checkout', $plan) }}" class="stripe-form-{{ $plan->id }}">
                                @csrf
                                <input type="hidden" name="interval" value="monthly" class="interval-input-{{ $plan->id }}">
                                <button type="submit"
                                        class="w-full py-2 {{ $plan->is_featured ? 'bg-primary-600 text-white hover:bg-primary-700' : 'bg-gray-900 text-white hover:bg-gray-800' }} text-xs font-medium rounded-lg transition-colors">
                                    Stripe
                                </button>
                            </form>
                            
                            <!-- PayPal Payment Option -->
                            @if(config('services.paypal.client_id') && config('services.paypal.secret'))
                                <form method="POST" action="{{ route('paypal.checkout', $plan) }}" class="paypal-form-{{ $plan->id }}">
                                    @csrf
                                    <input type="hidden" name="interval" value="monthly" class="interval-input-{{ $plan->id }}">
                                    <button type="submit"
                                            class="w-full py-2 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M9.5 2h-6a1.5 1.5 0 00-1.5 1.5v17A1.5 1.5 0 003.5 22h6a1.5 1.5 0 001.5-1.5V3.5A1.5 1.5 0 009.5 2zM13 2h6a1.5 1.5 0 011.5 1.5v17a1.5 1.5 0 01-1.5 1.5h-6a1.5 1.5 0 01-1.5-1.5V3.5A1.5 1.5 0 0113 2z"/>
                                        </svg>
                                        PayPal
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                @else
                    <a href="{{ route('register') }}"
                       class="block w-full text-center py-3 {{ $plan->is_featured ? 'bg-primary-600 text-white hover:bg-primary-700' : 'bg-gray-900 text-white hover:bg-gray-800' }} text-sm font-medium rounded-xl transition-colors">
                        Get Started
                    </a>
                @endauth
            </div>
        @endforeach
    </div>

    <div class="text-center text-sm text-gray-400">
        <p>All plans include secure file handling and 30+ format support.</p>
        <p class="mt-1">Questions? Email us at <a href="mailto:support@example.com" class="text-primary-600 hover:underline">support@example.com</a></p>
    </div>
</div>

<script>
function setInterval(button, interval, planId) {
    // Update button styles
    document.querySelector('.monthly-btn-' + planId).classList.toggle('bg-white');
    document.querySelector('.monthly-btn-' + planId).classList.toggle('text-gray-900');
    document.querySelector('.monthly-btn-' + planId).classList.toggle('shadow-sm');
    document.querySelector('.monthly-btn-' + planId).classList.toggle('text-gray-700');
    document.querySelector('.monthly-btn-' + planId).classList.toggle('hover:bg-gray-50');
    
    document.querySelector('.yearly-btn-' + planId).classList.toggle('bg-white');
    document.querySelector('.yearly-btn-' + planId).classList.toggle('text-gray-900');
    document.querySelector('.yearly-btn-' + planId).classList.toggle('shadow-sm');
    document.querySelector('.yearly-btn-' + planId).classList.toggle('text-gray-700');
    document.querySelector('.yearly-btn-' + planId).classList.toggle('hover:bg-gray-50');
    
    // Update hidden form inputs
    document.querySelectorAll('.interval-input-' + planId).forEach(input => {
        input.value = interval;
    });
}
</script>
@endsection
