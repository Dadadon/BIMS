<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['name', 'slug', 'is_admin'];
    protected $casts    = ['is_admin' => 'boolean'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    /** Check whether this role has a specific permission. */
    public function can(string $module, string $action): bool
    {
        return $this->permissions()
            ->where('module_key', $module)
            ->where('action', $action)
            ->exists();
    }
}
