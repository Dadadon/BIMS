<?php

namespace App\Models\Payroll;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TaxConfiguration extends Model
{
    protected $fillable = [
        'name', 'code', 'rate', 'flat_amount', 'applies_to',
        'income_threshold', 'is_employer_contribution', 'is_active',
    ];

    protected $casts = [
        'rate'                    => 'decimal:4',
        'flat_amount'             => 'decimal:2',
        'income_threshold'        => 'decimal:2',
        'is_employer_contribution' => 'boolean',
        'is_active'               => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('is_employer_contribution', false);
    }

    /** Calculate the tax amount for a given income. */
    public function calculate(float $grossIncome): float
    {
        if ($this->income_threshold && $grossIncome < $this->income_threshold) {
            return 0.00;
        }

        if ($this->flat_amount) {
            return (float) $this->flat_amount;
        }

        return round($grossIncome * $this->rate, 2);
    }
}
