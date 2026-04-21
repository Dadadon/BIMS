@extends('layouts.app')
@section('title', 'Employees')
@section('page-title', 'Employees')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-6">
    <div>
        <h2 class="text-xl font-semibold text-gray-900">All Employees</h2>
        <p class="mt-1 text-sm text-gray-500">{{ $employees->total() }} total</p>
    </div>
    @permission('hr', 'create')
    <a href="{{ route('admin.employees.create') }}"
       class="mt-4 sm:mt-0 inline-flex items-center gap-x-1.5 rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
        <svg class="-ml-0.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z"/>
        </svg>
        Add Employee
    </a>
    @endpermission
</div>

{{-- Filters --}}
<form method="GET" class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-4">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, code, email…"
           class="rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:col-span-2">
    <select name="company_id" class="rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600">
        <option value="">All Companies</option>
        @foreach($companies as $company)
        <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
            {{ $company->name }}
        </option>
        @endforeach
    </select>
    <select name="status" class="rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600">
        <option value="">All Statuses</option>
        @foreach(['Active', 'Inactive', 'Terminated', 'On Leave'] as $s)
        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
        @endforeach
    </select>
    <div class="flex gap-2 sm:col-span-4 sm:justify-end">
        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">Filter</button>
        <a href="{{ route('admin.employees.index') }}" class="rounded-md bg-white px-3 py-1.5 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Clear</a>
    </div>
</form>

{{-- Table --}}
<div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
            <tr>
                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Employee</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 hidden md:table-cell">Company / Dept</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 hidden lg:table-cell">Job Title</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($employees as $emp)
            <tr class="hover:bg-gray-50">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 sm:pl-6">
                    <div class="flex items-center gap-x-3">
                        <div class="h-9 w-9 shrink-0 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-semibold text-sm">
                            {{ strtoupper(substr($emp->firstname, 0, 1) . substr($emp->lastname, 0, 1)) }}
                        </div>
                        <div>
                            <a href="{{ route('admin.employees.show', $emp) }}" class="font-medium text-gray-900 hover:text-indigo-600">
                                {{ $emp->display_name }}
                            </a>
                            <p class="text-xs text-gray-500">{{ $emp->employee_code }}</p>
                        </div>
                    </div>
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 hidden md:table-cell">
                    <div>{{ $emp->company->name ?? '—' }}</div>
                    <div class="text-xs">{{ $emp->department->name ?? '—' }}</div>
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 hidden lg:table-cell">
                    {{ $emp->jobTitle->title ?? '—' }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm">
                    @php
                        $color = match($emp->employment_status) {
                            'Active'     => 'bg-green-50 text-green-700 ring-green-600/20',
                            'On Leave'   => 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
                            'Inactive'   => 'bg-gray-50 text-gray-600 ring-gray-500/10',
                            'Terminated' => 'bg-red-50 text-red-700 ring-red-600/20',
                            default      => 'bg-gray-50 text-gray-600 ring-gray-500/10',
                        };
                    @endphp
                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $color }}">
                        {{ $emp->employment_status }}
                    </span>
                </td>
                <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                    <a href="{{ route('admin.employees.show', $emp) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                    @permission('hr', 'edit')
                    <a href="{{ route('admin.employees.edit', $emp) }}" class="text-gray-600 hover:text-gray-900">Edit</a>
                    @endpermission
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="py-10 text-center text-sm text-gray-500">No employees found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $employees->links() }}</div>
@endsection
