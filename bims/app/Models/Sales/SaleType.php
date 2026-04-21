<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleType extends Model
{
    protected $fillable = [
        'product_category', 'portal', 'product_code',
        'total_points', 'points_per_agent', 'is_active',
    ];

    protected $casts = [
        'total_points'    => 'integer',
        'points_per_agent' => 'decimal:2',
        'is_active'       => 'boolean',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /** Computed display name: "Internet (Xfinity)" */
    public function getNameAttribute(): string
    {
        $label = $this->product_category ?? '';
        if ($this->portal) {
            $label .= " ({$this->portal})";
        }
        return trim($label);
    }
}
