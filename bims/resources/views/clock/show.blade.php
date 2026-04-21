@extends('layouts.app')
@section('title', 'SmartClock')
@section('page-title', 'SmartClock')

@section('content')
@if(! $employee)
<div class="rounded-md bg-yellow-50 p-4">
    <p class="text-sm text-yellow-800">Your account is not linked to an employee record. Please contact your administrator.</p>
</div>
@else

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

    {{-- Clock In / Out card --}}
    <div class="lg:col-span-1 space-y-4">

        {{-- Current time --}}
        <div class="rounded-lg bg-gray-900 text-white p-6 text-center">
            <p class="text-sm text-gray-400">{{ now()->format('l, F j, Y') }}</p>
            <p class="mt-1 text-5xl font-bold tabular-nums" id="live-clock">{{ now()->format('g:i:s A') }}</p>
            <p class="mt-2 text-sm text-gray-400">{{ config('app.timezone') }}</p>
        </div>

        {{-- Status --}}
        @if($openLog)
        <div class="rounded-lg bg-green-50 border border-green-200 p-4">
            <div class="flex items-center gap-3">
                <span class="flex h-3 w-3 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                </span>
                <div>
                    <p class="text-sm font-semibold text-green-800">Currently clocked in</p>
                    <p class="text-xs text-green-600">Since {{ $openLog->clock_in->format('g:i A') }} · {{ $openLog->reason }}</p>
                </div>
            </div>
        </div>
        @else
        <div class="rounded-lg bg-gray-50 border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <span class="flex h-3 w-3 rounded-full bg-gray-400"></span>
                <p class="text-sm text-gray-600">Not clocked in</p>
            </div>
        </div>
        @endif

        {{-- Clock In form --}}
        @if(! $openLog)
        <div class="rounded-lg bg-white shadow p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Clock In</h3>
            <form method="POST" action="{{ route('clock.in') }}">
                @csrf
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Reason</label>
                    <select name="reason"
                            class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
                        <option value="Shift">Shift</option>
                        <option value="Lunch">Lunch Return</option>
                        <option value="Break">Break Return</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Comment (optional)</label>
                    <input type="text" name="comment" placeholder="e.g. WFH today"
                           class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
                </div>
                <button type="submit"
                        class="w-full rounded-md bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-500">
                    Clock In
                </button>
            </form>
        </div>
        @else
        {{-- Clock Out form --}}
        <div class="rounded-lg bg-white shadow p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Clock Out</h3>
            <form method="POST" action="{{ route('clock.out') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Reason</label>
                    <select name="reason"
                            class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
                        <option value="Shift">End of Shift</option>
                        <option value="Lunch">Lunch Break</option>
                        <option value="Break">Short Break</option>
                    </select>
                </div>
                <button type="submit"
                        class="w-full rounded-md bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                    Clock Out
                </button>
            </form>
        </div>
        @endif

        {{-- Log a Sale --}}
        @module('sales')
        <div class="rounded-lg bg-white shadow p-5" x-data="{ open: false }">
            <button @click="open = !open"
                    class="flex w-full items-center justify-between text-sm font-semibold text-gray-900">
                Log a Sale
                <svg class="h-5 w-5 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                </svg>
            </button>
            <div x-show="open" x-transition class="mt-4">
                <form method="POST" action="{{ route('clock.sale') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Sale Type <span class="text-red-500">*</span></label>
                        <select name="sale_type_id" required
                                class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
                            <option value="">— Select —</option>
                            @foreach($saleTypes as $st)
                            <option value="{{ $st->id }}">{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Customer Name <span class="text-red-500">*</span></label>
                        <input type="text" name="customer_name" required
                               class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Customer Phone</label>
                        <input type="text" name="customer_phone"
                               class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Sale Date <span class="text-red-500">*</span></label>
                        <input type="date" name="sale_date" required value="{{ now()->format('Y-m-d') }}"
                               class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Remarks</label>
                        <input type="text" name="remarks"
                               class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
                    </div>
                    <button type="submit"
                            class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        Submit Sale
                    </button>
                </form>
            </div>
        </div>
        @endmodule

    </div>

    {{-- Today's log --}}
    <div class="lg:col-span-2">
        <div class="rounded-lg bg-white shadow">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Today's Log — {{ now()->format('F j, Y') }}</h3>
            </div>

            @if($todayLogs->isEmpty())
            <div class="px-6 py-12 text-center text-sm text-gray-500">No records for today yet.</div>
            @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Clock In</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Clock Out</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($todayLogs as $log)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-3 text-sm text-gray-900">{{ $log->reason }}</td>
                            <td class="whitespace-nowrap px-6 py-3 text-sm text-gray-700">{{ $log->clock_in->format('g:i A') }}</td>
                            <td class="whitespace-nowrap px-6 py-3 text-sm text-gray-700">
                                {{ $log->clock_out ? $log->clock_out->format('g:i A') : '—' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-3 text-sm text-gray-700">
                                {{ $log->clock_out ? $log->duration : '—' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-3 text-sm">
                                @php
                                    $statusLabel = $log->status_out ?? $log->status_in;
                                    $statusColor = match(true) {
                                        in_array($statusLabel, ['In Time', 'On Time']) => 'bg-green-50 text-green-700 ring-green-600/20',
                                        in_array($statusLabel, ['Late In'])             => 'bg-red-50 text-red-700 ring-red-600/20',
                                        in_array($statusLabel, ['Early Out'])           => 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
                                        default                                         => 'bg-gray-50 text-gray-600 ring-gray-500/10',
                                    };
                                @endphp
                                <span class="inline-flex rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $statusColor }}">
                                    {{ $statusLabel ?? 'Open' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            {{-- Daily summary --}}
            @if($todayLogs->whereNotNull('clock_out')->isNotEmpty())
            @php
                $totalMinutes = $todayLogs->whereNotNull('clock_out')
                                          ->where('reason', 'Shift')
                                          ->sum('total_minutes');
                $hours = intdiv($totalMinutes, 60);
                $mins  = $totalMinutes % 60;
            @endphp
            <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end">
                <p class="text-sm text-gray-600">
                    Total Shift Time: <span class="font-semibold text-gray-900">{{ $hours }}h {{ $mins }}m</span>
                </p>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
    function updateClock() {
        const el = document.getElementById('live-clock');
        if (!el) return;
        const now = new Date();
        const h = now.getHours() % 12 || 12;
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');
        const ampm = now.getHours() >= 12 ? 'PM' : 'AM';
        el.textContent = `${h}:${m}:${s} ${ampm}`;
    }
    updateClock();
    setInterval(updateClock, 1000);
</script>
@endpush
@endsection
