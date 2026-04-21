<?php

namespace App\Models\Payroll;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollLineItem extends Model
{
    protected $fillable = [
        'payroll_slip_id', 'type', 'description',
        'amount', 'source', 'source_ref_id',
    ];

    protected $casts = ['amount' => 'decimal:2'];

    public function slip(): BelongsTo
    {
        return $this->belongsTo(PayrollSlip::class, 'payroll_slip_id');
    }
}
