@extends('layouts.guest')
@section('title', 'Forgot Password')
@section('content')
<h2 class="text-2xl font-bold text-gray-900 mb-2 text-center">Forgot your password?</h2>
<p class="text-sm text-gray-500 text-center mb-6">Enter your email and we'll send you a reset link.</p>

<form method="POST" action="{{ route('password.email') }}" class="space-y-5">
    @csrf
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
               class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 @error('email') border-red-400 @enderror">
        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <button type="submit"
            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
        Send reset link
    </button>
</form>

<p class="mt-6 text-center text-sm text-gray-500">
    <a href="{{ route('login') }}" class="font-medium text-primary-600 hover:text-primary-500">← Back to login</a>
</p>
@endsection
