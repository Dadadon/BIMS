@extends('layouts.app')
@section('title', 'Companies')
@section('page-title', 'Companies')

@section('content')
<div class="sm:flex sm:items-center sm:justify-between mb-6">
    <h2 class="text-xl font-semibold text-gray-900">Companies</h2>
    <a href="{{ route('admin.fields.companies.create') }}"
       class="inline-flex items-center gap-x-1.5 rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
        + Add Company
    </a>
</div>

<div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
            <tr>
                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Name</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Commission Model</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Rate %</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Employees</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Primary</th>
                <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($companies as $company)
            <tr class="hover:bg-gray-50">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $company->name }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                    {{ $company->commission_model === 'sale_type_rate' ? 'Sale Type Rate' : 'Company %' }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ number_format($company->commission_rate, 2) }}%</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $company->employees_count }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm">
                    @if($company->is_primary)
                    <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Primary</span>
                    @endif
                </td>
                <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 space-x-2">
                    <a href="{{ route('admin.fields.companies.edit', $company) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                    @if($company->employees_count === 0)
                    <form method="POST" action="{{ route('admin.fields.companies.destroy', $company) }}" class="inline"
                          onsubmit="return confirm('Delete this company?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="py-10 text-center text-sm text-gray-500">No companies yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
