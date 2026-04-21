@extends('layouts.app')
@section('title', $report->name)
@section('page-title', 'Reports')

@section('content')
@php
    $rows    = $result['rows'];
    $columns = $result['columns'];
    $chart   = $result['chart'];
    $isChart = $report->isChart() && $chart !== null;
    $sources = ['sales' => 'Sales', 'payroll_slips' => 'Payroll Slips', 'attendance' => 'Attendance', 'employees' => 'Employees', 'leaves' => 'Leaves'];

    // Chart.js color palette
    $palette = ['#6366f1','#10b981','#f59e0b','#ef4444','#3b82f6','#8b5cf6','#ec4899','#14b8a6','#f97316','#84cc16'];
@endphp

{{-- Header --}}
<div class="sm:flex sm:items-start sm:justify-between mb-6 gap-4">
    <div>
        <div class="flex items-center gap-3">
            <h2 class="text-xl font-semibold text-gray-900">{{ $report->name }}</h2>
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-indigo-100 text-indigo-700">
                {{ $sources[$report->data_source] ?? $report->data_source }}
            </span>
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-600">
                {{ ucfirst($report->chart_type) }}
            </span>
        </div>
        @if($report->description)
        <p class="mt-1 text-sm text-gray-500">{{ $report->description }}</p>
        @endif
        <p class="mt-1 text-xs text-gray-400">{{ count($rows) }} row(s) returned</p>
    </div>
    <div class="flex items-center gap-2 mt-4 sm:mt-0 flex-shrink-0">
        <a href="{{ route('admin.reports.edit', $report) }}"
           class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            Edit
        </a>
        <a href="{{ route('admin.reports.export', $report) }}"
           class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            ↓ Export CSV
        </a>
        <a href="{{ route('admin.reports.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← All Reports</a>
    </div>
</div>

@if($isChart)
{{-- Chart + Table toggle --}}
<div x-data="{ view: 'chart' }" class="space-y-6">

    <div class="flex gap-1 rounded-lg bg-gray-100 p-1 w-fit">
        <button type="button" @click="view = 'chart'"
                :class="view === 'chart' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                class="rounded-md px-4 py-1.5 text-sm font-medium transition-all">
            Chart
        </button>
        <button type="button" @click="view = 'table'"
                :class="view === 'table' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                class="rounded-md px-4 py-1.5 text-sm font-medium transition-all">
            Table
        </button>
    </div>

    {{-- Chart --}}
    <div x-show="view === 'chart'" class="rounded-lg bg-white shadow p-6">
        <canvas id="reportChart" style="max-height: 420px;"></canvas>
    </div>

    {{-- Table --}}
    <div x-show="view === 'table'">
        @include('admin.reports._table', compact('columns', 'rows'))
    </div>
</div>

@else
{{-- Table only --}}
@include('admin.reports._table', compact('columns', 'rows'))
@endif

@if($isChart)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const ctx = document.getElementById('reportChart').getContext('2d');
    const type  = '{{ $report->chart_type }}';
    const labels = @json($chart['labels']);
    const values = @json($chart['datasets'][0]['data']);
    const label  = @json($chart['datasets'][0]['label']);
    const palette = @json($palette);

    const isRound = ['pie', 'doughnut'].includes(type);
    const isArea  = type === 'area';

    const dataset = {
        label,
        data: values,
        backgroundColor: isRound
            ? labels.map((_, i) => palette[i % palette.length])
            : (isArea ? palette[0] + '33' : palette[0]),
        borderColor: isRound ? '#fff' : palette[0],
        borderWidth: isRound ? 2 : 2,
        fill: isArea,
        tension: isArea ? 0.3 : 0,
        pointRadius: ['line','area'].includes(type) ? 4 : undefined,
    };

    new Chart(ctx, {
        type: type === 'area' ? 'line' : type,
        data: { labels, datasets: [dataset] },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: isRound, position: 'right' },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            const val = ctx.parsed.y ?? ctx.parsed;
                            return ' ' + label + ': ' + (Number.isInteger(val) ? val.toLocaleString() : val.toFixed(2));
                        }
                    }
                }
            },
            scales: isRound ? {} : {
                x: { grid: { color: '#f3f4f6' }, ticks: { maxRotation: 45 } },
                y: { grid: { color: '#f3f4f6' }, beginAtZero: true,
                     ticks: { callback: v => Number.isInteger(v) ? v.toLocaleString() : v.toFixed(1) } },
            },
        }
    });
})();
</script>
@endif
@endsection
