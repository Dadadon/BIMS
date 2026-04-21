@extends('layouts.app')
@section('title', 'Employee Custom Fields')
@section('page-title', 'Employee Custom Fields')

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
                        <th class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">Req.</th>
                        <th class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900 whitespace-nowrap">On Create</th>
                        <th class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">Active</th>
                        <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($fields as $field)
                    <tr x-data="{ editing: false }" class="hover:bg-gray-50">
                        <td class="py-4 pl-4 pr-3 text-sm sm:pl-6">
                            <p class="font-medium text-gray-900">{{ $field->label }}</p>
                            <p class="text-xs text-gray-400 font-mono">{{ $field->key }}</p>
                        </td>
                        <td class="px-3 py-4 text-sm text-gray-500">{{ $field->field_type }}</td>
                        <td class="px-3 py-4 text-sm text-center">{{ $field->is_required ? '✓' : '—' }}</td>
                        <td class="px-3 py-4 text-sm text-center">
                            @if($field->show_on_create)
                            <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20">Yes</span>
                            @else
                            <span class="text-gray-300">—</span>
                            @endif
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
                            <form method="POST" action="{{ route('admin.fields.employee-fields.destroy', $field) }}" class="inline"
                                  onsubmit="return confirm('Delete this field? Existing metadata values will remain.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                    {{-- Inline edit row --}}
                    <tr x-show="editing" x-data="{ type: '{{ $field->field_type }}' }" class="bg-indigo-50">
                        <td colspan="6" class="px-6 py-4">
                            <form method="POST" action="{{ route('admin.fields.employee-fields.update', $field) }}" class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                                @csrf @method('PUT')
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Label</label>
                                    <input type="text" name="label" value="{{ $field->label }}" required
                                           class="mt-1 block w-full rounded border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Type</label>
                                    <select name="field_type" x-model="type" class="mt-1 block w-full rounded border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                                        @foreach(['text','number','date','select','textarea','checkbox'] as $t)
                                        <option value="{{ $t }}" {{ $field->field_type === $t ? 'selected' : '' }}>{{ $t }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Sort Order</label>
                                    <input type="number" name="sort_order" value="{{ $field->sort_order }}" min="0"
                                           class="mt-1 block w-full rounded border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                                </div>
                                <div x-show="type === 'select'" class="sm:col-span-4">
                                    <label class="block text-xs font-medium text-gray-700">Options (one per line)</label>
                                    <textarea name="options" rows="3"
                                              class="mt-1 block w-full rounded border-gray-300 text-sm shadow-sm focus:ring-indigo-500">{{ $field->options ? implode("\n", $field->options) : '' }}</textarea>
                                </div>
                                <div class="sm:col-span-4 flex flex-wrap items-center gap-6">
                                    <label class="flex items-center gap-2 text-sm text-gray-700">
                                        <input type="checkbox" name="is_required" value="1" {{ $field->is_required ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">
                                        Required
                                    </label>
                                    <label class="flex items-center gap-2 text-sm text-gray-700">
                                        <input type="checkbox" name="show_on_create" value="1" {{ $field->show_on_create ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">
                                        Show on create form
                                    </label>
                                    <label class="flex items-center gap-2 text-sm text-gray-700">
                                        <input type="checkbox" name="is_active" value="1" {{ $field->is_active ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">
                                        Active
                                    </label>
                                    <button type="submit" class="ml-auto rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">Save</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-10 text-center text-sm text-gray-500">No custom fields defined yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3 flex gap-6 text-xs text-gray-500">
            <span><span class="font-medium text-blue-600">On Create</span> — shown when adding a new employee</span>
            <span><span class="font-medium text-green-600">Active</span> — shown on the edit employee form</span>
        </div>
    </div>

    {{-- Add new field --}}
    <div x-data="{ type: 'text' }">
        <div class="rounded-lg bg-white shadow">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Add New Field</h3>
            </div>
            <form method="POST" action="{{ route('admin.fields.employee-fields.store') }}" class="px-6 py-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-900">Key <span class="text-red-500">*</span></label>
                    <input type="text" name="key" value="{{ old('key') }}" required placeholder="e.g. sss_number"
                           class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-400">Lowercase, numbers, underscores only. Cannot be changed later.</p>
                    @error('key')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">Label <span class="text-red-500">*</span></label>
                    <input type="text" name="label" value="{{ old('label') }}" required placeholder="e.g. SSS Number"
                           class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">Field Type</label>
                    <select name="field_type" x-model="type" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                        @foreach(['text','number','date','select','textarea','checkbox'] as $t)
                        <option value="{{ $t }}" {{ old('field_type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-show="type === 'select'">
                    <label class="block text-sm font-medium text-gray-900">Options <span class="text-gray-400 font-normal">(one per line)</span></label>
                    <textarea name="options" rows="3" placeholder="Option A&#10;Option B"
                              class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500">{{ old('options') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                           class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:ring-indigo-500">
                </div>
                <div class="space-y-2 pt-1 border-t border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide pt-2">Visibility</p>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_required" value="1" {{ old('is_required') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">
                        Required field
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="show_on_create" value="1" checked class="rounded border-gray-300 text-indigo-600">
                        Show on <strong>Add Employee</strong> form
                    </label>
                    <p class="text-xs text-gray-400">All active fields always appear on the <strong>Edit Employee</strong> form.</p>
                </div>
                <button type="submit"
                        class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Create Field
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
