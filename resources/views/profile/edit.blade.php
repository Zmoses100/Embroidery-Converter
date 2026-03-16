@extends('layouts.app')
@section('title', 'Profile Settings')
@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Profile Settings</h1>

    <!-- Profile Info -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Profile Information</h3>
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 @error('name') border-red-400 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 @error('email') border-red-400 @enderror">
                @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                @if($user->email_verified_at === null)
                    <p class="mt-1 text-xs text-yellow-600">⚠ Email not verified.
                        <form method="POST" action="{{ route('verification.send') }}" class="inline">
                            @csrf <button type="submit" class="text-primary-600 underline">Resend</button>
                        </form>
                    </p>
                @endif
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Timezone</label>
                <select name="timezone" class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    @foreach(timezone_identifiers_list() as $tz)
                        <option value="{{ $tz }}" {{ $user->timezone === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                Save Changes
            </button>
        </form>
    </div>

    <!-- Change Password -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Change Password</h3>
        <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700">Current Password</label>
                <input type="password" name="current_password" required
                       class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 @error('current_password') border-red-400 @enderror">
                @error('current_password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">New Password</label>
                <input type="password" name="password" required
                       class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                <input type="password" name="password_confirmation" required
                       class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <button type="submit" class="px-6 py-2.5 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-900 transition-colors">
                Update Password
            </button>
        </form>
    </div>

    <!-- Subscription -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Subscription</h3>
        @php $plan = $user->activePlan(); @endphp
        <div class="flex items-center justify-between">
            <div>
                <p class="font-medium text-gray-900">{{ $plan?->name ?? 'Free' }} Plan</p>
                @if($user->subscribed('default'))
                    @if($user->subscription('default')->onGracePeriod())
                        <p class="text-sm text-yellow-600">Cancels on {{ $user->subscription('default')->ends_at?->format('M d, Y') }}</p>
                    @else
                        <p class="text-sm text-gray-500">Active subscription</p>
                    @endif
                @else
                    <p class="text-sm text-gray-500">Free plan</p>
                @endif
            </div>
            <div class="flex gap-2">
                <a href="{{ route('plans.index') }}" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                    {{ $user->subscribed('default') ? 'Change Plan' : 'Upgrade' }}
                </a>
                @if($user->subscribed('default') && !$user->subscription('default')->onGracePeriod())
                    <form method="POST" action="{{ route('subscription.cancel') }}" onsubmit="return confirm('Cancel subscription?')">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-red-50 text-red-600 text-sm font-medium rounded-lg hover:bg-red-100 transition-colors">Cancel</button>
                    </form>
                @endif
                @if($user->subscription('default')?->onGracePeriod())
                    <form method="POST" action="{{ route('subscription.resume') }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">Resume</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Delete Account -->
    <div class="bg-white rounded-xl shadow-sm border border-red-100 p-6">
        <h3 class="font-semibold text-red-700 mb-2">Danger Zone</h3>
        <p class="text-sm text-gray-500 mb-4">Once you delete your account, all your files and data will be permanently removed.</p>
        <form method="POST" action="{{ route('profile.destroy') }}"
              onsubmit="return confirm('Are you sure? This action CANNOT be undone.')">
            @csrf @method('DELETE')
            <div class="flex flex-wrap gap-3 items-center">
                <input type="password" name="password" placeholder="Enter your password to confirm"
                       class="flex-1 min-w-48 px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                <button type="submit" class="px-4 py-2.5 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                    Delete My Account
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
