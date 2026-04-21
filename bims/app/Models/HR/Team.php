<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = ['name', 'leader_id', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function leader(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'leader_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(\App\Models\Sales\Sale::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
