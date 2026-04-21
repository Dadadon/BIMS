<?php

namespace App\Models\Attendance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShiftTemplate extends Model
{
    protected $fillable = [
        'name', 'shift_in', 'shift_out', 'is_overnight',
        'break_minutes', 'color', 'is_active',
    ];

    protected $casts = [
        'is_overnight' => 'boolean',
        'is_active'    => 'boolean',
    ];

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function durationLabel(): string
    {
        $in  = \Carbon\Carbon::parse($this->shift_in);
        $out = \Carbon\Carbon::parse($this->shift_out);
        if ($this->is_overnight) $out->addDay();
        $mins = $in->diffInMinutes($out) - $this->break_minutes;
        $h = intdiv($mins, 60);
        $m = $mins % 60;
        return $m ? "{$h}h {$m}m" : "{$h}h";
    }
}
