<?php

namespace App\Models\Payroll;

use App\Models\HR\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class PayrollSlip extends Model
{
    protected $fillable = [
        'payroll_run_id', 'employee_id',
        'total_minutes_worked', 'regular_hours', 'overtime_hours', 'base_rate',
        'gross_salary', 'total_additions', 'total_deductions', 'total_tax',
        'commission_earned', 'net_pay',
    ];

    protected $casts = [
        'base_rate'           => 'decimal:2',
        'gross_salary'        => 'decimal:2',
        'total_additions'     => 'decimal:2',
        'total_deductions'    => 'decimal:2',
        'total_tax'           => 'decimal:2',
        'commission_earned'   => 'decimal:2',
        'net_pay'             => 'decimal:2',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(PayrollLineItem::class);
    }

    public function getDecimalHoursWorkedAttribute(): float
    {
        return round($this->total_minutes_worked / 60, 2);
    }

    /**
     * Year-to-date totals for this employee, summing all finalized slips
     * in the same calendar year up to and including this slip's pay period.
     */
    public function ytd(): object
    {
        $periodEnd = $this->payrollRun->payPeriod->end_date;
        $year      = \Carbon\Carbon::parse($periodEnd)->year;

        $row = static::query()
            ->join('payroll_runs', 'payroll_runs.id', '=', 'payroll_slips.payroll_run_id')
            ->join('pay_periods',  'pay_periods.id',  '=', 'payroll_runs.pay_period_id')
            ->where('payroll_slips.employee_id', $this->employee_id)
            ->where('payroll_runs.status', 'finalized')
            ->whereYear('pay_periods.end_date', $year)
            ->where('pay_periods.end_date', '<=', $periodEnd)
            ->select(DB::raw('
                COALESCE(SUM(gross_salary), 0)       AS ytd_gross,
                COALESCE(SUM(commission_earned), 0)  AS ytd_commission,
                COALESCE(SUM(total_additions), 0)    AS ytd_additions,
                COALESCE(SUM(total_deductions), 0)   AS ytd_deductions,
                COALESCE(SUM(total_tax), 0)          AS ytd_tax,
                COALESCE(SUM(net_pay), 0)            AS ytd_net
            '))
            ->first();

        return (object) [
            'gross'      => (float) ($row->ytd_gross       ?? 0),
            'commission' => (float) ($row->ytd_commission  ?? 0),
            'additions'  => (float) ($row->ytd_additions   ?? 0),
            'deductions' => (float) ($row->ytd_deductions  ?? 0),
            'tax'        => (float) ($row->ytd_tax         ?? 0),
            'net'        => (float) ($row->ytd_net         ?? 0),
        ];
    }
}
