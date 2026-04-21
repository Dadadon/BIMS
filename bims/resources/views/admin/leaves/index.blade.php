@extends('layouts.app')
@section('title', 'Leave Requests')
@section('page-title', 'Leave Requests')

@section('content')
<div class="mb-6">
    <h2 class="text-xl font-semibold text-gray-900">Pending Requests</h2>
    <p class="text-sm text-gray-500 mt-1">{{ $leaves->total() }} pending</p>
</div>

<div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
            <tr>
                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Employee</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Leave Type</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Dates</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Days</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 hidden md:table-cell">Reason</th>
                <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($leaves as $leave)
            <tr class="hover:bg-gray-50">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                    <p class="font-medium text-gray-900">{{ $leave->employee->display_name }}</p>
                    <p class="text-xs text-gray-500">{{ $leave->employee->department->name ?? '' }}</p>
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">{{ $leave->leaveType->name ?? '—' }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">
                    {{ $leave->date_from->format('M j') }}
                    – {{ $leave->date_to->format('M j, Y') }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">
                    {{ $leave->total_days ?? ($leave->date_from->diffInWeekdays($leave->date_to) + 1) }}
                </td>
                <td class="px-3 py-4 text-sm text-gray-500 max-w-xs truncate hidden md:table-cell">
                    {{ $leave->reason ?? '—' }}
                </td>
                <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 space-x-2">
                    <a href="{{ route('admin.leaves.edit', $leave) }}" class="text-indigo-600 hover:text-indigo-900">Review</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="py-10 text-center text-sm text-gray-500">No pending leave requests.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $leaves->links() }}</div>
@endsection
