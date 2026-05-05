<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * Encrypts PII on write; decrypts on read with a plaintext fallback.
 *
 * Legacy rows that pre-date encryption are returned as-is rather than
 * throwing DecryptException — they become encrypted the next time the
 * record is saved.
 */
class EncryptedPii implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            // Pre-encryption plaintext — return as-is; will be encrypted on next save
            return $value;
        }
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value !== null ? Crypt::encryptString((string) $value) : null;
    }
}
