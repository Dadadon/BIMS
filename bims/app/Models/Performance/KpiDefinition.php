<?php

namespace App\Models\Performance;

use App\Models\HR\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiDefinition extends Model
{
    protected $fillable = ['name', 'module_key', 'metric', 'target_value', 'unit', 'direction', 'is_active'];
    protected $casts    = ['target_value' => 'decimal:2', 'is_active' => 'boolean'];

    public function snapshots(): HasMany
    {
        return $this->hasMany(KpiSnapshot::class, 'kpi_id');
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_kpi');
    }
}
