<?php

namespace App\Services\Phone;

use App\Models\Phone\CallLog;
use App\Models\Phone\PhoneIntegration;
use App\Models\Sales\Sale;
use Illuminate\Http\Request;

class CustomSipProvider implements PhoneProviderContract
{
    public function __construct(private PhoneIntegration $integration) {}

    public function verifyWebhook(Request $request): bool
    {
        $secret = $this->integration->webhook_secret;
        if (! $secret) return true;
        return $request->header('X-Webhook-Secret') === $secret;
    }

    /** Generic CDR webhook — accepts a normalized JSON payload. */
    public function handleWebhook(Request $request): ?CallLog
    {
        $data = $request->json()->all();

        $callId    = $data['call_id']   ?? uniqid('sip_');
        $direction = ($data['direction'] ?? 'inbound') === 'outbound' ? 'outbound' : 'inbound';
        $from      = $data['from']      ?? $data['caller'] ?? null;
        $to        = $data['to']        ?? $data['callee'] ?? null;
        $status    = $data['status']    ?? 'completed';
        $duration  = (int) ($data['duration'] ?? 0);

        $disposition = match($status) {
            'answered'  => 'answered',
            'no_answer' => 'no_answer',
            'busy'      => 'busy',
            'voicemail' => 'voicemail',
            default     => 'failed',
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
                'started_at'       => isset($data['started_at']) ? \Carbon\Carbon::parse($data['started_at']) : now(),
                'ended_at'         => now(),
                'recording_url'    => $data['recording_url'] ?? null,
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
