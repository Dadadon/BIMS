@extends('layouts.app')
@section('title', 'Leave Groups')
@section('page-title', 'Leave Groups')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-6">
    <h2 class="text-xl font-semibold text-gray-900">Leave Groups</h2>
    <a href="{{ route('admin.fields.leave-groups.create') }}"
       class="inline-flex items-center gap-x-1.5 rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
        + Add Leave Group
    </a>
</div>
<div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
            <tr>
                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Name</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Employees</th>
                <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($leaveGroups as $lg)
            <tr class="hover:bg-gray-50">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $lg->name }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $lg->employees_count }}</td>
                <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 space-x-2">
                    <a href="{{ route('admin.fields.leave-groups.edit', $lg) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                    @if($lg->employees_count === 0)
                    <form method="POST" action="{{ route('admin.fields.leave-groups.destroy', $lg) }}" class="inline"
                          onsubmit="return confirm('Delete this leave group?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="3" class="py-10 text-center text-sm text-gray-500">No leave groups yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
