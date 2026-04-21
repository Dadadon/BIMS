<?php

namespace App\Models\Leave;

use App\Models\HR\LeaveGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    protected $fillable = ['leave_group_id', 'name', 'is_paid', 'days_per_year'];
    protected $casts    = ['is_paid' => 'boolean'];

    public function leaveGroup(): BelongsTo { return $this->belongsTo(LeaveGroup::class); }
    public function requests(): HasMany     { return $this->hasMany(LeaveRequest::class); }
}
