<?php

namespace App\Models\Phone;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class PhoneIntegration extends Model
{
    protected $fillable = [
        'name', 'type', 'is_active',
        'webhook_secret',
        'sip_domain', 'sip_port', 'sip_transport', 'websocket_url',
        'stun_server', 'turn_server', 'turn_username', 'turn_password',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sip_port'  => 'integer',
    ];

    protected $hidden = ['webhook_secret', 'turn_password', 'sip_password'];

    public function callLogs(): HasMany
    {
        return $this->hasMany(CallLog::class);
    }

    /** Activate this integration and deactivate all others. */
    public function activate(): void
    {
        static::query()->update(['is_active' => false]);
        $this->update(['is_active' => true]);
        Cache::forget('phone.active_integration');
    }

    /** Get the single active integration (cached). */
    public static function active(): ?self
    {
        return Cache::remember('phone.active_integration', 60, fn() =>
            static::where('is_active', true)->first()
        );
    }

    public static function clearCache(): void
    {
        Cache::forget('phone.active_integration');
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'freepbx'    => 'FreePBX',
            'vicidial'   => 'VICIdial',
            'custom_sip' => 'Custom SIP',
            default      => $this->type,
        };
    }
}
