@extends('layouts.app')
@section('title', 'Leave Types')
@section('page-title', 'Leave Types')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-6">
    <h2 class="text-xl font-semibold text-gray-900">Leave Types</h2>
    <a href="{{ route('admin.fields.leave-types.create') }}"
       class="inline-flex items-center gap-x-1.5 rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
        + Add Leave Type
    </a>
</div>
<div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
            <tr>
                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Name</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Paid</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Max Days</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 hidden md:table-cell">Description</th>
                <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($leaveTypes as $lt)
            <tr class="hover:bg-gray-50">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $lt->name }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm">
                    @if($lt->is_paid)
                    <span class="inline-flex rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Paid</span>
                    @else
                    <span class="inline-flex rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">Unpaid</span>
                    @endif
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $lt->max_days ?? '∞' }}</td>
                <td class="px-3 py-4 text-sm text-gray-500 hidden md:table-cell max-w-xs truncate">{{ $lt->description ?? '—' }}</td>
                <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 space-x-2">
                    <a href="{{ route('admin.fields.leave-types.edit', $lt) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                    <form method="POST" action="{{ route('admin.fields.leave-types.destroy', $lt) }}" class="inline"
                          onsubmit="return confirm('Delete this leave type?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="py-10 text-center text-sm text-gray-500">No leave types yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
