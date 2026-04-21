<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class EmployeeFieldDefinition extends Model
{
    protected $fillable = [
        'key', 'label', 'field_type', 'options',
        'is_required', 'is_active', 'show_on_create', 'sort_order',
    ];

    protected $casts = [
        'options'        => 'array',
        'is_required'    => 'boolean',
        'is_active'      => 'boolean',
        'show_on_create' => 'boolean',
    ];

    public static function active(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();
    }

    public static function forCreate(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)
            ->where('show_on_create', true)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();
    }
}
