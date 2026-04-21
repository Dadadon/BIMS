@extends('layouts.app')
@section('title', 'My Dashboard')
@section('page-title', 'My Dashboard')

@section('content')
@if(! $employee)
<div class="rounded-md bg-yellow-50 p-4 mb-6">
    <p class="text-sm text-yellow-800">Your account is not linked to an employee profile. Contact your administrator.</p>
</div>
@else

{{-- Stat cards --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
    @php
        $totalH = intdiv($stats['hours_this_month'] ?? 0, 60);
        $totalM = ($stats['hours_this_month'] ?? 0) % 60;
    @endphp
    <div class="rounded-lg bg-white shadow px-5 py-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Hours This Month</p>
        <p class="mt-2 text-2xl font-bold text-gray-900">{{ $totalH }}h {{ $totalM }}m</p>
    </div>
    <div class="rounded-lg bg-white shadow px-5 py-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Late This Month</p>
        <p class="mt-2 text-2xl font-bold text-red-600">{{ $stats['late_this_month'] ?? 0 }}</p>
    </div>
    @module('sales')
    <div class="rounded-lg bg-white shadow px-5 py-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Sales This Month</p>
        <p class="mt-2 text-2xl font-bold text-indigo-600">{{ $stats['sales_this_month'] ?? 0 }}</p>
    </div>
    @endmodule
    <div class="rounded-lg bg-white shadow px-5 py-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Clock Status</p>
        @if($stats['open_log'] ?? null)
        <p class="mt-2 text-sm font-bold text-green-600">Clocked In</p>
        <p class="text-xs text-gray-500">Since {{ $stats['open_log']->clock_in->format('g:i A') }}</p>
        @else
        <p class="mt-2 text-sm font-bold text-gray-400">Not clocked in</p>
        @endif
    </div>
</div>

{{-- Quick actions --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
    <a href="{{ route('clock') }}"
       class="rounded-lg bg-indigo-600 text-white px-6 py-5 hover:bg-indigo-700 transition">
        <p class="font-semibold">SmartClock</p>
        <p class="text-indigo-200 text-sm mt-1">Clock in or out</p>
    </a>
    <a href="{{ route('my.attendance') }}"
       class="rounded-lg bg-white shadow px-6 py-5 hover:bg-gray-50 transition">
        <p class="font-semibold text-gray-900">My Attendance</p>
        <p class="text-gray-500 text-sm mt-1">View attendance history</p>
    </a>
    @module('leaves')
    <a href="{{ route('my.leaves') }}"
       class="rounded-lg bg-white shadow px-6 py-5 hover:bg-gray-50 transition">
        <p class="font-semibold text-gray-900">My Leaves</p>
        <p class="text-gray-500 text-sm mt-1">Request or view leave</p>
    </a>
    @endmodule
    @module('payroll')
    <a href="{{ route('my.payroll') }}"
       class="rounded-lg bg-white shadow px-6 py-5 hover:bg-gray-50 transition">
        <p class="font-semibold text-gray-900">My Payslips</p>
        <p class="text-gray-500 text-sm mt-1">Download payslips</p>
    </a>
    @endmodule
    @module('performance')
    <a href="{{ route('my.performance') }}"
       class="rounded-lg bg-white shadow px-6 py-5 hover:bg-gray-50 transition">
        <p class="font-semibold text-gray-900">My Performance</p>
        <p class="text-gray-500 text-sm mt-1">View KPI scores</p>
    </a>
    @endmodule
    @module('tasks')
    <a href="{{ route('my.tasks') }}"
       class="rounded-lg bg-white shadow px-6 py-5 hover:bg-gray-50 transition">
        <p class="font-semibold text-gray-900">My Tasks</p>
        <p class="text-gray-500 text-sm mt-1">View assigned tasks</p>
    </a>
    @endmodule
</div>
@endif
@endsection
