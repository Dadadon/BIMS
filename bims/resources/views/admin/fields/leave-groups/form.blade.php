@extends('layouts.app')
@section('title', $leaveGroup ? 'Edit Leave Group' : 'Add Leave Group')
@section('page-title', $leaveGroup ? 'Edit Leave Group' : 'Add Leave Group')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.fields.leave-groups.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Leave Groups</a>
</div>
<div class="max-w-md">
    <form method="POST"
          action="{{ $leaveGroup ? route('admin.fields.leave-groups.update', $leaveGroup) : route('admin.fields.leave-groups.store') }}"
          class="bg-white shadow rounded-lg">
        @csrf
        @if($leaveGroup) @method('PUT') @endif
        <div class="px-6 py-6">
            <label class="block text-sm font-medium text-gray-900">Group Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" required value="{{ old('name', $leaveGroup?->name) }}"
                   class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end gap-3">
            <a href="{{ route('admin.fields.leave-groups.index') }}"
               class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                {{ $leaveGroup ? 'Save' : 'Create' }}
            </button>
        </div>
    </form>
</div>
@endsection
