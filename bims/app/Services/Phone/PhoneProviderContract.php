<?php

namespace App\Services\Phone;

use App\Models\Phone\CallLog;
use App\Models\Phone\PhoneIntegration;
use Illuminate\Http\Request;

interface PhoneProviderContract
{
    /** Verify the incoming webhook signature. */
    public function verifyWebhook(Request $request): bool;

    /** Parse the webhook payload and upsert a CallLog. Returns the log or null if irrelevant. */
    public function handleWebhook(Request $request): ?CallLog;

    /** Return the SIP.js / SDK config needed by the frontend for this employee's extension. */
    public function getSoftphoneConfig(string $extension, string $password): array;
}
