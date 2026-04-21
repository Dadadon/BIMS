<?php

namespace App\Models\HR;

use App\Models\Attendance\AttendanceLog;
use App\Models\Attendance\Schedule;
use App\Models\Leave\LeaveRequest;
use App\Models\Payroll\PayrollSlip;
use App\Models\Performance\KpiDefinition;
use App\Models\Performance\KpiSnapshot;
use App\Models\Sales\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'department_id', 'job_title_id', 'leave_group_id', 'team_id',
        'employee_code', 'firstname', 'lastname', 'middle_name',
        'email', 'company_email', 'phone', 'gender', 'civil_status',
        'birthday', 'birthplace', 'home_address', 'national_id',
        'employment_type', 'employment_status', 'start_date',
        'regularization_date', 'is_salaried', 'base_rate', 'avatar', 'metadata',
    ];

    protected $casts = [
        'birthday'            => 'date',
        'start_date'          => 'date',
        'regularization_date' => 'date',
        'is_salaried'         => 'boolean',
        'base_rate'           => 'decimal:2',
        'metadata'            => 'array',
    ];

    // ── Relationships ────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function jobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class);
    }

    public function leaveGroup(): BelongsTo
    {
        return $this->belongsTo(LeaveGroup::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function activeSchedule(): HasOne
    {
        return $this->hasOne(Schedule::class)->where('is_archived', false)->latestOfMany();
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function payrollSlips(): HasMany
    {
        return $this->hasMany(PayrollSlip::class);
    }

    public function kpiSnapshots(): HasMany
    {
        return $this->hasMany(KpiSnapshot::class);
    }

    public function kpiDefinitions(): BelongsToMany
    {
        return $this->belongsToMany(KpiDefinition::class, 'employee_kpi');
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

    // ── Accessors ────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        $mid = $this->middle_name ? " {$this->middle_name}" : '';
        return "{$this->firstname}{$mid} {$this->lastname}";
    }

    public function getDisplayNameAttribute(): string
    {
        return strtoupper("{$this->lastname}, {$this->firstname}");
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('employment_status', 'Active');
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }
}
