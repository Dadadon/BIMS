<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'role_id', 'employee_id', 'name', 'email',
        'password', 'acc_type', 'status',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'status'            => 'boolean',
        'password'          => 'hashed',
    ];

    // ── Relationships ────────────────────────────────────────────

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HR\Employee::class, 'employee_id');
    }

    // ── Permission Helpers ───────────────────────────────────────

    public function hasPermission(string $module, string $action): bool
    {
        // Admins and super-admins bypass all permission checks
        if ($this->isAdmin() || $this->isSuperAdmin()) {
            return true;
        }

        return $this->role?->can($module, $action) ?? false;
    }

    public function isAdmin(): bool
    {
        return $this->acc_type === 'admin';
    }

    public function isEmployee(): bool
    {
        return $this->acc_type === 'employee';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role?->slug === 'system_admin';
    }

    /** The team_id this user is scoped to, or null if they see all. */
    public function scopedTeamId(): ?int
    {
        return $this->employee?->team_id;
    }
}
