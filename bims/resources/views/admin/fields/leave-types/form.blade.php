@extends('layouts.app')
@section('title', $leaveType ? 'Edit Leave Type' : 'Add Leave Type')
@section('page-title', $leaveType ? 'Edit Leave Type' : 'Add Leave Type')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.fields.leave-types.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Leave Types</a>
</div>
<div class="max-w-lg">
    <form method="POST"
          action="{{ $leaveType ? route('admin.fields.leave-types.update', $leaveType) : route('admin.fields.leave-types.store') }}"
          class="bg-white shadow rounded-lg">
        @csrf
        @if($leaveType) @method('PUT') @endif
        <div class="px-6 py-6 space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-900">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required value="{{ old('name', $leaveType?->name) }}"
                       class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-900">Leave Group</label>
                <select name="leave_group_id"
                        class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    <option value="">— None —</option>
                    @foreach(\App\Models\HR\LeaveGroup::orderBy('name')->get() as $lg)
                    <option value="{{ $lg->id }}" {{ old('leave_group_id', $leaveType?->leave_group_id) == $lg->id ? 'selected' : '' }}>
                        {{ $lg->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-3">
                <input type="hidden" name="is_paid" value="0">
                <input type="checkbox" name="is_paid" id="is_paid" value="1"
                       {{ old('is_paid', $leaveType?->is_paid ?? true) ? 'checked' : '' }}
                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                <label for="is_paid" class="text-sm font-medium text-gray-900">Paid leave</label>
            </div>
        </div>
        <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end gap-3">
            <a href="{{ route('admin.fields.leave-types.index') }}"
               class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                {{ $leaveType ? 'Save' : 'Create' }}
            </button>
        </div>
    </form>
</div>
@endsection
