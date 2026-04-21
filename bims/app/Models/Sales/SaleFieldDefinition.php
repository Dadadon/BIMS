<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleFieldDefinition extends Model
{
    protected $fillable = [
        'key', 'label', 'field_type', 'options', 'formula',
        'sale_type_id', 'is_required', 'is_active', 'show_in_table', 'show_on_create', 'sort_order',
    ];

    protected $casts = [
        'options'         => 'array',
        'is_required'     => 'boolean',
        'is_active'       => 'boolean',
        'show_in_table'   => 'boolean',
        'show_on_create'  => 'boolean',
    ];

    public function saleType(): BelongsTo
    {
        return $this->belongsTo(SaleType::class);
    }

    /** Fields that apply to a given sale type (global + type-specific). */
    public static function forSaleType(?int $saleTypeId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)
            ->where(function (Builder $q) use ($saleTypeId) {
                $q->whereNull('sale_type_id');
                if ($saleTypeId) {
                    $q->orWhere('sale_type_id', $saleTypeId);
                }
            })
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();
    }
}
