<?php

namespace App\Models\Leave;

use App\Models\HR\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    protected $fillable = [
        'employee_id', 'leave_type_id', 'date_from', 'date_to',
        'total_days', 'reason', 'status', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'date_from'   => 'date',
        'date_to'     => 'date',
        'total_days'  => 'decimal:1',
        'reviewed_at' => 'datetime',
    ];

    public function employee(): BelongsTo  { return $this->belongsTo(Employee::class); }
    public function leaveType(): BelongsTo { return $this->belongsTo(LeaveType::class); }
    public function reviewer(): BelongsTo  { return $this->belongsTo(User::class, 'reviewed_by'); }

    public function scopePending(Builder $query): Builder  { return $query->where('status', 'Pending'); }
    public function scopeApproved(Builder $query): Builder { return $query->where('status', 'Approved'); }
}
