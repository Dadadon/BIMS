<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\Payroll\PayrollAdjustment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayrollAdjustmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = PayrollAdjustment::with('employee')->latest();

        if ($empId = $request->input('employee_id')) {
            $query->where(fn($q) => $q->where('employee_id', $empId)->orWhereNull('employee_id'));
        }
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        return view('admin.payroll.adjustments', [
            'adjustments' => $query->paginate(30)->withQueryString(),
            'employees'   => Employee::active()->orderBy('lastname')->get(),
            'categories'  => PayrollAdjustment::categories(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'employee_id'    => ['nullable', 'exists:employees,id'],
            'type'           => ['required', 'in:addition,deduction'],
            'category'       => ['required', 'in:allowance,bonus,loan_repayment,cash_advance,absence,late,other'],
            'description'    => ['required', 'string', 'max:150'],
            'amount_type'    => ['required', 'in:fixed,percentage'],
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'is_recurring'   => ['boolean'],
            'effective_date' => ['nullable', 'date'],
            'expires_date'   => ['nullable', 'date', 'after_or_equal:effective_date'],
        ]);

        $data['is_recurring'] = $data['is_recurring'] ?? true;
        $data['is_active']    = true;

        PayrollAdjustment::create($data);

        return redirect()->route('admin.payroll.adjustments.index')
            ->with('success', 'Adjustment created.');
    }

    public function update(Request $request, PayrollAdjustment $adjustment): RedirectResponse
    {
        $data = $request->validate([
            'employee_id'    => ['nullable', 'exists:employees,id'],
            'type'           => ['required', 'in:addition,deduction'],
            'category'       => ['required', 'in:allowance,bonus,loan_repayment,cash_advance,absence,late,other'],
            'description'    => ['required', 'string', 'max:150'],
            'amount_type'    => ['required', 'in:fixed,percentage'],
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'is_recurring'   => ['boolean'],
            'is_active'      => ['boolean'],
            'effective_date' => ['nullable', 'date'],
            'expires_date'   => ['nullable', 'date', 'after_or_equal:effective_date'],
        ]);

        $data['is_recurring'] = $data['is_recurring'] ?? false;
        $data['is_active']    = $data['is_active'] ?? false;

        $adjustment->update($data);

        return redirect()->route('admin.payroll.adjustments.index')
            ->with('success', 'Adjustment updated.');
    }

    public function destroy(PayrollAdjustment $adjustment): RedirectResponse
    {
        $adjustment->delete();
        return back()->with('success', 'Adjustment deleted.');
    }
}
