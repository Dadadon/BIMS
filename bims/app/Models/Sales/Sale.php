<?php

namespace App\Models\Sales;

use App\Models\HR\Employee;
use App\Models\HR\Team;
use App\Models\Payroll\PayrollLineItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    protected $fillable = [
        'employee_id', 'team_id', 'sale_type_id', 'sale_date',
        'customer_name', 'customer_phone',
        'total_points', 'agent_points', 'status',
        'compensation_received',
        'payroll_line_item_id', 'metadata',
    ];

    protected $casts = [
        'sale_date'             => 'date',
        'compensation_received' => 'boolean',
        'total_points'          => 'decimal:2',
        'agent_points'          => 'decimal:2',
        'metadata'              => 'array',
    ];

    // ── Relationships ────────────────────────────────────────────

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function saleType(): BelongsTo
    {
        return $this->belongsTo(SaleType::class);
    }

    public function payrollLineItem(): BelongsTo
    {
        return $this->belongsTo(PayrollLineItem::class);
    }

    // ── Metadata helpers (custom fields) ─────────────────────────

    public function getMeta(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    public function setMeta(string $key, mixed $value): void
    {
        $meta = $this->metadata ?? [];
        $meta[$key] = $value;
        $this->metadata = $meta;
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopeCompensated(Builder $query): Builder
    {
        return $query->where('compensation_received', true);
    }

    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->where('compensation_received', true)
                     ->whereNull('payroll_line_item_id');
    }

    public function scopeForPeriod(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('sale_date', [$from, $to]);
    }

    public function scopeForEmployee(Builder $query, int $employeeId): Builder
    {
        return $query->where('employee_id', $employeeId);
    }
}
