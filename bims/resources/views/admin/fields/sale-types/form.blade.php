@extends('layouts.app')
@section('title', $saleType ? 'Edit Sale Type' : 'Add Sale Type')
@section('page-title', $saleType ? 'Edit Sale Type' : 'Add Sale Type')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.fields.sale-types.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Sale Types</a>
</div>
<div class="max-w-lg">
    <form method="POST"
          action="{{ $saleType ? route('admin.fields.sale-types.update', $saleType) : route('admin.fields.sale-types.store') }}"
          class="bg-white shadow rounded-lg">
        @csrf
        @if($saleType) @method('PUT') @endif
        <div class="px-6 py-6 space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-900">Product Category <span class="text-red-500">*</span></label>
                <input type="text" name="product_category" required
                       value="{{ old('product_category', $saleType?->product_category) }}"
                       placeholder="e.g. Internet, Internet + TV"
                       class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                @error('product_category') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-900">Portal</label>
                    <input type="text" name="portal"
                           value="{{ old('portal', $saleType?->portal) }}"
                           placeholder="e.g. Xfinity, AT&T"
                           class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">Product Code</label>
                    <input type="text" name="product_code"
                           value="{{ old('product_code', $saleType?->product_code) }}"
                           placeholder="e.g. INT-XFN-100"
                           class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-900">Total Points <span class="text-red-500">*</span></label>
                    <input type="number" name="total_points" step="1" min="0" required
                           value="{{ old('total_points', $saleType?->total_points ?? 0) }}"
                           class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">Agent Points <span class="text-red-500">*</span></label>
                    <input type="number" name="points_per_agent" step="0.01" min="0" required
                           value="{{ old('points_per_agent', $saleType?->points_per_agent ?? 0) }}"
                           class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    <p class="mt-1 text-xs text-gray-500">Used when company commission model is "sale_type_rate"</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                       {{ old('is_active', $saleType?->is_active ?? true) ? 'checked' : '' }}
                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                <label for="is_active" class="text-sm font-medium text-gray-900">Active</label>
            </div>
        </div>
        <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end gap-3">
            <a href="{{ route('admin.fields.sale-types.index') }}"
               class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                {{ $saleType ? 'Save' : 'Create' }}
            </button>
        </div>
    </form>
</div>
@endsection
