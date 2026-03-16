@extends('layouts.app')
@section('title', 'All Conversions (Admin)')
@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">All Conversions</h1>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">File</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">User</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Format</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($conversions as $conv)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-900 truncate max-w-xs">{{ $conv->original_filename }}</p>
                        </td>
                        <td class="px-5 py-3 text-gray-500 text-xs hidden md:table-cell">{{ $conv->user?->email }}</td>
                        <td class="px-5 py-3 hidden md:table-cell">
                            <span class="font-medium text-xs uppercase text-gray-700">{{ $conv->source_format }} → {{ $conv->target_format }}</span>
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
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div>{{ $conversions->links() }}</div>
</div>
@endsection
