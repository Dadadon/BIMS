<?php

namespace App\Models\Payroll;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayPeriod extends Model
{
    protected $fillable = ['label', 'period_type', 'start_date', 'end_date', 'pay_date', 'status'];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'pay_date'   => 'date',
    ];

    public function payrollRuns(): HasMany
    {
        return $this->hasMany(PayrollRun::class);
    }

    public static function current(): ?self
    {
        return static::where('status', 'open')
                     ->where('start_date', '<=', today())
                     ->where('end_date', '>=', today())
                     ->first();
    }
}
