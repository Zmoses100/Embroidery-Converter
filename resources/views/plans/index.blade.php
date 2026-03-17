@extends('layouts.app')
@section('title', 'Plans')
@section('content')
<div class="max-w-5xl mx-auto space-y-8">
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
                        <form method="POST" action="{{ route('subscription.checkout', $plan) }}">
                            @csrf
                            <input type="hidden" name="interval" value="monthly">
                            <button type="submit"
                                    class="w-full py-3 {{ $plan->is_featured ? 'bg-primary-600 text-white hover:bg-primary-700' : 'bg-gray-900 text-white hover:bg-gray-800' }} text-sm font-medium rounded-xl transition-colors">
                                Get {{ $plan->name }}
                            </button>
                        </form>
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
@endsection
