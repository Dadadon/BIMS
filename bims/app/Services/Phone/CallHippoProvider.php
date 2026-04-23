<?php

namespace App\Services\Phone;

use App\Models\Phone\CallLog;
use App\Models\Phone\PhoneIntegration;
use App\Models\Sales\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CallHippoProvider implements PhoneProviderContract
{
    public function __construct(private PhoneIntegration $integration) {}

    public function verifyWebhook(Request $request): bool
    {
        $secret = $this->integration->webhook_secret;
        if (! $secret) return true;

        $signature = $request->header('X-CallHippo-Signature');
        $expected  = hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $signature ?? '');
    }

    public function handleWebhook(Request $request): ?CallLog
    {
        $data = $request->json()->all();
        $event = $data['event'] ?? $data['type'] ?? null;

        // Map CallHippo event types to our statuses
        $statusMap = [
            'CALL_INITIATED'   => 'ringing',
            'CALL_ANSWERED'    => 'connected',
            'CALL_ENDED'       => 'completed',
            'CALL_MISSED'      => 'missed',
            'CALL_FAILED'      => 'failed',
        ];

        if (! isset($statusMap[$event])) return null;

        $callId    = $data['callId'] ?? $data['call_id'] ?? null;
        $direction = strtolower($data['direction'] ?? 'inbound') === 'outbound' ? 'outbound' : 'inbound';
        $from      = $data['from'] ?? $data['caller_number'] ?? null;
        $to        = $data['to']   ?? $data['callee_number'] ?? null;
        $agentNum  = $data['agentNumber'] ?? null;
        $duration  = (int) ($data['duration'] ?? $data['callDuration'] ?? 0);

        $disposition = match($statusMap[$event]) {
            'completed' => 'answered',
            'missed'    => 'no_answer',
            'failed'    => 'failed',
            default     => null,
        };

        $log = CallLog::updateOrCreate(
            ['external_call_id' => $callId, 'phone_integration_id' => $this->integration->id],
            [
                'direction'        => $direction,
                'caller_number'    => $from,
                'callee_number'    => $to,
                'status'           => $statusMap[$event],
                'disposition'      => $disposition,
                'duration_seconds' => $duration,
                'started_at'       => isset($data['startTime']) ? \Carbon\Carbon::parse($data['startTime']) : now(),
                'ended_at'         => $statusMap[$event] === 'completed' ? now() : null,
                'recording_url'    => $data['recordingUrl'] ?? null,
                'metadata'         => $data,
            ]
        );

        $this->linkToSale($log);

        return $log;
    }

    public function getSoftphoneConfig(string $extension, string $password): array
    {
        // CallHippo uses their own JS SDK — return config for the frontend embed
        return [
            'provider'   => 'callhippo',
            'api_key'    => $this->integration->api_key,
            'account_id' => $this->integration->account_id,
            'extension'  => $extension,
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
