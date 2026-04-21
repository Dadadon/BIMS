@extends('layouts.app')
@section('title', 'Edit Attendance Record')
@section('page-title', 'Edit Attendance Record')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.attendance.filter') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Attendance</a>
</div>

<div class="max-w-xl">
    <div class="mb-4 bg-white shadow rounded-lg px-6 py-4">
        <p class="text-sm font-semibold text-gray-900">{{ $log->employee?->display_name }}</p>
        <p class="text-xs text-gray-500">{{ $log->log_date }} · {{ $log->reason }}</p>
    </div>

    <form method="POST" action="{{ route('admin.attendance.update', $log) }}" class="bg-white shadow rounded-lg">
        @csrf @method('PUT')
        <div class="px-6 py-6 space-y-5">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-900">Clock In <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="clock_in" required
                           value="{{ old('clock_in', $log->clock_in->format('Y-m-d\TH:i')) }}"
                           class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">Clock Out</label>
                    <input type="datetime-local" name="clock_out"
                           value="{{ old('clock_out', $log->clock_out?->format('Y-m-d\TH:i')) }}"
                           class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-900">Reason</label>
                    <select name="reason"
                            class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                        @foreach(['Shift','Lunch','Break','OT'] as $r)
                        <option value="{{ $r }}" {{ old('reason', $log->reason) === $r ? 'selected' : '' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">Status In</label>
                    <input type="text" name="status_in" value="{{ old('status_in', $log->status_in) }}"
                           class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">Status Out</label>
                    <input type="text" name="status_out" value="{{ old('status_out', $log->status_out) }}"
                           class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900">Comment</label>
                <input type="text" name="comment" value="{{ old('comment', $log->comment) }}"
                       class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end gap-3">
            <a href="{{ route('admin.attendance.filter') }}"
               class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</a>
            <button type="submit"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
