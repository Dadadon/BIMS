<?php

namespace App\Models\Report;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedReport extends Model
{
    protected $fillable = [
        'name', 'description', 'data_source', 'columns', 'filters',
        'group_by', 'aggregate_fn', 'aggregate_field', 'chart_type', 'created_by',
    ];

    protected $casts = [
        'columns' => 'array',
        'filters' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isGrouped(): bool
    {
        return (bool) $this->group_by;
    }

    public function isChart(): bool
    {
        return $this->chart_type !== 'table';
    }
}
