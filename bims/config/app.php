<?php

use Illuminate\Support\Facades\Facade;

return [

    'name'  => env('APP_NAME', 'BIMS'),
    'env'   => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url'   => env('APP_URL', 'http://localhost'),

    'timezone' => 'UTC',
    'locale'   => 'en',
    'fallback_locale' => 'en',
    'faker_locale'    => 'en_US',

    'cipher' => 'AES-256-CBC',
    'key'    => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    'maintenance' => [
        'driver' => 'file',
    ],

    // ── Custom application config ──────────────────────────────────────────

    /** Maximum size (MB) for chat file attachments. */
    'max_chat_attachment_mb' => (int) env('MAX_CHAT_ATTACHMENT_MB', 10),

];
