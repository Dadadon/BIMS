<?php

namespace App\Models\Attendance;

use App\Models\HR\Employee;
use App\Models\Payroll\PayrollRun;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    protected $fillable = [
        'employee_id', 'log_date', 'clock_in', 'clock_out',
        'total_minutes', 'reason', 'status_in', 'status_out',
        'comment', 'logged_by', 'is_approved', 'approved_by', 'payroll_run_id',
    ];

    protected $casts = [
        'log_date'    => 'date',
        'clock_in'    => 'datetime',
        'clock_out'   => 'datetime',
        'is_approved' => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────────

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    // ── Accessors ────────────────────────────────────────────────

    /** Returns duration as "Xh Ym" string for display. */
    public function getDurationAttribute(): string
    {
        if ($this->total_minutes === null) {
            return 'Active';
        }
        $h = intdiv($this->total_minutes, 60);
        $m = $this->total_minutes % 60;
        return "{$h}h {$m}m";
    }

    /** Returns total hours as a decimal float (e.g. 8.5 = 8h 30m). */
    public function getDecimalHoursAttribute(): float
    {
        return round(($this->total_minutes ?? 0) / 60, 2);
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->where('log_date', $date);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('clock_out');
    }

    public function scopeForPeriod(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('log_date', [$from, $to]);
    }

    public function scopeUnpaidFor(Builder $query, int $payrollRunId): Builder
    {
        return $query->whereNull('payroll_run_id')->where('is_approved', true);
    }
}
