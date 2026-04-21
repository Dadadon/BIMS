<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveGroup extends Model
{
    protected $fillable = ['name', 'annual_days'];
    protected $casts    = ['annual_days' => 'decimal:1'];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function leaveTypes(): HasMany
    {
        return $this->hasMany(\App\Models\Leave\LeaveType::class);
    }
}
