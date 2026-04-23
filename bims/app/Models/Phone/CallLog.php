<?php

namespace App\Models\Phone;

use App\Models\HR\Employee;
use App\Models\Sales\Sale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallLog extends Model
{
    protected $fillable = [
        'phone_integration_id', 'employee_id', 'sale_id',
        'direction', 'caller_number', 'callee_number',
        'status', 'disposition',
        'started_at', 'answered_at', 'ended_at', 'duration_seconds',
        'recording_url', 'external_call_id', 'metadata',
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'answered_at' => 'datetime',
        'ended_at'    => 'datetime',
        'metadata'    => 'array',
    ];

    public function integration(): BelongsTo { return $this->belongsTo(PhoneIntegration::class, 'phone_integration_id'); }
    public function employee(): BelongsTo    { return $this->belongsTo(Employee::class); }
    public function sale(): BelongsTo        { return $this->belongsTo(Sale::class); }

    public function getRemoteNumber(): string
    {
        return $this->direction === 'outbound' ? ($this->callee_number ?? '—') : ($this->caller_number ?? '—');
    }

    public function getDurationLabel(): string
    {
        $s = $this->duration_seconds;
        if ($s < 60) return "{$s}s";
        return floor($s / 60) . 'm ' . ($s % 60) . 's';
    }
}
