<?php

namespace App\Models\Performance;

use App\Models\HR\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiSnapshot extends Model
{
    public $timestamps = false;

    protected $fillable = ['employee_id', 'kpi_id', 'period_start', 'period_end', 'value', 'score', 'computed_at'];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'value'        => 'decimal:2',
        'score'        => 'decimal:2',
        'computed_at'  => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function kpi(): BelongsTo
    {
        return $this->belongsTo(KpiDefinition::class, 'kpi_id');
    }

    /** Normalize a raw value to a 0–100 score. */
    public static function normalize(float $value, float $target, string $direction): float
    {
        if ($target == 0) return 100.0;

        $ratio = $value / $target;

        $score = $direction === 'lower_is_better'
            ? max(0, 1 - $ratio) * 100
            : min($ratio, 1) * 100;

        return round($score, 2);
    }
}
