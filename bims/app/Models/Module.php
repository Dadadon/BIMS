<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Module extends Model
{
    public $timestamps = false;

    protected $fillable = ['key', 'label', 'description', 'is_core', 'is_enabled', 'settings'];

    protected $casts = [
        'is_core'    => 'boolean',
        'is_enabled' => 'boolean',
        'settings'   => 'array',
    ];

    /** Check if a module is enabled (cached for 60s). */
    public static function isEnabled(string $key): bool
    {
        return Cache::remember("module.{$key}.enabled", 60, function () use ($key) {
            $module = static::where('key', $key)->first();
            return $module?->is_enabled ?? false;
        });
    }

    /** Toggle and bust cache. */
    public function toggle(): void
    {
        if ($this->is_core) {
            throw new \RuntimeException("Core modules cannot be toggled.");
        }
        $this->update(['is_enabled' => ! $this->is_enabled]);
        Cache::forget("module.{$this->key}.enabled");
    }
}
