@extends('layouts.app')
@section('title', isset($report) ? 'Edit Report' : 'New Report')
@section('page-title', 'Reports')

@section('content')
@php
    $editing  = isset($report);
    $old      = fn($k, $d = null) => old($k, $editing ? data_get($report, $k, $d) : $d);
    $oldCols  = old('columns', $editing ? $report->columns : []);
    $oldFilts = old('filters', $editing ? ($report->filters ?? []) : []);
@endphp

<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-xl font-semibold text-gray-900">{{ $editing ? 'Edit Report' : 'New Report' }}</h2>
        <p class="mt-1 text-sm text-gray-500">Configure your data source, columns, filters, and visualization.</p>
    </div>
    <a href="{{ route('admin.reports.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← All Reports</a>
</div>

@if($errors->any())
<div class="mb-4 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
    <p class="font-semibold mb-1">Please fix the following:</p>
    <ul class="list-disc list-inside space-y-0.5">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST"
      action="{{ $editing ? route('admin.reports.update', $report) : route('admin.reports.store') }}"
      x-data="reportBuilder({{ json_encode($schema) }}, {{ json_encode($old('data_source', '')) }}, {{ json_encode($oldCols) }}, {{ json_encode($oldFilts) }}, '{{ $old('group_by', '') }}', '{{ $old('aggregate_field', '') }}', '{{ $old('chart_type', 'table') }}')">
    @if($editing) @method('PUT') @endif
    @csrf

    <div class="grid grid-cols-1 gap-8 xl:grid-cols-3">

        {{-- Left: config ──────────────────────────────────────────────── --}}
        <div class="xl:col-span-2 space-y-6">

            {{-- 1. Name + Source --}}
            <div class="rounded-lg bg-white shadow p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-900 border-b border-gray-100 pb-2">1. Basic Info</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-900">Report Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ $old('name') }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500"
                               placeholder="e.g. Monthly Sales by Team">
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-900">Description</label>
                        <input type="text" name="description" value="{{ $old('description') }}" maxlength="500"
                               class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500"
                               placeholder="Optional description">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-900">Data Source <span class="text-red-500">*</span></label>
                        <select name="data_source" x-model="source" @change="onSourceChange"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                            <option value="">— Select —</option>
                            <template x-for="(src, key) in schema" :key="key">
                                <option :value="key" :selected="source === key" x-text="src.label"></option>
                            </template>
                        </select>
                        @error('data_source')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- 2. Columns --}}
            <div class="rounded-lg bg-white shadow p-6" x-show="source">
                <h3 class="text-sm font-semibold text-gray-900 border-b border-gray-100 pb-2 mb-4">2. Columns to Display</h3>

                {{-- Grouped mode: columns are fixed, just explain what shows --}}
                <div x-show="groupBy" class="rounded-md bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-700 space-y-1">
                    <p class="font-medium">Grouped report — columns are determined by Group &amp; Aggregate settings.</p>
                    <p>The result will show: <strong x-text="currentColumns()[groupBy]?.label ?? groupBy"></strong> + the chosen aggregate value.</p>
                    <p class="text-xs text-blue-500">To show individual row columns, remove the Group By selection.</p>
                </div>

                {{-- Row-level mode: pick any columns --}}
                <div x-show="!groupBy">
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                        <template x-for="(col, key) in currentColumns()" :key="key">
                            <label class="flex items-center gap-2 text-sm cursor-pointer">
                                <input type="checkbox" name="columns[]" :value="key"
                                       x-model="selectedColumns"
                                       class="rounded border-gray-300 text-indigo-600">
                                <span x-text="col.label" class="text-gray-700"></span>
                                <template x-if="col.calculated">
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-1.5 py-0.5 text-xs font-medium text-amber-700">fx</span>
                                </template>
                                <template x-if="!col.calculated && key.startsWith('cf_')">
                                    <span class="inline-flex items-center rounded-full bg-purple-100 px-1.5 py-0.5 text-xs font-medium text-purple-700">custom</span>
                                </template>
                            </label>
                        </template>
                    </div>
                    <p class="mt-2 text-xs text-gray-400">
                        <span class="inline-flex items-center rounded-full bg-amber-100 px-1.5 py-0.5 font-medium text-amber-700 mr-1">fx</span> calculated &nbsp;
                        <span class="inline-flex items-center rounded-full bg-purple-100 px-1.5 py-0.5 font-medium text-purple-700 mr-1">custom</span> custom field
                    </p>
                    @error('columns')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- 3. Filters --}}
            <div class="rounded-lg bg-white shadow p-6" x-show="source">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2 mb-4">
                    <h3 class="text-sm font-semibold text-gray-900">3. Filters <span class="text-gray-400 font-normal">(optional)</span></h3>
                    <button type="button" @click="addFilter"
                            class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">+ Add Filter</button>
                </div>
                <div class="space-y-2">
                    <template x-for="(filter, i) in filters" :key="i">
                        <div class="flex items-center gap-2">
                            {{-- Field --}}
                            <select :name="'filters[' + i + '][field]'" x-model="filter.field"
                                    @change="onFilterFieldChange(filter)"
                                    class="flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                                <option value="">— Field —</option>
                                <template x-for="(col, key) in currentColumns()" :key="key">
                                    <option :value="key" x-text="col.label"></option>
                                </template>
                            </select>
                            {{-- Operator (changes by type) --}}
                            <select :name="'filters[' + i + '][op]'" x-model="filter.op"
                                    class="w-36 rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                                <template x-if="filter.fieldType === 'string'">
                                    <template x-for="opt in [{v:'=',l:'equals'},{v:'!=',l:'not equals'},{v:'contains',l:'contains'},{v:'starts_with',l:'starts with'}]" :key="opt.v">
                                        <option :value="opt.v" x-text="opt.l" :selected="filter.op === opt.v"></option>
                                    </template>
                                </template>
                                <template x-if="filter.fieldType === 'number'">
                                    <template x-for="opt in [{v:'=',l:'equals'},{v:'!=',l:'not equals'},{v:'>',l:'greater than'},{v:'<',l:'less than'},{v:'>=',l:'≥'},{v:'<=',l:'≤'}]" :key="opt.v">
                                        <option :value="opt.v" x-text="opt.l" :selected="filter.op === opt.v"></option>
                                    </template>
                                </template>
                                <template x-if="filter.fieldType === 'date'">
                                    <template x-for="opt in [{v:'=',l:'on'},{v:'!=',l:'not on'},{v:'>',l:'after'},{v:'<',l:'before'},{v:'>=',l:'on or after'},{v:'<=',l:'on or before'}]" :key="opt.v">
                                        <option :value="opt.v" x-text="opt.l" :selected="filter.op === opt.v"></option>
                                    </template>
                                </template>
                                <template x-if="!filter.fieldType">
                                    <option value="=">equals</option>
                                </template>
                            </select>
                            {{-- Value (input type matches field type) --}}
                            <template x-if="filter.fieldType === 'date'">
                                <input :name="'filters[' + i + '][value]'" x-model="filter.value" type="date"
                                       class="flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                            </template>
                            <template x-if="filter.fieldType === 'number'">
                                <input :name="'filters[' + i + '][value]'" x-model="filter.value" type="number" step="any"
                                       placeholder="Value"
                                       class="flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                            </template>
                            <template x-if="!filter.fieldType || filter.fieldType === 'string'">
                                <input :name="'filters[' + i + '][value]'" x-model="filter.value" type="text"
                                       placeholder="Value"
                                       class="flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                            </template>
                            <button type="button" @click="removeFilter(i)" class="text-red-400 hover:text-red-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                    <p x-show="filters.length === 0" class="text-xs text-gray-400 italic">No filters — all records will be included.</p>
                </div>
            </div>

            {{-- 4. Grouping + Aggregate --}}
            <div class="rounded-lg bg-white shadow p-6" x-show="source">
                <h3 class="text-sm font-semibold text-gray-900 border-b border-gray-100 pb-2 mb-4">4. Group &amp; Aggregate <span class="text-gray-400 font-normal">(required for charts)</span></h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-900">Group By</label>
                        <select name="group_by" x-model="groupBy"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                            <option value="">— None (row-level report) —</option>
                            <template x-for="key in groupableKeys()" :key="key">
                                <option :value="key" :selected="groupBy === key" x-text="currentColumns()[key]?.label ?? key"></option>
                            </template>
                        </select>
                    </div>
                    <div x-show="groupBy">
                        <label class="block text-sm font-medium text-gray-900">Aggregate</label>
                        <select name="aggregate_field" x-model="aggregateField"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                            <template x-for="agg in aggregatableOptions()" :key="agg.key">
                                <option :value="agg.key" :selected="aggregateField === agg.key" x-text="agg.label"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>

        </div>

        {{-- Right: visualization ─────────────────────────────────────── --}}
        <div class="space-y-6">

            {{-- 5. Chart Type --}}
            <div class="rounded-lg bg-white shadow p-6">
                <h3 class="text-sm font-semibold text-gray-900 border-b border-gray-100 pb-2 mb-4">5. Visualization</h3>

                {{-- No group by: only table makes sense --}}
                <div x-show="!groupBy">
                    <div class="flex flex-col items-center gap-1 rounded-lg p-4 ring-2 ring-indigo-600 bg-indigo-50 w-fit mx-auto">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h1.5C5.496 19.5 6 18.996 6 18.375m-3.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125M6 18.375v-6.75A1.125 1.125 0 017.125 10.5h9.75A1.125 1.125 0 0118 11.625v6.75M6 18.375A1.125 1.125 0 007.125 19.5h9.75A1.125 1.125 0 0018 18.375M6 18.375V11.25"/>
                        </svg>
                        <span class="text-xs font-medium text-indigo-700">Table</span>
                    </div>
                    <p class="mt-3 text-xs text-center text-gray-400">Add a Group By to enable charts.</p>
                    <input type="hidden" name="chart_type" value="table">
                </div>

                {{-- Group by set: all chart types available --}}
                <div x-show="groupBy">
                    <div class="grid grid-cols-3 gap-2">
                        @foreach([
                            ['table',    'Table',    'M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h1.5C5.496 19.5 6 18.996 6 18.375m-3.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125M6 18.375v-6.75A1.125 1.125 0 017.125 10.5h9.75A1.125 1.125 0 0118 11.625v6.75M6 18.375A1.125 1.125 0 007.125 19.5h9.75A1.125 1.125 0 0018 18.375M6 18.375V11.25'],
                            ['bar',      'Bar',      'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z'],
                            ['line',     'Line',     'M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941'],
                            ['area',     'Area',     'M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941'],
                            ['pie',      'Pie',      'M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z'],
                            ['doughnut', 'Doughnut', 'M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z'],
                        ] as [$type, $label, $icon])
                        <button type="button" @click="chartType = '{{ $type }}'"
                                :class="chartType === '{{ $type }}' ? 'ring-2 ring-indigo-600 bg-indigo-50' : 'ring-1 ring-gray-200 hover:bg-gray-50'"
                                class="flex flex-col items-center gap-1 rounded-lg p-3 transition-all">
                            <svg class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                            </svg>
                            <span class="text-xs font-medium text-gray-700">{{ $label }}</span>
                        </button>
                        @endforeach
                    </div>
                    <input type="hidden" name="chart_type" :value="chartType">
                </div>
            </div>

            {{-- Save --}}
            <div class="rounded-lg bg-white shadow p-6 space-y-3">
                <button type="submit"
                        class="w-full rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    {{ $editing ? 'Update Report' : 'Save &amp; Run Report' }}
                </button>
                <a href="{{ route('admin.reports.index') }}"
                   class="block w-full text-center rounded-md bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    Cancel
                </a>
            </div>

            {{-- Tips --}}
            <div class="rounded-lg bg-gray-50 border border-gray-200 px-4 py-3 text-xs text-gray-600 space-y-1">
                <p class="font-semibold text-gray-700">Tips</p>
                <p>• Select a <strong>Group By</strong> to aggregate data and enable charts.</p>
                <p>• Bar &amp; Line charts work best with ordered categories (month, team).</p>
                <p>• Pie &amp; Doughnut work best with 2–8 categories.</p>
                <p>• Row-level reports (no Group By) export to CSV best.</p>
            </div>
        </div>
    </div>
</form>

<script>
function reportBuilder(schema, initSource, initCols, initFilters, initGroupBy, initAggField, initChartType) {
    return {
        schema,
        source:         initSource  || '',
        selectedColumns: initCols   || [],
        filters:        (initFilters || []).map(f => ({
            ...f,
            fieldType: (schema[initSource]?.columns?.[f.field]?.type) || 'string',
        })),
        groupBy:        initGroupBy || '',
        aggregateField: initAggField || '',
        chartType:      initChartType || 'table',

        onSourceChange() {
            this.selectedColumns = [];
            this.filters = [];
            this.groupBy = '';
            this.aggregateField = '';
            this.chartType = 'table';
        },

        currentColumns() {
            return this.source && this.schema[this.source]
                ? this.schema[this.source].columns
                : {};
        },

        groupableKeys() {
            return this.source && this.schema[this.source]
                ? this.schema[this.source].groupable
                : [];
        },

        aggregatableOptions() {
            return this.source && this.schema[this.source]
                ? this.schema[this.source].aggregatable
                : [];
        },

        onFilterFieldChange(filter) {
            const col = this.currentColumns()[filter.field];
            filter.fieldType = col ? col.type : 'string';
            filter.value = '';
            // Reset op to a sensible default for the new type
            if (filter.fieldType === 'date')   filter.op = '>=';
            else if (filter.fieldType === 'number') filter.op = '=';
            else filter.op = '=';
        },

        addFilter() {
            this.filters.push({ field: '', op: '=', value: '', fieldType: '' });
        },

        removeFilter(i) {
            this.filters.splice(i, 1);
        },
    };
}
</script>
@endsection
