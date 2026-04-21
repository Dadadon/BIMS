<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payroll\PayPeriod;
use App\Models\Payroll\PayrollRun;
use App\Models\Payroll\PayrollSlip;
use App\Models\Payroll\TaxConfiguration;
use App\Models\User;
use App\Notifications\PayrollFinalized;
use App\Services\Payroll\PayrollService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use League\Csv\Writer;
use SplTempFileObject;

class PayrollController extends Controller
{
    public function __construct(private PayrollService $payroll) {}

    public function index(): View
    {
        $periods  = PayPeriod::withCount('payrollRuns')
            ->with(['payrollRuns' => fn($q) => $q->where('status', 'draft')->latest()->limit(1)])
            ->orderByDesc('start_date')->paginate(10);
        $taxes    = TaxConfiguration::active()->orderBy('name')->get();
        return view('admin.payroll.index', compact('periods', 'taxes'));
    }

    public function storePeriod(Request $request): RedirectResponse
    {
        $request->validate([
            'label'      => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after:start_date'],
        ]);

        PayPeriod::create([
            'label'      => $request->label,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'status'     => 'open',
        ]);

        return redirect()->route('admin.payroll.index')->with('success', 'Pay period created.');
    }

    public function run(Request $request, PayPeriod $period): RedirectResponse
    {
        try {
            $run = $this->payroll->runPayroll($period, auth()->id());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.payroll.run.show', $run)
            ->with('success', 'Payroll computed successfully.');
    }

    public function finalize(PayrollRun $run): RedirectResponse
    {
        try {
            $this->payroll->finalizeRun($run);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $run->load(['slips.employee', 'payPeriod']);
        $employeeIds = $run->slips->pluck('employee_id')->filter()->unique();
        $users       = User::whereIn('employee_id', $employeeIds)->get()->keyBy('employee_id');

        foreach ($run->slips as $slip) {
            $slip->setRelation('payrollRun', $run);
            $user = $users->get($slip->employee_id);
            $user?->notify(new PayrollFinalized($slip));
        }

        return back()->with('success', 'Payroll finalized and period closed.');
    }

    public function showRun(PayrollRun $run): View
    {
        $run->load(['payPeriod', 'slips.employee']);
        return view('admin.payroll.run', compact('run'));
    }

    public function showSlip(PayrollSlip $slip): View
    {
        $slip->load(['employee', 'payrollRun.payPeriod', 'lineItems']);
        return view('admin.payroll.slip', compact('slip'));
    }

    public function downloadSlip(PayrollSlip $slip)
    {
        $slip->load(['employee.company', 'payrollRun.payPeriod', 'lineItems']);
        $filename = 'payslip-' . $slip->employee->employee_code . '-' . $slip->payrollRun->payPeriod->label . '.pdf';
        return Pdf::loadView('admin.payroll.slip-pdf', compact('slip'))
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }

    public function export(PayrollRun $run)
    {
        $run->load(['slips.employee', 'payPeriod']);
        $csv = Writer::createFromFileObject(new SplTempFileObject());

        $csv->insertOne(['Employee Code', 'Name', 'Regular Hrs', 'OT Hrs', 'Base Rate', 'Gross', 'Commission', 'Additions', 'Deductions', 'Tax', 'Net Pay']);

        foreach ($run->slips as $slip) {
            $csv->insertOne([
                $slip->employee->employee_code,
                $slip->employee->display_name,
                $slip->regular_hours,
                $slip->overtime_hours,
                $slip->base_rate,
                $slip->gross_salary,
                $slip->commission_earned,
                $slip->total_additions,
                $slip->total_deductions,
                $slip->total_tax,
                $slip->net_pay,
            ]);
        }

        $filename = 'payroll-export-' . $run->payPeriod->label . '.csv';
        return response()->streamDownload(
            fn() => print($csv->toString()),
            $filename,
            ['Content-Type' => 'text/csv']
        );
    }

    public function destroyRun(PayrollRun $run): RedirectResponse
    {
        if ($run->status !== 'draft') {
            return back()->with('error', 'Only draft runs can be deleted.');
        }

        DB::transaction(function () use ($run) {
            $run->slips()->each(fn($slip) => $slip->delete());
            $run->payPeriod->update(['status' => 'open']);
            $run->delete();
        });

        return redirect()->route('admin.payroll.index')
            ->with('success', 'Draft run deleted. The period is open again.');
    }

    public function storeTax(Request $request): RedirectResponse
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'type'        => ['required', 'in:percentage,fixed_bracket'],
            'rate'        => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        TaxConfiguration::create($request->only('name', 'type', 'rate', 'description') + ['is_active' => true]);

        return redirect()->route('admin.payroll.index')->with('success', 'Tax configuration added.');
    }

    public function destroyTax(TaxConfiguration $tax): RedirectResponse
    {
        $tax->delete();
        return redirect()->route('admin.payroll.index')->with('success', 'Tax configuration removed.');
    }
}
