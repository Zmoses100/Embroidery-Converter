@extends('layouts.app')
@section('title', $plan->exists ? 'Edit Plan' : 'Create Plan')
@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.plans.index') }}" class="text-gray-400 hover:text-gray-600">←</a>
        <h1 class="text-2xl font-bold text-gray-900">{{ $plan->exists ? 'Edit Plan: ' . $plan->name : 'Create Plan' }}</h1>
    </div>

    <form method="POST" action="{{ $plan->exists ? route('admin.plans.update', $plan) : route('admin.plans.store') }}"
          class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
        @csrf
        @if($plan->exists) @method('PUT') @endif

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Name *</label>
                <input type="text" name="name" value="{{ old('name', $plan->name) }}" required
                       class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Slug *</label>
                <input type="text" name="slug" value="{{ old('slug', $plan->slug) }}" required
                       class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                @error('slug')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" rows="2"
                      class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">{{ old('description', $plan->description) }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Monthly Price ($)</label>
                <input type="number" name="price_monthly" step="0.01" min="0" value="{{ old('price_monthly', $plan->price_monthly ?? 0) }}" required
                       class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Yearly Price ($)</label>
                <input type="number" name="price_yearly" step="0.01" min="0" value="{{ old('price_yearly', $plan->price_yearly ?? 0) }}" required
                       class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Stripe Monthly Price ID</label>
                <input type="text" name="stripe_monthly_price_id" value="{{ old('stripe_monthly_price_id', $plan->stripe_monthly_price_id) }}" placeholder="price_..."
                       class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Stripe Yearly Price ID</label>
                <input type="text" name="stripe_yearly_price_id" value="{{ old('stripe_yearly_price_id', $plan->stripe_yearly_price_id) }}" placeholder="price_..."
                       class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Conversions/Day (-1 = unlimited)</label>
                <input type="number" name="conversions_per_day" min="-1" value="{{ old('conversions_per_day', $plan->conversions_per_day ?? 5) }}" required
                       class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Storage Limit (MB)</label>
                <input type="number" name="storage_limit_mb" min="1" value="{{ old('storage_limit_mb', $plan->storage_limit_mb ?? 100) }}" required
                       class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Max File Size (MB)</label>
                <input type="number" name="max_file_size_mb" min="1" value="{{ old('max_file_size_mb', $plan->max_file_size_mb ?? 10) }}" required
                       class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Max Batch Size</label>
                <input type="number" name="max_batch_size" min="1" value="{{ old('max_batch_size', $plan->max_batch_size ?? 1) }}" required
                       class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
        </div>

        <!-- Feature Toggles -->
        <div class="grid grid-cols-2 gap-3">
            @foreach([
                ['name' => 'preview_enabled',  'label' => 'Design Preview'],
                ['name' => 'history_enabled',  'label' => 'Conversion History'],
                ['name' => 'api_access',        'label' => 'API Access'],
                ['name' => 'priority_queue',   'label' => 'Priority Queue'],
                ['name' => 'is_active',         'label' => 'Active'],
                ['name' => 'is_featured',       'label' => 'Featured'],
            ] as $toggle)
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="{{ $toggle['name'] }}" value="1"
                           {{ old($toggle['name'], $plan->{$toggle['name']}) ? 'checked' : '' }}
                           class="h-4 w-4 text-primary-600 border-gray-300 rounded">
                    <span class="text-sm text-gray-700">{{ $toggle['label'] }}</span>
                </label>
            @endforeach
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Sort Order</label>
            <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $plan->sort_order ?? 0) }}"
                   class="mt-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                {{ $plan->exists ? 'Update Plan' : 'Create Plan' }}
            </button>
            <a href="{{ route('admin.plans.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Cancel</a>
        </div>
    </form>
</div>
@endsection
