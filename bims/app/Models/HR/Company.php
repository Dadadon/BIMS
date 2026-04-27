<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected static function newFactory(): \Database\Factories\CompanyFactory
    {
        return \Database\Factories\CompanyFactory::new();
    }


    protected $fillable = ['name', 'commission_model', 'commission_rate', 'is_primary'];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'is_primary'      => 'boolean',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Calculate agent commission points for a sale.
     * - sale_type_rate:      use sale_types.points_per_agent (MPV direct agents)
     * - company_percentage:  total_points × (commission_rate / 100)
     */
    public function calculateAgentPoints(float $totalPoints, float $saleTypeAgentPoints): float
    {
        return match ($this->commission_model) {
            'sale_type_rate'      => $saleTypeAgentPoints,
            'company_percentage'  => round($totalPoints * ($this->commission_rate / 100), 2),
            default               => 0.00,
        };
    }

    public static function primary(): ?self
    {
        return static::where('is_primary', true)->first();
    }
}
