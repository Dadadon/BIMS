@extends('layouts.app')
@section('title', 'Reports')
@section('page-title', 'Reports')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-6">
    <div>
        <h2 class="text-xl font-semibold text-gray-900">Saved Reports</h2>
        <p class="mt-1 text-sm text-gray-500">Build, save, and run reports with charts and CSV export.</p>
    </div>
    <a href="{{ route('admin.reports.create') }}"
       class="mt-4 sm:mt-0 inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
        + New Report
    </a>
</div>

@if($reports->isEmpty())
<div class="mt-12 text-center">
    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
    </svg>
    <h3 class="mt-2 text-sm font-semibold text-gray-900">No reports yet</h3>
    <p class="mt-1 text-sm text-gray-500">Create your first report to start analyzing data.</p>
    <a href="{{ route('admin.reports.create') }}"
       class="mt-4 inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
        + New Report
    </a>
</div>
@else
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach($reports as $report)
    @php
        $chartIcons = [
            'table'    => 'M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0112 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M13.125 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M20.625 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5',
            'bar'      => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
            'line'     => 'M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941',
            'area'     => 'M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941',
            'pie'      => 'M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z',
            'doughnut' => 'M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z',
        ];
        $sources = ['sales' => 'Sales', 'payroll_slips' => 'Payroll', 'attendance' => 'Attendance', 'employees' => 'Employees', 'leaves' => 'Leaves'];
    @endphp
    <div class="rounded-lg bg-white shadow ring-1 ring-gray-200 flex flex-col">
        <div class="p-5 flex-1">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="font-semibold text-gray-900">{{ $report->name }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $sources[$report->data_source] ?? $report->data_source }}</p>
                </div>
                <span class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                    {{ $report->chart_type === 'table' ? 'bg-gray-100 text-gray-600' : 'bg-indigo-100 text-indigo-700' }}">
                    {{ ucfirst($report->chart_type) }}
                </span>
            </div>
            @if($report->description)
            <p class="mt-2 text-sm text-gray-500 line-clamp-2">{{ $report->description }}</p>
            @endif
            <div class="mt-3 flex flex-wrap gap-1">
                @foreach(array_slice($report->columns, 0, 4) as $col)
                <span class="inline-flex rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-600">{{ $col }}</span>
                @endforeach
                @if(count($report->columns) > 4)
                <span class="text-xs text-gray-400">+{{ count($report->columns) - 4 }} more</span>
                @endif
            </div>
        </div>
        <div class="border-t border-gray-100 px-5 py-3 flex items-center justify-between bg-gray-50 rounded-b-lg">
            <span class="text-xs text-gray-400">{{ $report->updated_at->diffForHumans() }}</span>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.reports.edit', $report) }}" class="text-xs text-gray-500 hover:text-gray-700">Edit</a>
                <form method="POST" action="{{ route('admin.reports.destroy', $report) }}" class="inline"
                      onsubmit="return confirm('Delete \'{{ $report->name }}\'?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs text-red-500 hover:text-red-700">Delete</button>
                </form>
                <a href="{{ route('admin.reports.show', $report) }}"
                   class="rounded-md bg-indigo-600 px-3 py-1 text-xs font-semibold text-white hover:bg-indigo-500">
                    Run →
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
