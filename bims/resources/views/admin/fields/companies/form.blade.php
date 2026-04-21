@extends('layouts.app')
@section('title', $company ? 'Edit Company' : 'Add Company')
@section('page-title', $company ? 'Edit Company' : 'Add Company')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.fields.companies.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Companies</a>
</div>

<div class="max-w-2xl">
    <form method="POST"
          action="{{ $company ? route('admin.fields.companies.update', $company) : route('admin.fields.companies.store') }}"
          class="bg-white shadow rounded-lg">
        @csrf
        @if($company) @method('PUT') @endif

        <div class="px-6 py-6 space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-900">Company Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required
                       value="{{ old('name', $company?->name) }}"
                       class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900">Commission Model <span class="text-red-500">*</span></label>
                <select name="commission_model" required
                        class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    <option value="sale_type_rate" {{ old('commission_model', $company?->commission_model) === 'sale_type_rate' ? 'selected' : '' }}>
                        Sale Type Rate (MPV direct agents)
                    </option>
                    <option value="company_percentage" {{ old('commission_model', $company?->commission_model) === 'company_percentage' ? 'selected' : '' }}>
                        Company Percentage (third-party centers)
                    </option>
                </select>
                <p class="mt-1 text-xs text-gray-500">
                    Sale Type Rate uses the points_per_agent defined on each sale type.
                    Company Percentage multiplies total_points × commission_rate%.
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-900">Commission Rate %</label>
                <input type="number" name="commission_rate" step="0.01" min="0" max="100"
                       value="{{ old('commission_rate', $company?->commission_rate ?? 0) }}"
                       class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                <p class="mt-1 text-xs text-gray-500">Only used for the Company Percentage model.</p>
            </div>

            <div class="flex items-center gap-3">
                <input type="hidden" name="is_primary" value="0">
                <input type="checkbox" name="is_primary" id="is_primary" value="1"
                       {{ old('is_primary', $company?->is_primary) ? 'checked' : '' }}
                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                <label for="is_primary" class="text-sm font-medium text-gray-900">Primary company (main employer)</label>
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end gap-3">
            <a href="{{ route('admin.fields.companies.index') }}"
               class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                {{ $company ? 'Save Changes' : 'Create Company' }}
            </button>
        </div>
    </form>
</div>
@endsection
