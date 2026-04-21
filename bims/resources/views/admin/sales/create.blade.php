@extends('layouts.app')
@section('title', 'Add Sale')
@section('page-title', 'Add Sale')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.sales.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Sales</a>
</div>
<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.sales.store') }}" class="bg-white shadow rounded-lg"
          x-data="saleForm()" x-init="init()">
        @csrf
        <div class="px-6 py-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-900">Employee <span class="text-red-500">*</span></label>
                <select name="employee_id" required
                        class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    <option value="">— Select —</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->display_name }}</option>
                    @endforeach
                </select>
                @error('employee_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-900">Sale Type <span class="text-red-500">*</span></label>
                <select name="sale_type_id" required x-model="saleTypeId"
                        class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    <option value="">— Select —</option>
                    @foreach($saleTypes as $st)
                    <option value="{{ $st->id }}" {{ old('sale_type_id') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-900">Customer Name <span class="text-red-500">*</span></label>
                <input type="text" name="customer_name" required value="{{ old('customer_name') }}"
                       class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-900">Customer Phone</label>
                <input type="text" name="customer_phone" value="{{ old('customer_phone') }}"
                       class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-900">Sale Date <span class="text-red-500">*</span></label>
                <input type="date" name="sale_date" required value="{{ old('sale_date', now()->format('Y-m-d')) }}"
                       class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-900">Status</label>
                <select name="status"
                        class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    @foreach(['Submitted','Approved','Cancelled','Pending Cancellation'] as $s)
                    <option value="{{ $s }}" {{ old('status', 'Submitted') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Dynamic custom fields --}}
            @php
                $allFields   = $fieldsBySaleType->get('all', collect());
                $fieldsByType = $fieldsBySaleType->except('all');
            @endphp
            <template x-for="field in visibleFields" :key="field.key">
                <div :class="field.field_type === 'textarea' ? 'sm:col-span-2' : ''">
                    <label class="block text-sm font-medium text-gray-900">
                        <span x-text="field.label"></span>
                        <span x-show="field.is_required" class="text-red-500">*</span>
                        <span x-show="field.field_type === 'calculated'" class="ml-1 text-xs font-normal text-purple-600">calculated</span>
                    </label>
                    {{-- Calculated: read-only live preview --}}
                    <template x-if="field.field_type === 'calculated'">
                        <div>
                            <input type="text" readonly
                                   :value="calcValues[field.key] !== undefined ? calcValues[field.key] : '—'"
                                   class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 bg-purple-50 text-purple-800 shadow-sm ring-1 ring-inset ring-purple-200 sm:text-sm cursor-not-allowed">
                            <p class="mt-1 text-xs text-gray-400 font-mono truncate" :title="field.formula" x-text="'= ' + (field.formula || '')"></p>
                        </div>
                    </template>
                    <template x-if="field.field_type === 'select'">
                        <select :name="'meta_' + field.key" :required="field.is_required"
                                @change="metaValues[field.key] = $event.target.value; recompute()"
                                class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                            <option value="">— Select —</option>
                            <template x-for="opt in field.options" :key="opt">
                                <option :value="opt" x-text="opt"></option>
                            </template>
                        </select>
                    </template>
                    <template x-if="field.field_type === 'textarea'">
                        <textarea :name="'meta_' + field.key" :required="field.is_required" rows="2"
                                  @input="metaValues[field.key] = $event.target.value; recompute()"
                                  class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm"></textarea>
                    </template>
                    <template x-if="field.field_type === 'checkbox'">
                        <input type="checkbox" :name="'meta_' + field.key" value="1"
                               @change="metaValues[field.key] = $event.target.checked; recompute()"
                               class="mt-2 h-4 w-4 rounded border-gray-300 text-indigo-600">
                    </template>
                    <template x-if="!['select','textarea','checkbox','calculated'].includes(field.field_type)">
                        <input :type="field.field_type === 'number' ? 'number' : (field.field_type === 'date' ? 'date' : 'text')"
                               :name="'meta_' + field.key" :required="field.is_required"
                               @input="metaValues[field.key] = $event.target.value; recompute()"
                               class="mt-2 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                    </template>
                </div>
            </template>
        </div>
        <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end gap-3">
            <a href="{{ route('admin.sales.index') }}"
               class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</a>
            <button type="submit"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                Create Sale
            </button>
        </div>
    </form>
</div>
<script>
function saleForm() {
    const allFields  = @json($fieldsBySaleType->get('all', collect())->values());
    const byType     = @json($fieldsBySaleType->except('all')->map->values());
    const builtins   = @json($builtinKeys);

    return {
        saleTypeId: '{{ old('sale_type_id') }}',
        visibleFields: [],
        metaValues: {},   // user-entered values keyed by field key
        calcValues: {},   // computed results for calculated fields

        init() {
            this.$watch('saleTypeId', () => this.updateFields());
            this.updateFields();
        },

        updateFields() {
            const id = String(this.saleTypeId);
            const typeFields = byType[id] ?? [];
            this.visibleFields = [...allFields, ...typeFields];
            this.recompute();
        },

        recompute() {
            const calcFields = this.visibleFields.filter(f => f.field_type === 'calculated');
            calcFields.forEach(field => {
                if (!field.formula) return;
                this.calcValues[field.key] = this.evalFormula(field.formula);
            });
        },

        evalFormula(formula) {
            // Build a variable context from metaValues + zeroed built-ins
            const ctx = {};
            builtins.forEach(k => ctx[k] = 0);
            Object.assign(ctx, this.metaValues);

            // Substitute variable names with their numeric values
            let expr = formula;
            // Sort by key length descending to avoid partial replacements
            const keys = Object.keys(ctx).sort((a, b) => b.length - a.length);
            keys.forEach(k => {
                const v = ctx[k];
                const safe = (v === null || v === undefined || v === '') ? 0
                           : (typeof v === 'boolean') ? (v ? 1 : 0)
                           : (isNaN(Number(v))) ? JSON.stringify(String(v))
                           : Number(v);
                expr = expr.replaceAll(k, safe);
            });

            try {
                // eslint-disable-next-line no-new-func
                const result = Function('"use strict"; return (' + expr + ')')();
                return (typeof result === 'number' && !isNaN(result))
                    ? Math.round(result * 10000) / 10000
                    : result;
            } catch {
                return '—';
            }
        },
    };
}
</script>
@endsection
