@extends('layouts.app')
@section('title', 'Convert File')
@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Convert Embroidery File</h1>
        <p class="text-sm text-gray-500 mt-1">Select a file and choose your target format.</p>
    </div>

    <!-- Usage indicator -->
    @if($plan)
        <div class="flex items-center justify-between bg-white border border-gray-100 rounded-xl px-5 py-3 shadow-sm text-sm">
            <span class="text-gray-600">
                Daily conversions:
                <strong class="text-gray-900">{{ $todayCount }}</strong>
                @if($dailyLimit > 0) / {{ $dailyLimit }} @else (Unlimited) @endif
            </span>
            @if($dailyLimit > 0 && $todayCount >= $dailyLimit)
                <a href="{{ route('plans.index') }}" class="text-primary-600 font-medium hover:text-primary-700">Upgrade to convert more →</a>
            @endif
        </div>
    @endif

    @if($dailyLimit > 0 && $todayCount >= $dailyLimit)
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5 text-center">
            <p class="text-yellow-800 font-medium">Daily conversion limit reached</p>
            <p class="text-yellow-700 text-sm mt-1">You've used all {{ $dailyLimit }} conversions for today.</p>
            <a href="{{ route('plans.index') }}"
               class="mt-3 inline-block px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700 transition-colors">
                Upgrade Plan
            </a>
        </div>
    @else
        <form method="POST" action="{{ route('conversions.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">
            @csrf

            <!-- Source file selector -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Source File</label>
                @if($files->isEmpty())
                    <div class="border-2 border-dashed border-gray-200 rounded-lg p-8 text-center">
                        <p class="text-gray-500 text-sm">No files uploaded yet.</p>
                        <a href="{{ route('files.upload') }}"
                           class="mt-2 inline-block text-primary-600 hover:text-primary-700 text-sm font-medium">Upload a file first</a>
                    </div>
                @else
                    <select name="source_file_id" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">-- Select a file --</option>
                        @foreach($files as $file)
                            <option value="{{ $file->id }}" {{ ($selectedFile?->id === $file->id || old('source_file_id') == $file->id) ? 'selected' : '' }}>
                                {{ $file->original_name }} ({{ strtoupper($file->extension) }}, {{ $file->size_human }})
                            </option>
                        @endforeach
                    </select>
                    @error('source_file_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                @endif
            </div>

            <!-- Target format -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Target Format</label>
                <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                    @foreach($targetFormats as $fmt)
                        <label class="flex flex-col items-center gap-1 cursor-pointer">
                            <input type="radio" name="target_format" value="{{ $fmt }}"
                                   {{ old('target_format') === $fmt ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-full py-2 border-2 rounded-lg text-center text-xs font-bold uppercase transition-all
                                        peer-checked:border-primary-500 peer-checked:bg-primary-50 peer-checked:text-primary-700
                                        border-gray-200 text-gray-600 hover:border-gray-300">
                                {{ $fmt }}
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('target_format')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    Start Conversion
                </button>
            </div>
        </form>

        <!-- Batch conversion -->
        @if($plan && $plan->max_batch_size > 1)
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <p class="text-sm text-blue-800">
                    <strong>Batch conversion available!</strong>
                    Your {{ $plan->name }} plan supports converting up to {{ $plan->max_batch_size }} files at once.
                    <a href="{{ route('files.index') }}" class="underline">Go to your library</a> to select multiple files.
                </p>
            </div>
        @endif
    @endif
</div>
@endsection
