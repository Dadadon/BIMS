@extends('layouts.app')
@section('title', 'Attendance — Today')
@section('page-title', 'Attendance')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-6">
    <div>
        <h2 class="text-xl font-semibold text-gray-900">Today — {{ now()->format('F j, Y') }}</h2>
        <p class="text-sm text-gray-500 mt-1">{{ $logs->total() }} entries</p>
    </div>
    <div class="mt-4 sm:mt-0 flex gap-3">
        <a href="{{ route('admin.attendance.filter') }}"
           class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            Filter / Search
        </a>
        <button type="button" onclick="document.getElementById('add-entry-modal').classList.remove('hidden')"
                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            + Add Entry
        </button>
    </div>
</div>

<div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
            <tr>
                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Employee</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Reason</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Clock In</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Clock Out</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Duration</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($logs as $log)
            <tr class="hover:bg-gray-50">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                    <p class="font-medium text-gray-900">{{ $log->employee?->display_name ?? '—' }}</p>
                    <p class="text-xs text-gray-500">{{ $log->employee?->employee_code }}</p>
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">{{ $log->reason }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">{{ $log->clock_in->format('g:i A') }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">
                    {{ $log->clock_out?->format('g:i A') ?? '<span class="text-yellow-600">Open</span>' }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">
                    {{ $log->clock_out ? $log->duration : '—' }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm">
                    @php
                        $statusLabel = $log->status_in;
                        $color = match($statusLabel) {
                            'In Time'  => 'bg-green-50 text-green-700 ring-green-600/20',
                            'Late In'  => 'bg-red-50 text-red-700 ring-red-600/20',
                            'Manual'   => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                            default    => 'bg-gray-50 text-gray-600 ring-gray-500/10',
                        };
                    @endphp
                    <span class="inline-flex rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $color }}">
                        {{ $statusLabel }}
                    </span>
                </td>
                <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 space-x-2">
                    <a href="{{ route('admin.attendance.edit', $log) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                    <form method="POST" action="{{ route('admin.attendance.destroy', $log) }}" class="inline"
                          onsubmit="return confirm('Delete this record?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="py-10 text-center text-sm text-gray-500">No attendance records for today.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $logs->links() }}</div>

{{-- Add Entry Modal --}}
<div id="add-entry-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between">
            <h3 class="text-sm font-semibold text-gray-900">Add Manual Entry</h3>
            <button onclick="document.getElementById('add-entry-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        @include('admin.attendance._add-entry-form')
    </div>
</div>
@endsection
