@extends('layouts.app')
@section('title', 'Payroll')
@section('page-title', 'Payroll')

@section('content')
<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

    {{-- Pay Periods --}}
    <div class="lg:col-span-2 space-y-4">
        <div class="sm:flex sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold text-gray-900">Pay Periods</h2>
            <button type="button" onclick="document.getElementById('new-period-modal').classList.remove('hidden')"
                    class="mt-3 sm:mt-0 inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                + New Period
            </button>
        </div>

        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Period</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Dates</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Runs</th>
                        <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($periods as $period)
                    <tr class="hover:bg-gray-50">
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                            {{ $period->label }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($period->start_date)->format('M j') }}
                            – {{ \Carbon\Carbon::parse($period->end_date)->format('M j, Y') }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                            @php
                                $c = match($period->status) {
                                    'open'       => 'bg-green-50 text-green-700 ring-green-600/20',
                                    'processing' => 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
                                    'closed'     => 'bg-gray-50 text-gray-600 ring-gray-500/10',
                                    default      => 'bg-gray-50 text-gray-600 ring-gray-500/10',
                                };
                            @endphp
                            <span class="inline-flex rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $c }}">
                                {{ ucfirst($period->status) }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $period->payroll_runs_count }}</td>
                        <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                            @php $draftRun = $period->payrollRuns->first(); @endphp
                            @if($period->status === 'closed')
                                <span class="text-gray-400">Closed</span>
                            @elseif($draftRun)
                                <a href="{{ route('admin.payroll.run.show', $draftRun) }}"
                                   class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                    View Draft
                                </a>
                            @else
                            <form method="POST" action="{{ route('admin.payroll.run', $period) }}"
                                  onsubmit="return confirm('Run payroll for {{ $period->label }}?')">
                                @csrf
                                <button type="submit" class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                    Run Payroll
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-10 text-center text-sm text-gray-500">No pay periods yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $periods->links() }}</div>
    </div>

    {{-- Tax Configuration --}}
    <div class="space-y-4">
        <h2 class="text-xl font-semibold text-gray-900">Tax Config</h2>
        <div class="bg-white shadow rounded-lg divide-y divide-gray-100">
            @forelse($taxes as $tax)
            <div class="px-4 py-3 flex justify-between items-center text-sm">
                <div>
                    <p class="font-medium text-gray-900">{{ $tax->name }}</p>
                    <p class="text-xs text-gray-500">
                        {{ ucfirst(str_replace('_', ' ', $tax->type)) }}
                        @if($tax->rate) · {{ $tax->rate }}% @endif
                    </p>
                </div>
                <form method="POST" action="{{ route('admin.payroll.tax.destroy', $tax) }}"
                      onsubmit="return confirm('Remove this tax config?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs">Remove</button>
                </form>
            </div>
            @empty
            <div class="px-4 py-6 text-center text-sm text-gray-500">No tax configs.</div>
            @endforelse
        </div>

        {{-- Add Tax form --}}
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-3 border-b border-gray-100">
                <h4 class="text-sm font-semibold text-gray-900">Add Tax</h4>
            </div>
            <form method="POST" action="{{ route('admin.payroll.tax.store') }}" class="px-4 py-4 space-y-3">
                @csrf
                <div>
                    <input type="text" name="name" placeholder="Name (e.g. SSS)" required
                           class="block w-full rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <select name="type" class="rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600">
                        <option value="percentage">Percentage</option>
                        <option value="fixed_bracket">Fixed Bracket</option>
                    </select>
                    <input type="number" name="rate" step="0.01" placeholder="Rate %" min="0"
                           class="rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600">
                </div>
                <div>
                    <input type="text" name="description" placeholder="Description (optional)"
                           class="block w-full rounded-md border-0 py-1.5 px-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600">
                </div>
                <button type="submit"
                        class="w-full rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Add Tax Config
                </button>
            </form>
        </div>
    </div>
</div>

{{-- New Period Modal --}}
<div id="new-period-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between">
            <h3 class="text-sm font-semibold text-gray-900">New Pay Period</h3>
            <button onclick="document.getElementById('new-period-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.payroll.periods.store') }}" class="px-6 py-5 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-900">Label <span class="text-red-500">*</span></label>
                <input type="text" name="label" required placeholder="e.g. April 2026 – 1st Half"
                       class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-900">Start Date <span class="text-red-500">*</span></label>
                    <input type="date" name="start_date" required id="pp-start"
                           class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900">End Date <span class="text-red-500">*</span></label>
                    <input type="date" name="end_date" required id="pp-end"
                           class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-900">Pay Date <span class="text-gray-400 font-normal">(optional — defaults to end date)</span></label>
                <input type="date" name="pay_date" id="pp-pay"
                       class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            </div>
            <div class="flex justify-end gap-3 pt-1">
                <button type="button" onclick="document.getElementById('new-period-modal').classList.add('hidden')"
                        class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Create Period
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
