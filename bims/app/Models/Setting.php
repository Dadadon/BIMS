<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'id';

    protected $fillable = [
        'company_name', 'timezone', 'currency', 'date_format', 'time_format',
        'clock_comment', 'rfid_enabled', 'ip_whitelist', 'allowed_ips',
        'theme', 'logo_path', 'overtime_config', 'max_attachment_mb',
        'external_auth_domains',
    ];

    protected $casts = [
        'clock_comment'        => 'boolean',
        'rfid_enabled'         => 'boolean',
        'overtime_config'      => 'array',
        'external_auth_domains' => 'array',
    ];

    /** Always work with the single row. */
    public static function current(): self
    {
        return static::firstOrFail();
    }

    /** Check if an IP is whitelisted. Empty whitelist = allow all. */
    public function isIpAllowed(string $ip): bool
    {
        if (empty($this->ip_whitelist)) {
            return true;
        }
        $allowed = array_map('trim', explode(',', $this->ip_whitelist));
        return in_array($ip, $allowed, true);
    }
}
