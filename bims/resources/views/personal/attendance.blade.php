@extends('layouts.app')
@section('title', 'My Attendance')
@section('page-title', 'My Attendance')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-6">
    <div>
        <h2 class="text-xl font-semibold text-gray-900">{{ $month->format('F Y') }}</h2>
        <p class="text-sm text-gray-500 mt-1">
            Total shift time: <strong>{{ intdiv($totalMinutes, 60) }}h {{ $totalMinutes % 60 }}m</strong>
            · Late: <strong class="{{ $lateCount > 0 ? 'text-red-600' : '' }}">{{ $lateCount }}</strong>
        </p>
    </div>
    <form method="GET" class="flex gap-2 mt-4 sm:mt-0">
        <input type="month" name="month" value="{{ $month->format('Y-m') }}"
               class="rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600">
        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">Go</button>
    </form>
</div>

<div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
            <tr>
                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Date</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Reason</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">In</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Out</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Duration</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($logs as $log)
            <tr class="{{ $log->status_in === 'Late In' ? 'bg-red-50' : 'hover:bg-gray-50' }}">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-900 sm:pl-6">
                    {{ \Carbon\Carbon::parse($log->log_date)->format('D, M j') }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">{{ $log->reason }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">{{ $log->clock_in->format('g:i A') }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">{{ $log->clock_out?->format('g:i A') ?? '—' }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">{{ $log->clock_out ? $log->duration : '—' }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm">
                    @php
                        $s = $log->status_in;
                        $c = match($s) {
                            'In Time' => 'bg-green-50 text-green-700 ring-green-600/20',
                            'Late In' => 'bg-red-50 text-red-700 ring-red-600/20',
                            default   => 'bg-gray-50 text-gray-600 ring-gray-500/10',
                        };
                    @endphp
                    <span class="inline-flex rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $c }}">{{ $s }}</span>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="py-10 text-center text-sm text-gray-500">No records for this month.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
