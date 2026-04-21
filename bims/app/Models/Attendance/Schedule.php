<?php

namespace App\Models\Attendance;

use App\Models\HR\Employee;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    protected $fillable = [
        'employee_id', 'shift_template_id', 'name',
        'shift_in', 'shift_out', 'is_overnight', 'break_minutes',
        'days_of_week', 'is_archived', 'effective_from', 'effective_to',
    ];

    protected $casts = [
        'is_overnight'   => 'boolean',
        'is_archived'    => 'boolean',
        'effective_from' => 'date',
        'effective_to'   => 'date',
        'days_of_week'   => 'array',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shiftTemplate(): BelongsTo
    {
        return $this->belongsTo(ShiftTemplate::class);
    }

    public function label(): string
    {
        if ($this->shiftTemplate) return $this->shiftTemplate->name;
        return $this->name ?: 'Custom';
    }

    public function color(): string
    {
        return $this->shiftTemplate?->color ?? '#6b7280';
    }

    /**
     * Find the active schedule for an employee on a given date.
     * Uses ISO day-of-week: 1=Mon … 7=Sun.
     */
    public static function forDate(int $employeeId, Carbon $date): ?self
    {
        $iso = (int) $date->isoFormat('E');

        return self::with('shiftTemplate')
            ->where('employee_id', $employeeId)
            ->where('is_archived', false)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_from')->orWhere('effective_from', '<=', $date->toDateString());
            })
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $date->toDateString());
            })
            ->where(function ($q) use ($iso) {
                $q->whereNull('days_of_week')
                  ->orWhereJsonContains('days_of_week', $iso);
            })
            ->orderByDesc('effective_from')
            ->first();
    }
}
