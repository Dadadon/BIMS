@extends('layouts.app')
@section('title', 'Sale Types')
@section('page-title', 'Sale Types')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-6">
    <h2 class="text-xl font-semibold text-gray-900">Sale Types</h2>
    <a href="{{ route('admin.fields.sale-types.create') }}"
       class="inline-flex items-center gap-x-1.5 rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
        + Add Sale Type
    </a>
</div>
<div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
            <tr>
                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Product / Category</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Portal</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Code</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Total Pts</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Agent Pts</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($saleTypes as $st)
            <tr class="hover:bg-gray-50">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $st->product_category }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $st->portal ?? '—' }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $st->product_code ?? '—' }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ number_format($st->total_points, 2) }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ number_format($st->points_per_agent, 2) }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm">
                    <span class="inline-flex rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
                                 {{ $st->is_active ? 'bg-green-50 text-green-700 ring-green-600/20' : 'bg-gray-50 text-gray-600 ring-gray-500/10' }}">
                        {{ $st->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 space-x-2">
                    <a href="{{ route('admin.fields.sale-types.edit', $st) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                    <form method="POST" action="{{ route('admin.fields.sale-types.destroy', $st) }}" class="inline"
                          onsubmit="return confirm('Delete this sale type?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                    </form>

                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="py-10 text-center text-sm text-gray-500">No sale types yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
