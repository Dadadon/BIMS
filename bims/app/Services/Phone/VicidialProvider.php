<?php

namespace App\Services\Phone;

use App\Models\Phone\CallLog;
use App\Models\Phone\PhoneIntegration;
use App\Models\Sales\Sale;
use Illuminate\Http\Request;

class VicidialProvider implements PhoneProviderContract
{
    public function __construct(private PhoneIntegration $integration) {}

    public function verifyWebhook(Request $request): bool
    {
        $secret = $this->integration->webhook_secret;
        if (! $secret) return true;
        return $request->header('X-Webhook-Secret') === $secret;
    }

    /**
     * VICIdial can POST CDR (call detail records) to an external URL.
     * Field names match the VICIdial external URL API format.
     */
    public function handleWebhook(Request $request): ?CallLog
    {
        $data = $request->all();

        // VICIdial CDR fields
        $callId    = $data['uniqueid'] ?? $data['call_id'] ?? null;
        $from      = $data['phone_number'] ?? $data['callerid'] ?? null;
        $to        = $data['extension']    ?? $data['did']       ?? null;
        $direction = ($data['call_type'] ?? '') === 'OUT' ? 'outbound' : 'inbound';
        $duration  = (int) ($data['length_in_sec'] ?? $data['duration'] ?? 0);
        $status    = $data['status'] ?? 'A';

        $disposition = match(strtoupper($status)) {
            'A', 'ANSWERED' => 'answered',
            'N', 'NO ANS'   => 'no_answer',
            'B', 'BUSY'     => 'busy',
            'VM'            => 'voicemail',
            default         => 'failed',
        };

        $log = CallLog::updateOrCreate(
            ['external_call_id' => $callId, 'phone_integration_id' => $this->integration->id],
            [
                'direction'        => $direction,
                'caller_number'    => $from,
                'callee_number'    => $to,
                'status'           => 'completed',
                'disposition'      => $disposition,
                'duration_seconds' => $duration,
                'started_at'       => isset($data['call_date']) ? \Carbon\Carbon::parse($data['call_date']) : now(),
                'ended_at'         => now(),
                'recording_url'    => $data['recording_filename'] ?? null,
                'metadata'         => $data,
            ]
        );

        $this->linkToSale($log);

        return $log;
    }

    public function getSoftphoneConfig(string $extension, string $password): array
    {
        return [
            'provider'      => 'sip',
            'sip_uri'       => "sip:{$extension}@{$this->integration->sip_domain}",
            'password'      => $password,
            'websocket_url' => $this->integration->websocket_url,
            'stun_server'   => $this->integration->stun_server,
            'turn_server'   => $this->integration->turn_server,
            'turn_username' => $this->integration->turn_username,
            'turn_password' => $this->integration->turn_password,
        ];
    }

    private function linkToSale(CallLog $log): void
    {
        if ($log->sale_id) return;
        $number = $log->direction === 'inbound' ? $log->caller_number : $log->callee_number;
        if (! $number) return;
        $sale = Sale::where('customer_phone', $number)->latest()->first();
        if ($sale) $log->update(['sale_id' => $sale->id]);
    }
}
