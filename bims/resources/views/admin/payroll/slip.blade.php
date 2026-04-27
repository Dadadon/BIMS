@extends('layouts.app')
@section('title', 'Payslip — ' . $slip->employee->display_name)
@section('page-title', 'Payslip')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <a href="{{ route('admin.payroll.run.show', $slip->payrollRun) }}" class="text-sm text-gray-500 hover:text-gray-700">
        ← Back to Run
    </a>
    <a href="{{ route('admin.payroll.slip.download', $slip) }}"
       class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
        Download PDF
    </a>
</div>

<div class="max-w-2xl mx-auto">
    <div class="bg-white shadow rounded-lg overflow-hidden">
        {{-- Header --}}
        <div class="bg-gray-900 px-6 py-5 text-white">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-lg font-bold">{{ $settings->company_name }}</h2>
                    <p class="text-gray-400 text-sm mt-1">Payslip &mdash; {{ $slip->payrollRun->payPeriod->label }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-400">
                        {{ \Carbon\Carbon::parse($slip->payrollRun->payPeriod->start_date)->format('M j') }}
                        – {{ \Carbon\Carbon::parse($slip->payrollRun->payPeriod->end_date)->format('M j, Y') }}
                    </p>
                    @if($slip->payrollRun->status === 'draft')
                    <span class="inline-flex mt-1 rounded bg-yellow-500/20 px-2 py-0.5 text-xs font-medium text-yellow-300">Draft</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Employee info --}}
        <div class="px-6 py-5 border-b border-gray-100 grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500">Employee</p>
                <p class="font-semibold text-gray-900">{{ $slip->employee->display_name }}</p>
            </div>
            <div>
                <p class="text-gray-500">Code</p>
                <p class="font-medium text-gray-900">{{ $slip->employee->employee_code }}</p>
            </div>
            <div>
                <p class="text-gray-500">Job Title</p>
                <p class="font-medium text-gray-900">{{ $slip->employee->jobTitle->title ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Department</p>
                <p class="font-medium text-gray-900">{{ $slip->employee->department->name ?? '—' }}</p>
            </div>
        </div>

        {{-- Earnings --}}
        <div class="px-6 py-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Earnings</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-700">Base / Gross Salary</span>
                    <span class="font-medium text-gray-900">{{ number_format($slip->gross_salary, 2) }}</span>
                </div>
                @if($slip->overtime_hours > 0)
                <div class="flex justify-between text-gray-500">
                    <span class="pl-4">Regular ({{ number_format($slip->regular_hours, 2) }}h)</span>
                    <span></span>
                </div>
                <div class="flex justify-between text-gray-500">
                    <span class="pl-4">Overtime ({{ number_format($slip->overtime_hours, 2) }}h)</span>
                    <span></span>
                </div>
                @endif
                @if($slip->commission_earned > 0)
                <div class="flex justify-between">
                    <span class="text-gray-700">Commission</span>
                    <span class="font-medium text-gray-900">{{ number_format($slip->commission_earned, 2) }}</span>
                </div>
                @endif
                @foreach($slip->lineItems->where('type', 'addition') as $item)
                <div class="flex justify-between">
                    <span class="text-gray-700">{{ $item->description }}</span>
                    <span class="font-medium text-green-700">{{ number_format($item->amount, 2) }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Deductions --}}
        @if($slip->lineItems->whereIn('type', ['deduction', 'tax'])->isNotEmpty())
        <div class="px-6 py-5 border-t border-gray-100">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Deductions & Taxes</h3>
            <div class="space-y-2 text-sm">
                @foreach($slip->lineItems->where('type', 'deduction') as $item)
                <div class="flex justify-between">
                    <span class="text-gray-700">{{ $item->description }}</span>
                    <span class="text-red-600">({{ number_format($item->amount, 2) }})</span>
                </div>
                @endforeach
                @foreach($slip->lineItems->where('type', 'tax') as $item)
                <div class="flex justify-between">
                    <span class="text-gray-700">{{ $item->description }}</span>
                    <span class="text-red-600">({{ number_format($item->amount, 2) }})</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Net Pay --}}
        <div class="px-6 py-5 bg-gray-50 border-t border-gray-200">
            <div class="flex justify-between text-base font-bold text-gray-900">
                <span>NET PAY</span>
                <span class="text-indigo-700 text-lg">{{ number_format($slip->net_pay, 2) }}</span>
            </div>
        </div>

        {{-- Year-to-Date --}}
        @php $ytd = $slip->ytd(); @endphp
        <div class="px-6 py-5 border-t border-gray-100">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
                Year-to-Date ({{ \Carbon\Carbon::parse($slip->payrollRun->payPeriod->end_date)->year }})
                @if($slip->payrollRun->status !== 'finalized')
                <span class="ml-1 font-normal text-yellow-600 normal-case">— excludes this draft period</span>
                @endif
            </h3>
            <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Gross Salary</span>
                    <span class="font-medium text-gray-900">{{ number_format($ytd->gross, 2) }}</span>
                </div>
                @if($ytd->commission > 0)
                <div class="flex justify-between">
                    <span class="text-gray-500">Commission</span>
                    <span class="font-medium text-gray-900">{{ number_format($ytd->commission, 2) }}</span>
                </div>
                @endif
                @if($ytd->additions > 0)
                <div class="flex justify-between">
                    <span class="text-gray-500">Additions</span>
                    <span class="font-medium text-green-700">{{ number_format($ytd->additions, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-500">Deductions</span>
                    <span class="font-medium text-red-600">({{ number_format($ytd->deductions, 2) }})</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Tax Withheld</span>
                    <span class="font-medium text-red-600">({{ number_format($ytd->tax, 2) }})</span>
                </div>
                <div class="flex justify-between border-t border-gray-200 pt-2 col-span-2">
                    <span class="font-semibold text-gray-900">Net Pay YTD</span>
                    <span class="font-bold text-indigo-700">{{ number_format($ytd->net, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 text-xs text-gray-400 text-center">
            {{ $settings->company_name }} &middot; Computer-generated payslip &middot; {{ now()->format('F j, Y') }}
        </div>
    </div>
</div>
@endsection
