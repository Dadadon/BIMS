@extends('layouts.app')
@section('title', 'Sale Custom Fields')
@section('page-title', 'Sale Custom Fields')

@section('content')
<div class="grid grid-cols-1 gap-8 lg:grid-cols-3">

    {{-- Field list --}}
    <div class="lg:col-span-2">
        <div class="overflow-x-auto shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Field</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Type</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Sale Type</th>
                        <th class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">Req.</th>
                        <th class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900 whitespace-nowrap">On Create</th>
                        <th class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900 whitespace-nowrap">In Table</th>
                        <th class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">Active</th>
                        <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>

                @forelse($fields as $field)
                {{-- One tbody per field so both rows share the same Alpine scope --}}
                <tbody x-data="{
                    editing: false,
                    type: {{ json_encode($field->field_type) }},
                    formula: {{ json_encode($field->formula ?? '') }},
                    openBuilder() {
                        $store.fb.open(this.formula, v => { this.formula = v; });
                    }
                }" class="divide-y divide-gray-200 bg-white">

                    {{-- Display row --}}
                    <tr class="hover:bg-gray-50">
                        <td class="py-4 pl-4 pr-3 text-sm sm:pl-6">
                            <p class="font-medium text-gray-900">{{ $field->label }}</p>
                            <p class="text-xs text-gray-400 font-mono">{{ $field->key }}</p>
                            @if($field->field_type === 'calculated' && $field->formula)
                            <p class="text-xs text-purple-600 font-mono mt-0.5 truncate max-w-[180px]" title="{{ $field->formula }}">= {{ $field->formula }}</p>
                            @endif
                        </td>
                        <td class="px-3 py-4 text-sm text-gray-500">
                            @if($field->field_type === 'calculated')
                            <span class="inline-flex items-center rounded-md bg-purple-50 px-2 py-1 text-xs font-medium text-purple-700 ring-1 ring-inset ring-purple-600/20">calculated</span>
                            @else
                            {{ $field->field_type }}
                            @endif
                        </td>
                        <td class="px-3 py-4 text-sm text-gray-500">{{ $field->saleType?->name ?? 'All types' }}</td>
                        <td class="px-3 py-4 text-sm text-center">{{ $field->is_required ? '✓' : '—' }}</td>
                        <td class="px-3 py-4 text-sm text-center">
                            @if($field->show_on_create)
                            <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20">Yes</span>
                            @else<span class="text-gray-300">—</span>@endif
                        </td>
                        <td class="px-3 py-4 text-sm text-center">
                            @if($field->show_in_table)
                            <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-600/20">Yes</span>
                            @else<span class="text-gray-300">—</span>@endif
                        </td>
                        <td class="px-3 py-4 text-sm text-center">
                            @if($field->is_active)
                            <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">On</span>
                            @else
                            <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">Off</span>
                            @endif
                        </td>
                        <td class="py-4 pl-3 pr-4 text-right text-sm sm:pr-6 space-x-2">
                            <button @click="editing = !editing" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                            <form method="POST" action="{{ route('admin.fields.sale-fields.destroy', $field) }}" class="inline"
                                  onsubmit="return confirm('Delete this field?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>

                    {{-- Edit row — same tbody scope, so editing/type/formula are shared --}}
                    <tr x-show="editing" x-cloak class="bg-indigo-50">
                        <td colspan="8" class="px-6 py-4">
                            <form method="POST" action="{{ route('admin.fields.sale-fields.update', $field) }}" class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                                @csrf @method('PUT')

                                {{-- formula hidden input — x-effect keeps it in sync --}}
                                <input type="hidden" name="formula" x-effect="$el.value = formula">

                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Label</label>
                                    <input type="text" name="label" value="{{ $field->label }}" required
                                           class="mt-1 block w-full rounded border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Type</label>
                                    <select name="field_type" x-model="type" class="mt-1 block w-full rounded border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                                        @foreach(['text','number','date','select','textarea','checkbox','calculated'] as $t)
                                        <option value="{{ $t }}">{{ $t }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Sale Type</label>
                                    <select name="sale_type_id" class="mt-1 block w-full rounded border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                                        <option value="">All types</option>
                                        @foreach($saleTypes as $st)
                                        <option value="{{ $st->id }}" {{ $field->sale_type_id == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Sort Order</label>
                                    <input type="number" name="sort_order" value="{{ $field->sort_order }}" min="0"
                                           class="mt-1 block w-full rounded border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                                </div>

                                <div class="sm:col-span-4" x-show="type === 'select'">
                                    <label class="block text-xs font-medium text-gray-700">Options (one per line)</label>
                                    <textarea name="options" rows="3"
                                              class="mt-1 block w-full rounded border-gray-300 text-sm shadow-sm focus:ring-indigo-500">{{ $field->options ? implode("\n", $field->options) : '' }}</textarea>
                                </div>

                                <div class="sm:col-span-4" x-show="type === 'calculated'">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Formula</label>
                                    <code class="block w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm font-mono text-purple-700 min-h-[2.25rem] break-all mb-2"
                                          x-text="formula || '—'"></code>
                                    <button type="button" @click="openBuilder()"
                                            class="inline-flex items-center gap-1.5 rounded-md border-2 border-purple-600 bg-purple-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-purple-700">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75l-2.489-2.489m0 0a3.375 3.375 0 10-4.773-4.773 3.375 3.375 0 004.773 4.773zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Build Formula
                                    </button>
                                </div>

                                <div class="sm:col-span-4 flex flex-wrap items-center gap-6">
                                    <label class="flex items-center gap-2 text-sm text-gray-700">
                                        <input type="checkbox" name="is_required" value="1" {{ $field->is_required ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">Required
                                    </label>
                                    <label class="flex items-center gap-2 text-sm text-gray-700">
                                        <input type="checkbox" name="show_on_create" value="1" {{ $field->show_on_create ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">Show on create
                                    </label>
                                    <label class="flex items-center gap-2 text-sm text-gray-700">
                                        <input type="checkbox" name="show_in_table" value="1" {{ $field->show_in_table ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">Show in table
                                    </label>
                                    <label class="flex items-center gap-2 text-sm text-gray-700">
                                        <input type="checkbox" name="is_active" value="1" {{ $field->is_active ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">Active
                                    </label>
                                    <button type="submit" class="ml-auto rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">Save</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                </tbody>
                @empty
                <tbody><tr><td colspan="8" class="py-10 text-center text-sm text-gray-500">No custom fields defined yet.</td></tr></tbody>
                @endforelse
            </table>
        </div>

        <div class="mt-3 flex flex-wrap gap-6 text-xs text-gray-500">
            <span><span class="font-medium text-blue-600">On Create</span> — appears on the Add Sale form</span>
            <span><span class="font-medium text-indigo-600">In Table</span> — toggleable column in sales list</span>
            <span><span class="font-medium text-green-600">Active</span> — appears on the Edit Sale form</span>
            <span><span class="font-medium text-purple-600">Calculated</span> — auto-computed from a formula on save</span>
        </div>
    </div>

    {{-- Add new field sidebar --}}
    <div x-data="{
        type: 'text',
        formula: {{ json_encode(old('formula', '')) }},
        openBuilder() {
            $store.fb.open(this.formula, v => { this.formula = v; });
        }
    }">
        <div class="rounded-lg bg-white shadow">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Add New Field</h3>
            </div>
            <form method="POST" action="{{ route('admin.fields.sale-fields.store') }}" class="px-6 py-5 space-y-4">
                @csrf
                <input type="hidden" name="formula" x-effect="$el.value = formula">

                <div>
                    <label class="block text-sm font-medium text-gray-900">Key <span class="text-red-500">*</span></label>
                    <input type="text" name="key" value="{{ old('key') }}" required placeholder="e.g. commission_rate"
                           class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-400">Lowercase, numbers, underscores only.</p>
                    @error('key')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">Label <span class="text-red-500">*</span></label>
                    <input type="text" name="label" value="{{ old('label') }}" required placeholder="e.g. Commission Rate"
                           class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">Field Type</label>
                    <select name="field_type" x-model="type" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                        @foreach(['text','number','date','select','textarea','checkbox','calculated'] as $t)
                        <option value="{{ $t }}" {{ old('field_type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="type === 'select'">
                    <label class="block text-sm font-medium text-gray-900">Options <span class="text-gray-400 font-normal">(one per line)</span></label>
                    <textarea name="options" rows="3" placeholder="Option A&#10;Option B"
                              class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500">{{ old('options') }}</textarea>
                </div>

                <div x-show="type === 'calculated'">
                    <label class="block text-sm font-medium text-gray-900 mb-1">Formula <span class="text-red-500">*</span></label>
                    <code class="block w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm font-mono text-purple-700 min-h-[2.5rem] break-all mb-2"
                          x-text="formula || '—'"></code>
                    <button type="button" @click="openBuilder()"
                            class="inline-flex w-full items-center justify-center gap-1.5 rounded-md border-2 border-purple-600 bg-purple-600 px-4 py-2 text-sm font-semibold text-white hover:bg-purple-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75l-2.489-2.489m0 0a3.375 3.375 0 10-4.773-4.773 3.375 3.375 0 004.773 4.773zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Open Formula Builder
                    </button>
                    @error('formula')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-900">Applies to Sale Type</label>
                    <select name="sale_type_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                        <option value="">All sale types</option>
                        @foreach($saleTypes as $st)
                        <option value="{{ $st->id }}" {{ old('sale_type_id') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                           class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                </div>

                <div class="space-y-2 pt-1 border-t border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide pt-2">Visibility</p>
                    <label class="flex items-center gap-2 text-sm text-gray-700" x-show="type !== 'calculated'">
                        <input type="checkbox" name="is_required" value="1" {{ old('is_required') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">Required field
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="show_on_create" value="1" {{ old('show_on_create') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">
                        Show on <strong>Add Sale</strong> form
                        <span x-show="type === 'calculated'" class="text-xs text-purple-600">(preview)</span>
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="show_in_table" value="1" {{ old('show_in_table') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">Show in sales table
                    </label>
                    <p class="text-xs text-gray-400">Active fields appear on the Edit Sale form.</p>
                </div>
                <button type="submit"
                        class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Create Field
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ── Formula Builder Modal ─────────────────────────────────── --}}
<div x-data x-show="$store.fb.isOpen" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="fixed inset-0 bg-gray-700/60" @click="$store.fb.isOpen = false"></div>

    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden flex flex-col">
        {{-- Header --}}
        <div class="bg-gray-900 px-5 py-4 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-white">Formula Builder</h3>
            <button @click="$store.fb.isOpen = false" class="text-gray-400 hover:text-white text-xl leading-none">&times;</button>
        </div>

        {{-- Live formula display --}}
        <div class="bg-gray-950 px-5 py-3 min-h-[3.5rem] flex items-center">
            <span x-text="$store.fb.formula || '—'"
                  class="font-mono text-sm break-all"
                  :class="$store.fb.formula ? 'text-purple-300' : 'text-gray-600'"></span>
        </div>

        <div class="p-5 space-y-4 overflow-y-auto max-h-[60vh]">

            {{-- Built-in variables --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Built-in Variables</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($builtinKeys as $key)
                    <button type="button" @click="$store.fb.insert('{{ $key }}')"
                            class="rounded-md bg-blue-50 px-2.5 py-1 text-xs font-mono font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20 hover:bg-blue-100">
                        {{ $key }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Custom field variables --}}
            @if($fieldKeys->isNotEmpty())
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Custom Field Variables</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($fieldKeys as $key)
                    <button type="button" @click="$store.fb.insert('{{ $key }}')"
                            class="rounded-md bg-purple-50 px-2.5 py-1 text-xs font-mono font-medium text-purple-700 ring-1 ring-inset ring-purple-600/20 hover:bg-purple-100">
                        {{ $key }}
                    </button>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Operators --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Operators</p>
                <div class="flex flex-wrap gap-2">
                    @foreach(['+', '-', '*', '/', '**', '%', '(', ')', '?', ':'] as $op)
                    <button type="button" @click="$store.fb.insert('{{ $op }}')"
                            class="rounded-md bg-gray-100 px-3 py-1.5 text-sm font-mono font-bold text-gray-800 hover:bg-gray-200 min-w-[2.5rem] text-center">
                        {{ $op }}
                    </button>
                    @endforeach
                    <button type="button" @click="$store.fb.insert(' ')"
                            class="rounded-md bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-200">
                        Space
                    </button>
                </div>
                <p class="mt-1.5 text-xs text-gray-400"><span class="font-mono">**</span> = power &nbsp;·&nbsp; <span class="font-mono">%</span> = modulo &nbsp;·&nbsp; <span class="font-mono">? :</span> = ternary</p>
            </div>

            {{-- Number --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Insert Number</p>
                <div class="flex gap-2">
                    <input type="number" step="any" x-model="$store.fb.numInput" placeholder="e.g. 100"
                           @keydown.enter.prevent="$store.fb.insertNum()"
                           class="flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:ring-purple-500">
                    <button type="button" @click="$store.fb.insertNum()"
                            class="rounded-md bg-gray-800 px-3 py-1.5 text-xs font-semibold text-white hover:bg-gray-700">
                        Insert
                    </button>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-2 pt-1 border-t border-gray-100">
                <button type="button" @click="$store.fb.backspace()"
                        class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    ⌫ Backspace
                </button>
                <button type="button" @click="$store.fb.clear()"
                        class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-red-600 shadow-sm ring-1 ring-inset ring-red-200 hover:bg-red-50">
                    Clear
                </button>
                <button type="button" @click="$store.fb.done()"
                        class="ml-auto rounded-md bg-purple-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-500">
                    Done — Use Formula
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('fb', {
        isOpen:   false,
        formula:  '',
        numInput: '',
        _cb:      null,

        open(existing, callback) {
            this.formula  = existing || '';
            this.numInput = '';
            this._cb      = callback;
            this.isOpen   = true;
        },
        done() {
            if (this._cb) this._cb(this.formula);
            this.isOpen = false;
            this._cb    = null;
        },
        insert(token)  { this.formula += token; },
        insertNum()    { if (this.numInput !== '') { this.formula += this.numInput; this.numInput = ''; } },
        backspace()    { this.formula = this.formula.slice(0, -1); },
        clear()        { this.formula = ''; },
    });
});
</script>
@endpush
@endsection
