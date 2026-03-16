@extends('layouts.guest')
@section('title', 'Register')
@section('content')
<h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Create your account</h2>

<form method="POST" action="{{ route('register') }}" class="space-y-5">
    @csrf
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Full name</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
               class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 @error('name') border-red-400 @enderror">
        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
               class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 @error('email') border-red-400 @enderror">
        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
        <input id="password" type="password" name="password" required autocomplete="new-password"
               class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
        @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required
               class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
    </div>

    <button type="submit"
            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
        Create account
    </button>
</form>

<p class="mt-6 text-center text-sm text-gray-500">
    Already have an account?
    <a href="{{ route('login') }}" class="font-medium text-primary-600 hover:text-primary-500">Sign in</a>
</p>
@endsection
