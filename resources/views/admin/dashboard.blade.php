@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Admin Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">System overview and analytics</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php $adminStats = [
            ['label' => 'Total Users',       'value' => $stats['total_users'],       'sub' => '+' . $stats['new_users_month'] . ' this month', 'color' => 'blue'],
            ['label' => 'Total Conversions', 'value' => $stats['total_conversions'], 'sub' => $stats['conversions_today'] . ' today',          'color' => 'green'],
            ['label' => 'Failed Conversions','value' => $stats['failed_conversions'],'sub' => 'needs attention',                               'color' => 'red'],
            ['label' => 'Active Subscribers','value' => $stats['active_subscribers'],'sub' => $stats['storage_used_gb'] . ' GB stored',        'color' => 'purple'],
        ] @endphp
        @foreach($adminStats as $stat)
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">{{ $stat['label'] }}</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stat['value'] }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $stat['sub'] }}</p>
            </div>
        @endforeach
    </div>

    <!-- Top Formats -->
    @if($topFormats->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Most Requested Target Formats</h3>
            <div class="space-y-2">
                @foreach($topFormats as $fmt)
                    <div class="flex items-center gap-3">
                        <span class="w-12 text-xs font-bold text-gray-700 uppercase">{{ $fmt->target_format }}</span>
                        <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-primary-500 rounded-full"
                                 style="width: {{ ($topFormats->max('count') > 0) ? round(($fmt->count / $topFormats->max('count')) * 100) : 0 }}%"></div>
                        </div>
                        <span class="text-xs text-gray-500 w-10 text-right">{{ $fmt->count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Recent Users -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Recent Users</h3>
                <a href="{{ route('admin.users.index') }}" class="text-sm text-primary-600 hover:text-primary-700">View all →</a>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($recentUsers as $u)
                    <div class="flex items-center gap-3 px-5 py-3">
                        <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center text-xs font-bold text-primary-700">
                            {{ strtoupper(substr($u->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $u->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $u->email }}</p>
                        </div>
                        <span class="text-xs text-gray-400">{{ $u->created_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Conversions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Recent Conversions</h3>
                <a href="{{ route('admin.conversions.index') }}" class="text-sm text-primary-600 hover:text-primary-700">View all →</a>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($recentConversions as $conv)
                    <div class="flex items-center gap-3 px-5 py-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $conv->original_filename }}</p>
                            <p class="text-xs text-gray-500">{{ $conv->user?->email }} · {{ strtoupper($conv->source_format) }} → {{ strtoupper($conv->target_format) }}</p>
                        </div>
                        <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $conv->status === 'completed' ? 'bg-green-100 text-green-700' :
                               ($conv->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                            {{ ucfirst($conv->status) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
