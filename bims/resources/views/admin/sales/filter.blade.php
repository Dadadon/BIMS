@extends('layouts.app')
@section('title', 'Sales Filter')
@section('page-title', 'Sales')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-6">
    <h2 class="text-xl font-semibold text-gray-900">Filter Sales</h2>
    <a href="{{ route('admin.sales.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← All Sales</a>
</div>

<form method="GET" class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6">
    <select name="team_id" class="rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600">
        <option value="">All Teams</option>
        @foreach($teams as $team)
        <option value="{{ $team->id }}" {{ request('team_id') == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
        @endforeach
    </select>
    <select name="employee_id" class="rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600">
        <option value="">All Employees</option>
        @foreach($employees as $emp)
        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->display_name }}</option>
        @endforeach
    </select>
    <select name="sale_type_id" class="rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600">
        <option value="">All Types</option>
        @foreach($saleTypes as $st)
        <option value="{{ $st->id }}" {{ request('sale_type_id') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
        @endforeach
    </select>
    <select name="status" class="rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600">
        <option value="">All Statuses</option>
        @foreach(['Submitted','Approved','Cancelled','Pending Cancellation'] as $s)
        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
        @endforeach
    </select>
    <input type="date" name="from" value="{{ request('from') }}" placeholder="From"
           class="rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600">
    <input type="date" name="to" value="{{ request('to') }}" placeholder="To"
           class="rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600">
    <div class="flex gap-2">
        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">Filter</button>
        <a href="{{ route('admin.sales.filter') }}" class="rounded-md bg-white px-3 py-1.5 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Clear</a>
    </div>
</form>

<div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
            <tr>
                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Customer</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Employee</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Type</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date</th>
                <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Agent Pts</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Comp.</th>
                <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($sales as $sale)
            <tr class="hover:bg-gray-50">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                    <p class="font-medium text-gray-900">{{ $sale->customer_name }}</p>
                    <p class="text-xs text-gray-500">{{ $sale->customer_phone }}</p>
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">{{ $sale->employee->display_name ?? '—' }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">{{ $sale->saleType->name ?? '—' }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">{{ \Carbon\Carbon::parse($sale->sale_date)->format('M j, Y') }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900 text-right">{{ number_format($sale->agent_points, 2) }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm">
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
                <td class="whitespace-nowrap px-3 py-4 text-sm">
                    @if($sale->compensation_received)
                        <span class="text-green-600 text-xs font-medium">Yes</span>
                    @else
                        <span class="text-gray-400 text-xs">No</span>
                    @endif
                </td>
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
            <tr><td colspan="8" class="py-10 text-center text-sm text-gray-500">No sales found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $sales->links() }}</div>
@endsection
