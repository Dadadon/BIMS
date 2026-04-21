<?php

namespace App\Models\Payroll;

use App\Models\HR\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollAdjustment extends Model
{
    protected $fillable = [
        'employee_id', 'type', 'category', 'description',
        'amount_type', 'amount', 'is_recurring',
        'effective_date', 'expires_date', 'is_active',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'is_recurring'   => 'boolean',
        'is_active'      => 'boolean',
        'effective_date' => 'date',
        'expires_date'   => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * All active adjustments that apply to a given employee
     * and fall within the given pay period date range.
     */
    public static function forEmployee(int $employeeId, string $periodStart, string $periodEnd): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)
            ->where(fn(Builder $q) => $q
                ->where('employee_id', $employeeId)
                ->orWhereNull('employee_id')
            )
            ->where(fn(Builder $q) => $q
                ->whereNull('effective_date')
                ->orWhere('effective_date', '<=', $periodEnd)
            )
            ->where(fn(Builder $q) => $q
                ->whereNull('expires_date')
                ->orWhere('expires_date', '>=', $periodStart)
            )
            ->orderBy('type')
            ->orderBy('description')
            ->get();
    }

    /** Resolve the monetary amount given a gross salary base. */
    public function resolve(float $grossSalary): float
    {
        return $this->amount_type === 'percentage'
            ? round($grossSalary * ($this->amount / 100), 2)
            : (float) $this->amount;
    }

    public static function categories(): array
    {
        return ['allowance', 'bonus', 'loan_repayment', 'cash_advance', 'absence', 'late', 'other'];
    }
}
