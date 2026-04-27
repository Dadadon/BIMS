@extends('layouts.app')
@section('title', 'Payroll Run — ' . $run->payPeriod->label)
@section('page-title', 'Payroll Run')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <a href="{{ route('admin.payroll.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Payroll</a>
    <div class="flex gap-3">
        <a href="{{ route('admin.payroll.export', $run) }}"
           class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            Export CSV
        </a>
        @if($run->status === 'draft')
        <form method="POST" action="{{ route('admin.payroll.run.destroy', $run) }}"
              onsubmit="return confirm('Delete this draft run? All computed slips will be removed and the period will reopen.')">
            @csrf @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-red-600 shadow-sm ring-1 ring-inset ring-red-300 hover:bg-red-50">
                Delete Draft
            </button>
        </form>
        <form method="POST" action="{{ route('admin.payroll.finalize', $run) }}"
              onsubmit="return confirm('Finalize this payroll run? This cannot be undone.')">
            @csrf
            <button type="submit"
                    class="inline-flex items-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500">
                Finalize Payroll
            </button>
        </form>
        @endif
    </div>
</div>

{{-- Summary --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-5 mb-6">
    @php
        $cards = [
            ['label' => 'Period',                  'value' => $run->payPeriod->label,                                        'color' => 'text-gray-900'],
            ['label' => 'Total Gross',             'value' => number_format($run->total_gross, 2),                           'color' => 'text-gray-900'],
            ['label' => 'Total Tax & Deductions',  'value' => number_format($run->total_deductions, 2),                      'color' => 'text-gray-900'],
            ['label' => 'Employer Contributions',  'value' => number_format($run->total_employer_contributions, 2),          'color' => 'text-amber-700'],
            ['label' => 'Total Net Pay',           'value' => number_format($run->total_net, 2),                             'color' => 'text-indigo-700'],
        ];
    @endphp
    @foreach($cards as $card)
    <div class="rounded-lg bg-white shadow px-5 py-4">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $card['label'] }}</p>
        <p class="mt-1 text-lg font-semibold {{ $card['color'] }}">{{ $card['value'] }}</p>
    </div>
    @endforeach
</div>

{{-- Status badge --}}
<div class="mb-4 flex items-center gap-3">
    <h2 class="text-lg font-semibold text-gray-900">{{ $run->slips->count() }} Employees</h2>
    @php
        $sc = $run->status === 'finalized' ? 'bg-green-50 text-green-700 ring-green-600/20' : 'bg-yellow-50 text-yellow-800 ring-yellow-600/20';
    @endphp
    <span class="inline-flex rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $sc }}">
        {{ ucfirst($run->status) }}
    </span>
</div>

{{-- Slips table --}}
<div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
            <tr>
                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Employee</th>
                <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Reg Hrs</th>
                <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">OT Hrs</th>
                <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Gross</th>
                <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Commission</th>
                <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Tax</th>
                <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900 font-bold">Net Pay</th>
                <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">View</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @foreach($run->slips as $slip)
            <tr class="hover:bg-gray-50">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                    <p class="font-medium text-gray-900">{{ $slip->employee->display_name }}</p>
                    <p class="text-xs text-gray-500">
                        {{ $slip->employee->employee_code }}
                        @if($slip->employee->employment_type === 'Contract')
                        <span class="ml-1 inline-flex rounded bg-amber-50 px-1.5 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20">Contractor</span>
                        @endif
                    </p>
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700 text-right">{{ number_format($slip->regular_hours, 2) }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700 text-right">{{ number_format($slip->overtime_hours, 2) }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700 text-right">{{ number_format($slip->gross_salary, 2) }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700 text-right">{{ number_format($slip->commission_earned, 2) }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700 text-right">{{ number_format($slip->total_tax, 2) }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-gray-900 text-right">{{ number_format($slip->net_pay, 2) }}</td>
                <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm sm:pr-6 space-x-2">
                    <a href="{{ route('admin.payroll.slip.show', $slip) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                    <a href="{{ route('admin.payroll.slip.download', $slip) }}" class="text-gray-600 hover:text-gray-900">PDF</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
