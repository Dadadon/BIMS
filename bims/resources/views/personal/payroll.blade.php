@extends('layouts.app')
@section('title', 'My Payslips')
@section('page-title', 'My Payslips')

@section('content')
<h2 class="text-xl font-semibold text-gray-900 mb-6">Payslip History</h2>

<div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
            <tr>
                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Period</th>
                <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Gross</th>
                <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Tax</th>
                <th class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900 font-bold">Net Pay</th>
                <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Download</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($slips as $slip)
            <tr class="hover:bg-gray-50">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                    {{ $slip->payrollRun->payPeriod->label ?? '—' }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700 text-right">{{ number_format($slip->gross_salary + $slip->commission_earned, 2) }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700 text-right">{{ number_format($slip->total_tax, 2) }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm font-bold text-indigo-700 text-right">{{ number_format($slip->net_pay, 2) }}</td>
                <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm sm:pr-6">
                    <a href="{{ route('my.payroll.download', $slip) }}" class="text-indigo-600 hover:text-indigo-900">Download PDF</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="py-10 text-center text-sm text-gray-500">No payslips yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $slips->links() }}</div>
@endsection
