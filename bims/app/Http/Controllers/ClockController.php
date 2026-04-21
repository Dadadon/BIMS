<?php

namespace App\Http\Controllers;

use App\Models\Attendance\AttendanceLog;
use App\Models\Sales\Sale;
use App\Models\Sales\SaleType;
use App\Services\Attendance\AttendanceService;
use App\Services\Sales\CommissionCalculator;
use App\Events\SaleRecorded;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClockController extends Controller
{
    public function __construct(
        private AttendanceService   $attendance,
        private CommissionCalculator $commission,
    ) {}

    public function show(): View
    {
        $employee = auth()->user()->employee;
        $openLog  = null;
        $todayLogs = collect();

        if ($employee) {
            $openLog   = AttendanceLog::where('employee_id', $employee->id)
                            ->whereNull('clock_out')
                            ->latest('clock_in')
                            ->first();

            $todayLogs = AttendanceLog::where('employee_id', $employee->id)
                            ->whereDate('log_date', Carbon::today())
                            ->orderBy('clock_in')
                            ->get();
        }

        $saleTypes = SaleType::where('is_active', true)->orderBy('product_category')->orderBy('portal')->get();

        return view('clock.show', compact('employee', 'openLog', 'todayLogs', 'saleTypes'));
    }

    public function clockIn(Request $request): RedirectResponse
    {
        $employee = auth()->user()->employee;
        if (! $employee) {
            return back()->with('error', 'No employee record linked to your account.');
        }

        $request->validate([
            'reason'  => ['required', 'in:Shift,Lunch,Break'],
            'comment' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->attendance->clockIn(
                $employee,
                $request->input('reason', 'Shift'),
                $request->input('comment'),
                auth()->user()->name,
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Clocked in successfully.');
    }

    public function clockOut(Request $request): RedirectResponse
    {
        $employee = auth()->user()->employee;
        if (! $employee) {
            return back()->with('error', 'No employee record linked to your account.');
        }

        $request->validate([
            'reason' => ['required', 'in:Shift,Lunch,Break'],
        ]);

        try {
            $this->attendance->clockOut(
                $employee,
                $request->input('reason', 'Shift'),
                auth()->user()->name,
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return back()->with('error', 'No open clock-in found to clock out of.');
        }

        return back()->with('success', 'Clocked out successfully.');
    }

    public function logSale(Request $request): RedirectResponse
    {
        $employee = auth()->user()->employee;
        if (! $employee) {
            return back()->with('error', 'No employee record linked to your account.');
        }

        $validated = $request->validate([
            'sale_type_id'  => ['required', 'exists:sale_types,id'],
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_phone'=> ['nullable', 'string', 'max:30'],
            'sale_date'     => ['required', 'date'],
        ]);

        $saleType  = SaleType::findOrFail($validated['sale_type_id']);
        $points    = $this->commission->calculate($employee->id, $saleType->id);

        $sale = Sale::create([
            'employee_id'           => $employee->id,
            'sale_type_id'          => $saleType->id,
            'customer_name'         => $validated['customer_name'],
            'customer_phone'        => $validated['customer_phone'] ?? null,
            'sale_date'             => $validated['sale_date'],
            'total_points'          => $points['total_points'],
            'agent_points'          => $points['agent_points'],
            'status'                => 'Submitted',
            'compensation_received' => false,
        ]);

        event(new SaleRecorded($sale));

        return back()->with('success', 'Sale logged successfully.');
    }
}
