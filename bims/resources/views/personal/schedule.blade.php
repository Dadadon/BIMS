@extends('layouts.app')
@section('title', 'My Schedule')
@section('page-title', 'My Schedule')

@section('content')

@if(! $employee)
<div class="rounded-md bg-yellow-50 p-4 mb-6">
    <p class="text-sm text-yellow-800">Your account is not linked to an employee profile. Contact your administrator.</p>
</div>
@else

{{-- This week header --}}
<div class="mb-6">
    <h2 class="text-xl font-semibold text-gray-900">This Week</h2>
    <p class="text-sm text-gray-500">{{ $weekStart->format('M j') }} – {{ $weekStart->copy()->endOfWeek()->format('M j, Y') }}</p>
</div>

{{-- Weekly grid --}}
<div class="grid grid-cols-7 gap-2 mb-10">
    @foreach($week as $day)
    @php
        $isToday    = $day['date']->isToday();
        $schedule   = $day['schedule'];
        $isPast     = $day['date']->isPast() && ! $isToday;
    @endphp
    <div class="rounded-lg border {{ $isToday ? 'border-indigo-400 ring-2 ring-indigo-300' : 'border-gray-200' }} bg-white overflow-hidden">
        {{-- Day header --}}
        <div class="px-2 py-2 text-center border-b {{ $isToday ? 'bg-indigo-600' : 'bg-gray-50' }} border-gray-100">
            <p class="text-xs font-semibold {{ $isToday ? 'text-indigo-100' : 'text-gray-500' }} uppercase tracking-wide">
                {{ $day['date']->format('D') }}
            </p>
            <p class="text-sm font-bold {{ $isToday ? 'text-white' : ($isPast ? 'text-gray-400' : 'text-gray-900') }}">
                {{ $day['date']->format('j') }}
            </p>
        </div>

        {{-- Shift --}}
        <div class="px-1.5 py-2 min-h-[64px] flex flex-col items-center justify-center gap-1">
            @if($schedule)
            <span class="w-full text-center text-[11px] font-semibold rounded px-1 py-1 text-white leading-tight"
                  style="background-color: {{ $schedule->color() }}">
                {{ $schedule->label() }}
            </span>
            <span class="text-[10px] text-gray-500">
                {{ \Carbon\Carbon::parse($schedule->shift_in)->format('g:ia') }}
                –
                {{ \Carbon\Carbon::parse($schedule->shift_out)->format('g:ia') }}
            </span>
            @else
            <span class="text-xs text-gray-300">—</span>
            @endif
        </div>
    </div>
    @endforeach
</div>

{{-- Active assignments --}}
<div class="mb-2 flex items-center justify-between">
    <h2 class="text-lg font-semibold text-gray-900">Active Assignments</h2>
</div>

@if($upcoming->isEmpty())
<div class="rounded-lg bg-white border border-gray-200 px-6 py-10 text-center">
    <p class="text-sm text-gray-500">No active shift assignments. Contact your manager.</p>
</div>
@else
<div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
            <tr>
                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Shift</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Time</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Days</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Effective</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @foreach($upcoming as $s)
            @php
                $days = collect($s->days_of_week ?? [])->map(fn($d) => ['1'=>'Mon','2'=>'Tue','3'=>'Wed','4'=>'Thu','5'=>'Fri','6'=>'Sat','7'=>'Sun'][$d] ?? $d)->join(', ');
                $from = $s->effective_from?->format('M j, Y') ?? '—';
                $to   = $s->effective_to?->format('M j, Y') ?? 'Ongoing';
            @endphp
            <tr class="hover:bg-gray-50">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 sm:pl-6">
                    <span class="inline-flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $s->color() }}"></span>
                        <span class="text-sm font-medium text-gray-900">{{ $s->label() }}</span>
                    </span>
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">
                    {{ \Carbon\Carbon::parse($s->shift_in)->format('g:ia') }}
                    –
                    {{ \Carbon\Carbon::parse($s->shift_out)->format('g:ia') }}
                    @if($s->break_minutes)
                    <span class="text-gray-400 text-xs ml-1">({{ $s->break_minutes }}m break)</span>
                    @endif
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">
                    {{ $days ?: 'All days' }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">
                    {{ $from }}
                    @if($s->effective_to)
                    → {{ $to }}
                    @else
                    <span class="text-green-600 font-medium">· Ongoing</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@endif
@endsection
