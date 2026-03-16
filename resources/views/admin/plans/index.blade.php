@extends('layouts.app')
@section('title', 'Manage Plans')
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Plans</h1>
        <a href="{{ route('admin.plans.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
            Add Plan
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Plan</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Price</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Conversions/Day</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Storage</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Status</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($plans as $plan)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-900">{{ $plan->name }}</p>
                            <p class="text-xs text-gray-500">{{ $plan->slug }}</p>
                        </td>
                        <td class="px-5 py-3">
                            <p class="text-gray-900">${{ $plan->price_monthly }}/mo</p>
                            <p class="text-xs text-gray-500">${{ $plan->price_yearly }}/yr</p>
                        </td>
                        <td class="px-5 py-3 text-gray-600 hidden md:table-cell">
                            {{ $plan->conversions_per_day === -1 ? 'Unlimited' : $plan->conversions_per_day }}
                        </td>
                        <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ $plan->storage_limit_mb }} MB</td>
                        <td class="px-5 py-3 hidden lg:table-cell">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $plan->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $plan->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.plans.edit', $plan) }}" class="text-xs text-primary-600 hover:text-primary-700 font-medium">Edit</a>
                                <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}"
                                      onsubmit="return confirm('Delete this plan?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-red-500 hover:text-red-700">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
