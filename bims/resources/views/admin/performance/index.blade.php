@extends('layouts.app')
@section('title', 'Performance')
@section('page-title', 'Performance')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-6">
    <div>
        <h2 class="text-xl font-semibold text-gray-900">Performance Scorecard</h2>
        <p class="text-sm text-gray-500 mt-1">{{ $month->format('F Y') }}</p>
    </div>
    <div class="mt-4 sm:mt-0 flex items-center gap-3">
        <form method="POST" action="{{ route('admin.performance.compute') }}">
            @csrf
            <button type="submit"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                Compute KPIs
            </button>
        </form>
        <a href="{{ route('admin.performance.kpi.index') }}"
           class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            KPI Definitions
        </a>
    </div>
</div>

<div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
            <tr>
                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Employee</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 hidden md:table-cell">Company</th>
                <th class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">Avg Score</th>
                <th class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">KPIs Tracked</th>
                <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">View</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($employees as $emp)
            @php
                $snaps   = $emp->kpiSnapshots;
                $avg     = $snaps->isNotEmpty() ? round($snaps->avg('score'), 1) : null;
                $color   = $avg === null ? 'text-gray-400'
                         : ($avg >= 80 ? 'text-green-600' : ($avg >= 60 ? 'text-yellow-600' : 'text-red-600'));
            @endphp
            <tr class="hover:bg-gray-50">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                    <p class="font-medium text-gray-900">{{ $emp->display_name }}</p>
                    <p class="text-xs text-gray-500">{{ $emp->jobTitle->title ?? '' }}</p>
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 hidden md:table-cell">{{ $emp->company->name ?? '—' }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-center">
                    <span class="text-lg font-bold {{ $color }}">
                        {{ $avg !== null ? $avg : '—' }}
                    </span>
                    @if($avg !== null)<span class="text-gray-400 text-xs">/100</span>@endif
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">{{ $snaps->count() }}</td>
                <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm sm:pr-6">
                    <a href="{{ route('admin.performance.show', $emp) }}" class="text-indigo-600 hover:text-indigo-900">Details</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="py-10 text-center text-sm text-gray-500">No active employees.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
