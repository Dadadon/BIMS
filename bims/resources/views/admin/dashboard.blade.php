@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')

@section('content')
{{-- Stat cards --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
    <div class="rounded-lg bg-white shadow px-5 py-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Active Employees</p>
        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['total_employees'] }}</p>
    </div>
    <div class="rounded-lg bg-white shadow px-5 py-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Clocked In Now</p>
        <p class="mt-2 text-3xl font-bold text-green-600">{{ $stats['clocked_in_today'] }}</p>
    </div>
    <div class="rounded-lg bg-white shadow px-5 py-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Late Today</p>
        <p class="mt-2 text-3xl font-bold text-red-600">{{ $stats['late_today'] }}</p>
    </div>
    @module('sales')
    <div class="rounded-lg bg-white shadow px-5 py-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Sales This Month</p>
        <p class="mt-2 text-3xl font-bold text-indigo-600">{{ $monthlySales }}</p>
    </div>
    @endmodule
    @module('leaves')
    <div class="rounded-lg bg-white shadow px-5 py-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Pending Leaves</p>
        <p class="mt-2 text-3xl font-bold text-yellow-600">{{ $pendingLeaves }}</p>
    </div>
    @endmodule
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

    {{-- Attendance trend --}}
    <div class="lg:col-span-2 bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-900">Attendance — Last 7 Days</h3>
        </div>
        <div class="px-6 py-6">
            <div class="flex items-end gap-3 h-32">
                @php $max = max($attendanceTrend->pluck('count')->toArray() + [1]); @endphp
                @foreach($attendanceTrend as $day)
                @php $height = $max > 0 ? ($day['count'] / $max) * 100 : 0; @endphp
                <div class="flex flex-col items-center flex-1 gap-1">
                    <span class="text-xs text-gray-700 font-medium">{{ $day['count'] }}</span>
                    <div class="w-full rounded-t bg-indigo-500" style="height: {{ $height }}%; min-height: {{ $day['count'] > 0 ? '4px' : '0' }};"></div>
                    <span class="text-xs text-gray-400">{{ $day['date'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Quick links --}}
    <div class="space-y-4">
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Quick Links</h3>
            </div>
            <ul class="divide-y divide-gray-100">
                @module('hr')
                <li><a href="{{ route('admin.employees.create') }}" class="flex items-center justify-between px-6 py-3 text-sm text-gray-700 hover:bg-gray-50">Add Employee <span class="text-gray-400">→</span></a></li>
                @endmodule
                @module('attendance')
                <li><a href="{{ route('admin.attendance.index') }}" class="flex items-center justify-between px-6 py-3 text-sm text-gray-700 hover:bg-gray-50">Today's Attendance <span class="text-gray-400">→</span></a></li>
                @endmodule
                @module('leaves')
                <li><a href="{{ route('admin.leaves.index') }}" class="flex items-center justify-between px-6 py-3 text-sm text-gray-700 hover:bg-gray-50">Pending Leaves <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">{{ $pendingLeaves }}</span></a></li>
                @endmodule
                @module('payroll')
                <li><a href="{{ route('admin.payroll.index') }}" class="flex items-center justify-between px-6 py-3 text-sm text-gray-700 hover:bg-gray-50">Payroll <span class="text-gray-400">→</span></a></li>
                @endmodule
            </ul>
        </div>

        {{-- Pending leaves --}}
        @module('leaves')
        @if($recentLeaves->isNotEmpty())
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-sm font-semibold text-gray-900">Pending Leaves</h3>
                <a href="{{ route('admin.leaves.index') }}" class="text-xs text-indigo-600 hover:text-indigo-900">View all</a>
            </div>
            <ul class="divide-y divide-gray-100">
                @foreach($recentLeaves as $leave)
                <li class="px-6 py-3">
                    <p class="text-sm font-medium text-gray-900">{{ $leave->employee->display_name }}</p>
                    <p class="text-xs text-gray-500">
                        {{ $leave->leaveType->name ?? '—' }} ·
                        {{ $leave->date_from->format('M j') }}–{{ $leave->date_to->format('M j') }}
                    </p>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
        @endmodule
    </div>
</div>
@endsection
