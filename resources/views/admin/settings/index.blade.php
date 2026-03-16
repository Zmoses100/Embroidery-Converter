@extends('layouts.app')
@section('title', 'Settings')
@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Application Settings</h1>

    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf @method('PATCH')

        @foreach($settings as $group => $groupSettings)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-semibold text-gray-900 capitalize mb-4">{{ $group }}</h3>
                <div class="space-y-4">
                    @foreach($groupSettings as $setting)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ ucwords(str_replace(['_', 'app', 'max', 'mb', 'sec'], [' ', 'App', 'Max', 'MB', 'Seconds'], $setting->key)) }}
                            </label>
                            @if($setting->type === 'boolean')
                                <select name="{{ $setting->key }}"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="1" {{ $setting->value === '1' ? 'selected' : '' }}>Enabled</option>
                                    <option value="0" {{ $setting->value === '0' ? 'selected' : '' }}>Disabled</option>
                                </select>
                            @else
                                <input type="{{ $setting->type === 'integer' ? 'number' : 'text' }}"
                                       name="{{ $setting->key }}"
                                       value="{{ $setting->value }}"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
            Save Settings
        </button>
    </form>
</div>
@endsection
