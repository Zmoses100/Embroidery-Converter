@extends('layouts.app')
@section('title', 'Conversion History')
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Conversion History</h1>
            <p class="text-sm text-gray-500 mt-1">All your past and pending conversions</p>
        </div>
        <a href="{{ route('conversions.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
            New Conversion
        </a>
    </div>

    <!-- Filters -->
    <form method="GET" class="flex flex-wrap gap-3 bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <select name="status" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
            <option value="">All Status</option>
            @foreach(['pending','processing','completed','failed'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-900 transition-colors">Filter</button>
        @if(request('status'))
            <a href="{{ route('conversions.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Clear</a>
        @endif
    </form>

    @if($conversions->isEmpty())
        <div class="text-center py-16 bg-white rounded-xl border border-gray-100">
            <svg class="mx-auto w-16 h-16 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-500">No conversions yet</h3>
            <a href="{{ route('conversions.create') }}"
               class="mt-4 inline-block px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                Start Converting
            </a>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">File</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Conversion</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Date</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($conversions as $conv)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3">
                                <p class="font-medium text-gray-900 truncate max-w-xs">{{ $conv->original_filename }}</p>
                            </td>
                            <td class="px-5 py-3 hidden md:table-cell">
                                <span class="text-xs font-semibold uppercase text-gray-700">
                                    {{ $conv->source_format }} → {{ $conv->target_format }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $conv->status === 'completed' ? 'bg-green-100 text-green-700' :
                                       ($conv->status === 'failed' ? 'bg-red-100 text-red-700' :
                                       ($conv->status === 'processing' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700')) }}">
                                    {{ ucfirst($conv->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-xs text-gray-500 hidden lg:table-cell">{{ $conv->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('conversions.show', $conv) }}" class="text-xs text-primary-600 hover:text-primary-700 font-medium">View</a>
                                    @if($conv->outputFile)
                                        <a href="{{ route('files.download', $conv->outputFile) }}" class="text-xs text-gray-500 hover:text-gray-700">Download</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $conversions->links() }}</div>
    @endif
</div>
@endsection
