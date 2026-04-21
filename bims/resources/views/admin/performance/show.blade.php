@extends('layouts.app')
@section('title', 'Performance — ' . $employee->display_name)
@section('page-title', 'Performance Details')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <a href="{{ route('admin.performance.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Performance</a>
        <h2 class="mt-1 text-lg font-semibold text-gray-900">{{ $employee->display_name }}</h2>
    </div>
</div>

<div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
            <tr>
                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">KPI</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Period</th>
                <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Raw Value</th>
                <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Score /100</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($snapshots as $snap)
            <tr class="hover:bg-gray-50">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                    <p class="font-medium text-gray-900">{{ $snap->kpi->name ?? 'Unknown KPI' }}</p>
                    <p class="text-xs text-gray-500">{{ $snap->kpi->module_key ?? '' }}</p>
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">
                    {{ \Carbon\Carbon::parse($snap->period_start)->format('M Y') }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 text-right">{{ number_format($snap->raw_value, 2) }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-right">
                    @php
                        $s = $snap->score;
                        $c = $s >= 80 ? 'text-green-600' : ($s >= 60 ? 'text-yellow-600' : 'text-red-600');
                    @endphp
                    <span class="font-bold {{ $c }}">{{ number_format($s, 1) }}</span>
                    <span class="text-xs text-gray-400">/100</span>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="py-10 text-center text-sm text-gray-500">No KPI data yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $snapshots->links() }}</div>
@endsection
