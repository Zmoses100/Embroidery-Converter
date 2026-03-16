@extends('layouts.app')
@section('title', $file->original_name)
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('files.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900 truncate">{{ $file->original_name }}</h1>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Preview Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col items-center justify-center">
            @if($file->preview_url)
                <img src="{{ $file->preview_url }}" alt="Preview" class="max-w-full max-h-64 object-contain rounded-lg">
            @else
                <div class="w-32 h-32 bg-primary-50 rounded-xl flex items-center justify-center mb-4">
                    <span class="text-4xl font-bold text-primary-300 uppercase">{{ $file->extension }}</span>
                </div>
                <p class="text-xs text-gray-400 text-center">Preview not available.<br>Install pyembroidery for previews.</p>
            @endif
        </div>

        <!-- File Info -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-900 mb-4">File Information</h3>
            <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                <div>
                    <dt class="text-gray-500">Format</dt>
                    <dd class="font-medium text-gray-900 uppercase">{{ $file->extension }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">File Size</dt>
                    <dd class="font-medium text-gray-900">{{ $file->size_human }}</dd>
                </div>
                @if($file->stitch_count)
                <div>
                    <dt class="text-gray-500">Stitch Count</dt>
                    <dd class="font-medium text-gray-900">{{ number_format($file->stitch_count) }}</dd>
                </div>
                @endif
                @if($file->color_count)
                <div>
                    <dt class="text-gray-500">Thread Colors</dt>
                    <dd class="font-medium text-gray-900">{{ $file->color_count }}</dd>
                </div>
                @endif
                @if($file->dimensions)
                <div>
                    <dt class="text-gray-500">Dimensions</dt>
                    <dd class="font-medium text-gray-900">{{ $file->dimensions }}</dd>
                </div>
                @endif
                @if($file->hoop_size)
                <div>
                    <dt class="text-gray-500">Hoop Size</dt>
                    <dd class="font-medium text-gray-900">{{ $file->hoop_size }}</dd>
                </div>
                @endif
                <div>
                    <dt class="text-gray-500">Type</dt>
                    <dd>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                            {{ $file->type === 'original' ? 'bg-green-50 text-green-700' : 'bg-orange-50 text-orange-700' }}">
                            {{ ucfirst($file->type) }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500">Uploaded</dt>
                    <dd class="font-medium text-gray-900">{{ $file->created_at->format('M d, Y') }}</dd>
                </div>
            </dl>

            <!-- Thread Colors -->
            @if($file->thread_colors)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-sm text-gray-500 mb-2">Thread Colors</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach(array_slice($file->thread_colors, 0, 20) as $color)
                            <div class="flex items-center gap-1.5">
                                <div class="w-4 h-4 rounded-full border border-gray-200"
                                     style="background-color: {{ $color['color'] ?? '#ccc' }}"></div>
                                <span class="text-xs text-gray-500">{{ $color['name'] ?? $color['color'] ?? '' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="mt-4 pt-4 border-t border-gray-100 flex flex-wrap gap-3">
                <a href="{{ route('conversions.create', ['file_id' => $file->id]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    Convert
                </a>
                <a href="{{ route('files.download', $file) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download
                </a>
                <form method="POST" action="{{ route('files.destroy', $file) }}"
                      onsubmit="return confirm('Are you sure you want to delete this file?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 text-sm font-medium rounded-lg hover:bg-red-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Conversion History for this file -->
    @if($conversions->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Conversion History</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Target Format</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($conversions as $conv)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3">
                                <span class="font-medium uppercase text-primary-700">{{ $conv->target_format }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $conv->status === 'completed' ? 'bg-green-100 text-green-700' :
                                       ($conv->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                    {{ ucfirst($conv->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-500 text-xs">{{ $conv->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-5 py-3 text-right">
                                @if($conv->outputFile)
                                    <a href="{{ route('files.download', $conv->outputFile) }}"
                                       class="text-xs text-primary-600 hover:text-primary-700 font-medium">Download</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
