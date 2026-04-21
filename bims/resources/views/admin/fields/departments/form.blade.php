@extends('layouts.app')
@section('title', $department ? 'Edit Department' : 'Add Department')
@section('page-title', $department ? 'Edit Department' : 'Add Department')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.fields.departments.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Departments</a>
</div>
<div class="max-w-md">
    <form method="POST"
          action="{{ $department ? route('admin.fields.departments.update', $department) : route('admin.fields.departments.store') }}"
          class="bg-white shadow rounded-lg">
        @csrf
        @if($department) @method('PUT') @endif
        <div class="px-6 py-6">
            <label class="block text-sm font-medium text-gray-900">Department Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" required value="{{ old('name', $department?->name) }}"
                   class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end gap-3">
            <a href="{{ route('admin.fields.departments.index') }}"
               class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                {{ $department ? 'Save' : 'Create' }}
            </button>
        </div>
    </form>
</div>
@endsection
