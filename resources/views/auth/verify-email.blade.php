@extends('layouts.guest')
@section('title', 'Verify Email')
@section('content')
<div class="text-center">
    <svg class="mx-auto w-16 h-16 text-primary-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
    </svg>
    <h2 class="text-2xl font-bold text-gray-900 mb-2">Verify your email</h2>
    <p class="text-sm text-gray-500 mb-6">
        We've sent a verification link to <strong>{{ auth()->user()->email }}</strong>.
        Please check your inbox and click the link to verify your email address.
    </p>

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit"
                class="inline-flex justify-center py-2.5 px-6 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
            Resend verification email
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">Log out</button>
    </form>
</div>
@endsection
