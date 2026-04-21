@extends('layouts.app')
@section('title', 'Attendance Filter')
@section('page-title', 'Attendance')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-6">
    <h2 class="text-xl font-semibold text-gray-900">Attendance Records</h2>
    <div class="flex gap-3 mt-4 sm:mt-0">
        <a href="{{ route('admin.attendance.index') }}"
           class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            Today's View
        </a>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-5">
    <input type="date" name="date" value="{{ request('date') }}"
           class="rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600">
    <select name="employee_id"
            class="rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600">
        <option value="">All Employees</option>
        @foreach($employees as $emp)
        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
            {{ $emp->display_name }}
        </option>
        @endforeach
    </select>
    <select name="status_in"
            class="rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600">
        <option value="">All Statuses</option>
        @foreach(['In Time','Late In','Manual','Lunch In','Break In'] as $s)
        <option value="{{ $s }}" {{ request('status_in') === $s ? 'selected' : '' }}>{{ $s }}</option>
        @endforeach
    </select>
    <div class="flex items-center gap-2">
        <input type="checkbox" name="open_only" id="open_only" value="1"
               {{ request('open_only') ? 'checked' : '' }}
               class="h-4 w-4 rounded border-gray-300 text-indigo-600">
        <label for="open_only" class="text-sm text-gray-700">Open only</label>
    </div>
    <div class="flex gap-2">
        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">Filter</button>
        <a href="{{ route('admin.attendance.filter') }}" class="rounded-md bg-white px-3 py-1.5 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Clear</a>
    </div>
</form>

<div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
            <tr>
                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Employee</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Reason</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">In → Out</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Duration</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($logs as $log)
            <tr class="hover:bg-gray-50">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                    <p class="font-medium text-gray-900">{{ $log->employee?->display_name }}</p>
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">{{ $log->log_date }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">{{ $log->reason }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">
                    {{ $log->clock_in->format('g:i A') }} → {{ $log->clock_out?->format('g:i A') ?? 'Open' }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">
                    {{ $log->clock_out ? $log->duration : '—' }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm">
                    @php
                        $s = $log->status_in;
                        $c = match($s) {
                            'In Time' => 'bg-green-50 text-green-700 ring-green-600/20',
                            'Late In' => 'bg-red-50 text-red-700 ring-red-600/20',
                            'Manual'  => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                            default   => 'bg-gray-50 text-gray-600 ring-gray-500/10',
                        };
                    @endphp
                    <span class="inline-flex rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $c }}">{{ $s }}</span>
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
            <tr><td colspan="7" class="py-10 text-center text-sm text-gray-500">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
