<?php

namespace App\Http\Controllers\Personal;

use App\Http\Controllers\Controller;
use App\Models\Payroll\PayrollSlip;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\View\View;

class PersonalPayrollController extends Controller
{
    public function index(): View
    {
        $employee = auth()->user()->employee;
        $slips    = collect();

        if ($employee) {
            $slips = PayrollSlip::where('employee_id', $employee->id)
                ->with('payrollRun.payPeriod')
                ->orderByDesc('created_at')
                ->paginate(12);
        }

        return view('personal.payroll', compact('employee', 'slips'));
    }

    public function download(PayrollSlip $slip)
    {
        $employee = auth()->user()->employee;

        if ($slip->employee_id !== $employee?->id) {
            abort(403);
        }

        $slip->load(['employee.company', 'payrollRun.payPeriod', 'lineItems']);
        $settings = Setting::current();
        $filename = 'payslip-' . $slip->payrollRun->payPeriod->label . '.pdf';

        return Pdf::loadView('admin.payroll.slip-pdf', compact('slip', 'settings'))
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }
}
