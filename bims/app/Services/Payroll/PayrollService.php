<?php

namespace App\Services\Payroll;

use App\Events\PayrollFinalized;
use App\Models\Attendance\AttendanceLog;
use App\Models\HR\Employee;
use App\Models\Payroll\PayPeriod;
use App\Models\Payroll\PayrollLineItem;
use App\Models\Payroll\PayrollRun;
use App\Models\Payroll\PayrollSlip;
use App\Models\Payroll\PayrollAdjustment;
use App\Models\Payroll\TaxConfiguration;
use App\Models\Sales\Sale;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    /**
     * Create a draft PayrollRun for a pay period and compute all slips.
     *
     * @throws \RuntimeException if the period is already processed
     */
    public function runPayroll(PayPeriod $period, int $runByUserId): PayrollRun
    {
        if ($period->status === 'closed') {
            throw new \RuntimeException("Pay period '{$period->label}' is already closed.");
        }

        if (PayrollRun::where('pay_period_id', $period->id)->where('status', 'draft')->exists()) {
            throw new \RuntimeException("A draft run already exists for '{$period->label}'. Delete the draft before re-running.");
        }

        return DB::transaction(function () use ($period, $runByUserId) {
            $period->update(['status' => 'processing']);

            $run = PayrollRun::create([
                'pay_period_id' => $period->id,
                'run_by'        => $runByUserId,
                'status'        => 'draft',
            ]);

            $employees = Employee::active()->get();
            $taxes     = TaxConfiguration::active()->get();
            $settings  = Setting::current();
            $overtime  = $settings->overtime_config ?? [];

            $totalGross = $totalDed = $totalNet = 0;

            foreach ($employees as $employee) {
                $slip = $this->computeSlip($run, $employee, $period, $taxes, $overtime);
                $totalGross += $slip->gross_salary + $slip->commission_earned;
                $totalDed   += $slip->total_deductions + $slip->total_tax;
                $totalNet   += $slip->net_pay;
            }

            $run->update([
                'total_gross'      => $totalGross,
                'total_deductions' => $totalDed,
                'total_net'        => $totalNet,
            ]);

            return $run;
        });
    }

    /**
     * Finalize a draft run — locks all slips, marks logs and sales as paid.
     */
    public function finalizeRun(PayrollRun $run): void
    {
        if (! $run->isEditable()) {
            throw new \RuntimeException('Only draft payroll runs can be finalized.');
        }

        DB::transaction(function () use ($run) {
            // Stamp attendance logs with this run
            AttendanceLog::where('payroll_run_id', null)
                ->whereBetween('log_date', [
                    $run->payPeriod->start_date,
                    $run->payPeriod->end_date,
                ])
                ->update(['payroll_run_id' => $run->id]);

            $run->update(['status' => 'finalized', 'finalized_at' => now()]);
            $run->payPeriod->update(['status' => 'closed']);
        });

        event(new PayrollFinalized($run));
    }

    // ── Private ──────────────────────────────────────────────────

    private function computeSlip(
        PayrollRun $run,
        Employee $employee,
        PayPeriod $period,
        $taxes,
        array $overtimeConfig
    ): PayrollSlip {
        $logs = AttendanceLog::where('employee_id', $employee->id)
            ->forPeriod($period->start_date, $period->end_date)
            ->whereNotNull('total_minutes')
            ->whereNull('payroll_run_id')
            ->get();

        $totalMinutes   = $logs->sum('total_minutes');
        $totalHours     = $totalMinutes / 60;
        $dailyThreshold = ($overtimeConfig['daily_threshold_hours'] ?? 8) * 60; // minutes
        $multiplier     = $overtimeConfig['multiplier'] ?? 1.5;

        // Regular vs overtime split (per-log basis)
        $regularMinutes  = 0;
        $overtimeMinutes = 0;
        foreach ($logs as $log) {
            $reg = min($log->total_minutes, $dailyThreshold);
            $ot  = max(0, $log->total_minutes - $dailyThreshold);
            $regularMinutes  += $reg;
            $overtimeMinutes += $ot;
        }

        $regularHours  = $regularMinutes / 60;
        $overtimeHours = $overtimeMinutes / 60;
        $baseRate      = (float) $employee->base_rate;

        $grossSalary = $employee->is_salaried
            ? (float) $baseRate   // base_rate is monthly/period salary
            : ($regularHours * $baseRate) + ($overtimeHours * $baseRate * $multiplier);

        // Commission from confirmed sales this period
        $commissionEarned = (float) Sale::unpaid()
            ->forEmployee($employee->id)
            ->forPeriod($period->start_date, $period->end_date)
            ->sum('agent_points');

        // Create slip
        $slip = PayrollSlip::create([
            'payroll_run_id'      => $run->id,
            'employee_id'         => $employee->id,
            'total_minutes_worked' => $totalMinutes,
            'regular_hours'       => round($regularHours, 2),
            'overtime_hours'      => round($overtimeHours, 2),
            'base_rate'           => $baseRate,
            'gross_salary'        => round($grossSalary, 2),
            'commission_earned'   => round($commissionEarned, 2),
        ]);

        // Apply taxes
        $totalTax = 0;
        foreach ($taxes as $tax) {
            $taxAmount = $tax->calculate($grossSalary);
            if ($taxAmount > 0) {
                PayrollLineItem::create([
                    'payroll_slip_id' => $slip->id,
                    'type'            => 'tax',
                    'description'     => $tax->name,
                    'amount'          => $taxAmount,
                    'source'          => 'tax_config',
                    'source_ref_id'   => $tax->id,
                ]);
                $totalTax += $taxAmount;
            }
        }

        // Create commission line items and link sales to slip
        if ($commissionEarned > 0) {
            $commItem = PayrollLineItem::create([
                'payroll_slip_id' => $slip->id,
                'type'            => 'commission',
                'description'     => 'Sales commission',
                'amount'          => $commissionEarned,
                'source'          => 'sales',
            ]);

            Sale::unpaid()
                ->forEmployee($employee->id)
                ->forPeriod($period->start_date, $period->end_date)
                ->update(['payroll_line_item_id' => $commItem->id]);
        }

        // Apply payroll adjustments (additions & deductions)
        $totalAdditions  = 0;
        $totalDeductions = 0;
        $adjustments = PayrollAdjustment::forEmployee(
            $employee->id,
            $period->start_date,
            $period->end_date
        );

        foreach ($adjustments as $adj) {
            $resolved = $adj->resolve($grossSalary);
            if ($resolved <= 0) continue;

            PayrollLineItem::create([
                'payroll_slip_id' => $slip->id,
                'type'            => $adj->type,
                'description'     => $adj->description,
                'amount'          => $resolved,
                'source'          => 'adjustment',
                'source_ref_id'   => $adj->id,
            ]);

            if ($adj->type === 'addition') {
                $totalAdditions += $resolved;
            } else {
                $totalDeductions += $resolved;
            }

            // One-time adjustment: deactivate after applying
            if (! $adj->is_recurring) {
                $adj->update(['is_active' => false]);
            }
        }

        $netPay = $grossSalary + $commissionEarned + $totalAdditions - $totalDeductions - $totalTax;

        $slip->update([
            'total_tax'         => round($totalTax, 2),
            'total_additions'   => round($totalAdditions, 2),
            'total_deductions'  => round($totalDeductions, 2),
            'net_pay'           => round($netPay, 2),
        ]);

        return $slip;
    }
}
