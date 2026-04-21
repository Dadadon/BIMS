<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;

class CancelReason extends Model
{
    protected $fillable = ['code', 'label', 'description', 'is_voluntary', 'is_controllable'];
    protected $casts    = ['is_voluntary' => 'boolean', 'is_controllable' => 'boolean'];
}
