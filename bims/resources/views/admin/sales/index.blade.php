@extends('layouts.app')
@section('title', 'Sales')
@section('page-title', 'Sales')

@section('content')
<div x-data="salesTable()" x-init="init()">

<div class="sm:flex sm:items-center sm:justify-between mb-6">
    <div>
        <h2 class="text-xl font-semibold text-gray-900">Sales</h2>
        <p class="text-sm text-gray-500 mt-1">{{ $sales->total() }} total</p>
    </div>
    <div class="flex gap-3 mt-4 sm:mt-0 items-center">
        {{-- Column visibility toggle --}}
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open"
                    class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Columns
            </button>
            <div x-show="open" @click.outside="open = false"
                 class="absolute right-0 mt-1 w-52 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 z-10 p-3 space-y-2">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Toggle Columns</p>
                <template x-for="col in columns" :key="col.key">
                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="checkbox" :checked="col.visible" @change="toggleCol(col.key)"
                               class="rounded border-gray-300 text-indigo-600">
                        <span x-text="col.label"></span>
                    </label>
                </template>
            </div>
        </div>
        <a href="{{ route('admin.sales.filter') }}"
           class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            Filter
        </a>
        <a href="{{ route('admin.sales.create') }}"
           class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            + Add Sale
        </a>
    </div>
</div>

<div class="overflow-x-auto shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
            <tr>
                <th x-show="isVisible('customer')"   class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Customer</th>
                <th x-show="isVisible('employee')"   class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Employee</th>
                <th x-show="isVisible('sale_type')"  class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Sale Type</th>
                <th x-show="isVisible('sale_date')"  class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date</th>
                <th x-show="isVisible('points')"     class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Pts</th>
                <th x-show="isVisible('status')"     class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                <th x-show="isVisible('comp')"       class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Comp.</th>
                @foreach($tableFields as $tf)
                <th x-show="isVisible('meta_{{ $tf->key }}')"
                    class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{ $tf->label }}</th>
                @endforeach
                <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($sales as $sale)
            <tr class="hover:bg-gray-50">
                <td x-show="isVisible('customer')"  class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                    <p class="font-medium text-gray-900">{{ $sale->customer_name }}</p>
                    <p class="text-xs text-gray-500">{{ $sale->customer_phone }}</p>
                </td>
                <td x-show="isVisible('employee')"  class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">{{ $sale->employee->display_name ?? '—' }}</td>
                <td x-show="isVisible('sale_type')" class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">{{ $sale->saleType->name ?? '—' }}</td>
                <td x-show="isVisible('sale_date')" class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">{{ \Carbon\Carbon::parse($sale->sale_date)->format('M j, Y') }}</td>
                <td x-show="isVisible('points')"    class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 text-right font-medium">{{ number_format($sale->agent_points, 2) }}</td>
                <td x-show="isVisible('status')"    class="whitespace-nowrap px-3 py-4 text-sm">
                    @php
                        $c = match($sale->status) {
                            'Approved'             => 'bg-green-50 text-green-700 ring-green-600/20',
                            'Submitted'            => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                            'Cancelled'            => 'bg-red-50 text-red-700 ring-red-600/20',
                            'Pending Cancellation' => 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
                            default                => 'bg-gray-50 text-gray-600 ring-gray-500/10',
                        };
                    @endphp
                    <span class="inline-flex rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $c }}">{{ $sale->status }}</span>
                </td>
                <td x-show="isVisible('comp')" class="whitespace-nowrap px-3 py-4 text-sm">
                    @if($sale->compensation_received)
                    <span class="text-green-600 text-xs font-medium">Yes</span>
                    @else
                    <span class="text-gray-400 text-xs">No</span>
                    @endif
                </td>
                @foreach($tableFields as $tf)
                <td x-show="isVisible('meta_{{ $tf->key }}')" class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">
                    {{ $sale->getMeta($tf->key, '—') }}
                </td>
                @endforeach
                <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 space-x-2">
                    <a href="{{ route('admin.sales.edit', $sale) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                    @if(! $sale->compensation_received && $sale->status === 'Approved')
                    <form method="POST" action="{{ route('admin.sales.compensate', $sale) }}" class="inline">
                        @csrf
                        <button type="submit" class="text-green-600 hover:text-green-900">Mark Paid</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="20" class="py-10 text-center text-sm text-gray-500">No sales found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $sales->links() }}</div>

</div>

<script>
function salesTable() {
    const STORAGE_KEY = 'bims_sales_columns';
    const metaFields  = @json($tableFields->map(fn($f) => ['key' => 'meta_' . $f->key, 'label' => $f->label])->values());

    const defaultColumns = [
        { key: 'customer',  label: 'Customer',  visible: true },
        { key: 'employee',  label: 'Employee',  visible: true },
        { key: 'sale_type', label: 'Sale Type', visible: true },
        { key: 'sale_date', label: 'Date',      visible: true },
        { key: 'points',    label: 'Points',    visible: true },
        { key: 'status',    label: 'Status',    visible: true },
        { key: 'comp',      label: 'Comp.',     visible: true },
        ...metaFields.map(f => ({ ...f, visible: true })),
    ];

    return {
        columns: [],
        init() {
            const saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || 'null');
            if (saved) {
                // Merge saved visibility into defaults (handles new columns being added)
                this.columns = defaultColumns.map(col => ({
                    ...col,
                    visible: saved[col.key] !== undefined ? saved[col.key] : col.visible,
                }));
            } else {
                this.columns = defaultColumns;
            }
        },
        isVisible(key) {
            const col = this.columns.find(c => c.key === key);
            return col ? col.visible : true;
        },
        toggleCol(key) {
            const col = this.columns.find(c => c.key === key);
            if (col) col.visible = !col.visible;
            const prefs = Object.fromEntries(this.columns.map(c => [c.key, c.visible]));
            localStorage.setItem(STORAGE_KEY, JSON.stringify(prefs));
        },
    };
}
</script>
@endsection
