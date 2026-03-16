@extends('layouts.guest')
@section('title', 'Login')
@section('content')
<h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Sign in to your account</h2>

<form method="POST" action="{{ route('login') }}" class="space-y-5">
    @csrf
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
               class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 @error('email') border-red-400 @enderror">
        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <div class="flex items-center justify-between">
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <a href="{{ route('password.request') }}" class="text-xs text-primary-600 hover:text-primary-500">Forgot password?</a>
        </div>
        <input id="password" type="password" name="password" required autocomplete="current-password"
               class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
    </div>

    <div class="flex items-center">
        <input id="remember" type="checkbox" name="remember" class="h-4 w-4 text-primary-600 border-gray-300 rounded">
        <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
    </div>

    <button type="submit"
            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
        Sign in
    </button>
</form>

<p class="mt-6 text-center text-sm text-gray-500">
    Don't have an account?
    <a href="{{ route('register') }}" class="font-medium text-primary-600 hover:text-primary-500">Sign up free</a>
</p>
@endsection
