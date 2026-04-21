@extends('layouts.app')
@section('title', 'Review Leave Request')
@section('page-title', 'Review Leave Request')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.leaves.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Leaves</a>
</div>

<div class="max-w-xl">
    <div class="bg-white shadow rounded-lg mb-4 px-6 py-5">
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div><dt class="text-gray-500">Employee</dt><dd class="font-medium text-gray-900 mt-1">{{ $leave->employee->display_name }}</dd></div>
            <div><dt class="text-gray-500">Leave Type</dt><dd class="font-medium text-gray-900 mt-1">{{ $leave->leaveType->name ?? '—' }}</dd></div>
            <div><dt class="text-gray-500">Start Date</dt><dd class="font-medium text-gray-900 mt-1">{{ \Carbon\Carbon::parse($leave->date_from)->format('M j, Y') }}</dd></div>
            <div><dt class="text-gray-500">End Date</dt><dd class="font-medium text-gray-900 mt-1">{{ \Carbon\Carbon::parse($leave->date_to)->format('M j, Y') }}</dd></div>
            <div><dt class="text-gray-500">Total Days</dt><dd class="font-medium text-gray-900 mt-1">{{ $leave->total_days ?? '—' }}</dd></div>
            <div class="col-span-2"><dt class="text-gray-500">Reason</dt><dd class="mt-1 text-gray-900">{{ $leave->reason ?? '—' }}</dd></div>
        </dl>
    </div>

    <form method="POST" action="{{ route('admin.leaves.update', $leave) }}" class="bg-white shadow rounded-lg">
        @csrf @method('PUT')
        <div class="px-6 py-6">
            <label class="block text-sm font-medium text-gray-900">Decision <span class="text-red-500">*</span></label>
            <select name="status" required
                    class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                <option value="Approved">Approve</option>
                <option value="Rejected">Reject</option>
                <option value="Cancelled">Cancel</option>
            </select>
        </div>
        <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end gap-3">
            <a href="{{ route('admin.leaves.index') }}"
               class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</a>
            <button type="submit"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                Submit Decision
            </button>
        </div>
    </form>
</div>
@endsection
