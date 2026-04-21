@extends('layouts.app')
@section('title', 'KPI Definitions')
@section('page-title', 'KPI Definitions')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-6">
    <div>
        <h2 class="text-xl font-semibold text-gray-900">KPI Definitions</h2>
        <a href="{{ route('admin.performance.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Performance</a>
    </div>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    {{-- Definitions list --}}
    <div class="lg:col-span-2 overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Name</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Module</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Metric</th>
                    <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Target</th>
                    <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Weight</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Active</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($definitions as $kpi)
                <tr class="hover:bg-gray-50">
                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $kpi->name }}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $kpi->module_key }}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 font-mono text-xs">{{ $kpi->metric_key }}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700 text-right">{{ $kpi->target }}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700 text-right">{{ $kpi->weight }}%</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                        @if($kpi->is_active)
                        <span class="inline-flex rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Yes</span>
                        @else
                        <span class="inline-flex rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">No</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-10 text-center text-sm text-gray-500">No KPI definitions.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Add new KPI --}}
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-900">Add KPI Definition</h3>
        </div>
        <form method="POST" action="{{ route('admin.performance.kpi.store') }}" class="px-6 py-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="name" required
                       class="block w-full rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Module</label>
                <select name="module_key"
                        class="block w-full rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600">
                    <option value="attendance">Attendance</option>
                    <option value="sales">Sales</option>
                    <option value="tasks">Tasks</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Metric Key</label>
                <select name="metric_key"
                        class="block w-full rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600">
                    <option value="late_in_count">late_in_count</option>
                    <option value="attendance_rate">attendance_rate</option>
                    <option value="total_agent_points">total_agent_points</option>
                    <option value="sale_count">sale_count</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Target</label>
                    <input type="number" name="target" step="0.01" min="0" required
                           class="block w-full rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Weight %</label>
                    <input type="number" name="weight" min="0" max="100" step="1" required
                           class="block w-full rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600">
                </div>
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="kpi_active" value="1" checked
                       class="h-4 w-4 rounded border-gray-300 text-indigo-600">
                <label for="kpi_active" class="text-xs text-gray-700">Active</label>
            </div>
            <button type="submit"
                    class="w-full rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                Add KPI
            </button>
        </form>
    </div>
</div>
@endsection
