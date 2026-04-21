@extends('layouts.app')
@section('title', 'My Sales')
@section('page-title', 'My Sales')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-6">
    <h2 class="text-xl font-semibold text-gray-900">{{ $month->format('F Y') }}</h2>
    <form method="GET" class="flex gap-2 mt-4 sm:mt-0">
        <input type="month" name="month" value="{{ $month->format('Y-m') }}"
               class="rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600">
        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">Go</button>
    </form>
</div>

<div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
            <tr>
                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Customer</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Type</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date</th>
                <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Agent Pts</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Compensated</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($sales as $sale)
            <tr class="hover:bg-gray-50">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                    <p class="font-medium text-gray-900">{{ $sale->customer_name }}</p>
                    <p class="text-xs text-gray-500">{{ $sale->customer_phone }}</p>
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">{{ $sale->saleType->name ?? '—' }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">{{ \Carbon\Carbon::parse($sale->sale_date)->format('M j, Y') }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-gray-900 text-right">{{ number_format($sale->agent_points, 2) }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">{{ $sale->status }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm">
                    @if($sale->compensation_received)
                    <span class="text-green-600 font-medium">Yes</span>
                    @else
                    <span class="text-gray-400">No</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="py-10 text-center text-sm text-gray-500">No sales this month.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $sales->links() }}</div>
@endsection
