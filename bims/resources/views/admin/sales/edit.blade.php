@extends('layouts.app')
@section('title', 'Edit Sale')
@section('page-title', 'Edit Sale')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.sales.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Sales</a>
</div>
<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.sales.update', $sale) }}" class="bg-white shadow rounded-lg">
        @csrf @method('PUT')
        <div class="px-6 py-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2 p-3 bg-gray-50 rounded-md text-sm text-gray-600">
                <strong>Employee:</strong> {{ $sale->employee->display_name }} ·
                <strong>Sale Type:</strong> {{ $sale->saleType->name }}
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-900">Customer Name <span class="text-red-500">*</span></label>
                <input type="text" name="customer_name" required value="{{ old('customer_name', $sale->customer_name) }}"
                       class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-900">Customer Phone</label>
                <input type="text" name="customer_phone" value="{{ old('customer_phone', $sale->customer_phone) }}"
                       class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-900">Sale Date <span class="text-red-500">*</span></label>
                <input type="date" name="sale_date" required value="{{ old('sale_date', \Carbon\Carbon::parse($sale->sale_date)->format('Y-m-d')) }}"
                       class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-900">Status</label>
                <select name="status"
                        class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    @foreach($statuses as $s)
                    <option value="{{ $s }}" {{ old('status', $sale->status) === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Custom metadata fields --}}
            @foreach($customFields as $field)
            @php $val = old("meta_{$field->key}", $sale->getMeta($field->key)); @endphp
            <div class="{{ $field->field_type === 'textarea' ? 'sm:col-span-2' : '' }}">
                <label class="block text-sm font-medium text-gray-900">
                    {{ $field->label }}
                    @if($field->is_required)<span class="text-red-500">*</span>@endif
                    @if($field->field_type === 'calculated')
                    <span class="ml-1 text-xs font-normal text-purple-600">calculated</span>
                    @endif
                </label>
                @if($field->field_type === 'calculated')
                {{-- Stored computed value, read-only --}}
                @php $display = $val !== null ? (is_numeric($val) ? rtrim(rtrim(number_format((float)$val, 4), '0'), '.') : $val) : '—'; @endphp
                <input type="text" readonly value="{{ $display }}"
                       class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 bg-purple-50 text-purple-800 shadow-sm ring-1 ring-inset ring-purple-200 sm:text-sm cursor-not-allowed">
                <p class="mt-1 text-xs text-gray-400 font-mono truncate" title="{{ $field->formula }}">= {{ $field->formula }}</p>
                @elseif($field->field_type === 'select')
                <select name="meta_{{ $field->key }}" {{ $field->is_required ? 'required' : '' }}
                        class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    <option value="">— Select —</option>
                    @foreach($field->options ?? [] as $opt)
                    <option value="{{ $opt }}" {{ $val === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                    @endforeach
                </select>
                @elseif($field->field_type === 'textarea')
                <textarea name="meta_{{ $field->key }}" {{ $field->is_required ? 'required' : '' }} rows="2"
                          class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">{{ $val }}</textarea>
                @elseif($field->field_type === 'checkbox')
                <input type="checkbox" name="meta_{{ $field->key }}" value="1" {{ $val ? 'checked' : '' }}
                       class="mt-2 h-4 w-4 rounded border-gray-300 text-indigo-600">
                @else
                <input type="{{ $field->field_type === 'number' ? 'number' : ($field->field_type === 'date' ? 'date' : 'text') }}"
                       name="meta_{{ $field->key }}" value="{{ $val }}" {{ $field->is_required ? 'required' : '' }}
                       class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                @endif
            </div>
            @endforeach
        </div>
        <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end gap-3">
            <a href="{{ route('admin.sales.index') }}"
               class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</a>
            <button type="submit"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
