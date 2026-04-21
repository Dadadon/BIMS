@extends('layouts.app')
@section('title', 'Add Employee')
@section('page-title', 'Add Employee')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.employees.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Employees</a>
</div>

<form method="POST" action="{{ route('admin.employees.store') }}" enctype="multipart/form-data" class="space-y-8">
    @csrf
    @include('admin.employees._form')
    <div class="flex justify-end gap-3">
        <a href="{{ route('admin.employees.index') }}"
           class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            Cancel
        </a>
        <button type="submit"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            Create Employee
        </button>
    </div>
</form>
@endsection
