@extends('layouts.app')
@section('title', 'Payroll Adjustments')
@section('page-title', 'Payroll Adjustments')

@section('content')
<div x-data="adjManager()" class="space-y-6">

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('admin.payroll.adjustments.index') }}"
          class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Employee</label>
            <select name="employee_id"
                    class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">All employees</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" @selected(request('employee_id') == $emp->id)>
                        {{ $emp->lastname }}, {{ $emp->firstname }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Type</label>
            <select name="type"
                    class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">All types</option>
                <option value="addition"  @selected(request('type') === 'addition')>Addition</option>
                <option value="deduction" @selected(request('type') === 'deduction')>Deduction</option>
            </select>
        </div>
        <button type="submit"
                class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            Filter
        </button>
        <a href="{{ route('admin.payroll.adjustments.index') }}"
           class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-600 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            Clear
        </a>
        <div class="ml-auto">
            <button type="button" @click="openCreate()"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                + New Adjustment
            </button>
        </div>
    </form>

    {{-- Table --}}
    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Employee</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Type</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Category</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Description</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Amount</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Recurring</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Dates</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                    <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($adjustments as $adj)
                <tr class="hover:bg-gray-50">
                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                        @if($adj->employee)
                            {{ $adj->employee->lastname }}, {{ $adj->employee->firstname }}
                        @else
                            <span class="italic text-gray-400">All employees</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                        @if($adj->type === 'addition')
                            <span class="inline-flex rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                Addition
                            </span>
                        @else
                            <span class="inline-flex rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">
                                Deduction
                            </span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                        {{ ucfirst(str_replace('_', ' ', $adj->category)) }}
                    </td>
                    <td class="px-3 py-4 text-sm text-gray-700 max-w-xs truncate">
                        {{ $adj->description }}
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 font-mono">
                        @if($adj->amount_type === 'percentage')
                            {{ number_format($adj->amount, 2) }}%
                        @else
                            {{ number_format($adj->amount, 2) }}
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                        {{ $adj->is_recurring ? 'Yes' : 'One-time' }}
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-xs text-gray-500">
                        @if($adj->effective_date || $adj->expires_date)
                            {{ $adj->effective_date?->format('M j, Y') ?? '—' }}
                            →
                            {{ $adj->expires_date?->format('M j, Y') ?? '∞' }}
                        @else
                            <span class="text-gray-400">Always</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                        @if($adj->is_active)
                            <span class="inline-flex rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Active</span>
                        @else
                            <span class="inline-flex rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">Inactive</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 space-x-2">
                        <button type="button" @click="openEdit({{ $adj }})"
                                class="text-indigo-600 hover:text-indigo-900">Edit</button>
                        <form method="POST" action="{{ route('admin.payroll.adjustments.destroy', $adj) }}"
                              class="inline" onsubmit="return confirm('Delete this adjustment?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="py-8 text-center text-sm text-gray-400">No adjustments found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $adjustments->links() }}

    {{-- Create / Edit Modal --}}
    <div x-show="modalOpen" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="modalOpen = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6 space-y-5">
                <h3 class="text-lg font-semibold text-gray-900" x-text="editId ? 'Edit Adjustment' : 'New Adjustment'"></h3>

                <form :action="editId
                        ? '{{ url('admin/payroll/adjustments') }}/' + editId
                        : '{{ route('admin.payroll.adjustments.store') }}'"
                      method="POST" class="space-y-4">
                    @csrf
                    <input x-show="editId" type="hidden" name="_method" value="PUT">

                    {{-- Employee --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Employee
                            <span class="text-gray-400 font-normal">(leave blank for all)</span>
                        </label>
                        <select name="employee_id"
                                class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">— All employees —</option>
                            @foreach($employees as $emp)
                                <option :value="'{{ $emp->id }}'"
                                        :selected="form.employee_id == '{{ $emp->id }}'">
                                    {{ $emp->lastname }}, {{ $emp->firstname }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Type & Category --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                            <select name="type" x-model="form.type" required
                                    class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="addition">Addition</option>
                                <option value="deduction">Deduction</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select name="category" x-model="form.category" required
                                    class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">{{ ucfirst(str_replace('_', ' ', $cat)) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <input type="text" name="description" x-model="form.description" required maxlength="150"
                               class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="e.g. Housing allowance">
                    </div>

                    {{-- Amount --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Amount type</label>
                            <select name="amount_type" x-model="form.amount_type" required
                                    class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="fixed">Fixed</option>
                                <option value="percentage">Percentage (%)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Amount <span x-text="form.amount_type === 'percentage' ? '(%)' : ''"></span>
                            </label>
                            <input type="number" name="amount" x-model="form.amount" required min="0.01" step="0.01"
                                   class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    {{-- Recurring --}}
                    <div class="flex items-center gap-3">
                        <input type="hidden" name="is_recurring" value="0">
                        <input type="checkbox" name="is_recurring" value="1" id="is_recurring"
                               :checked="form.is_recurring"
                               @change="form.is_recurring = $event.target.checked"
                               class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                        <label for="is_recurring" class="text-sm text-gray-700">
                            Recurring (applies every pay period)
                        </label>
                    </div>

                    {{-- Active (edit only) --}}
                    <div x-show="editId" class="flex items-center gap-3">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" id="is_active"
                               :checked="form.is_active"
                               @change="form.is_active = $event.target.checked"
                               class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                        <label for="is_active" class="text-sm text-gray-700">Active</label>
                    </div>

                    {{-- Dates --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Effective date</label>
                            <input type="date" name="effective_date" x-model="form.effective_date"
                                   class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expires date</label>
                            <input type="date" name="expires_date" x-model="form.expires_date"
                                   class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="modalOpen = false"
                                class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function adjManager() {
    return {
        modalOpen: false,
        editId: null,
        form: {
            employee_id: '',
            type: 'addition',
            category: 'allowance',
            description: '',
            amount_type: 'fixed',
            amount: '',
            is_recurring: true,
            is_active: true,
            effective_date: '',
            expires_date: '',
        },
        openCreate() {
            this.editId = null;
            this.form = {
                employee_id: '', type: 'addition', category: 'allowance',
                description: '', amount_type: 'fixed', amount: '',
                is_recurring: true, is_active: true,
                effective_date: '', expires_date: '',
            };
            this.modalOpen = true;
        },
        openEdit(adj) {
            this.editId = adj.id;
            this.form = {
                employee_id:    adj.employee_id ?? '',
                type:           adj.type,
                category:       adj.category,
                description:    adj.description,
                amount_type:    adj.amount_type,
                amount:         adj.amount,
                is_recurring:   adj.is_recurring,
                is_active:      adj.is_active,
                effective_date: adj.effective_date ?? '',
                expires_date:   adj.expires_date ?? '',
            };
            this.modalOpen = true;
        },
    };
}
</script>
@endpush
@endsection
