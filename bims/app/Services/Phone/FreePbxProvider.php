<?php

namespace App\Services\Phone;

use App\Models\Phone\CallLog;
use App\Models\Phone\PhoneIntegration;
use App\Models\Sales\Sale;
use Illuminate\Http\Request;

class FreePbxProvider implements PhoneProviderContract
{
    public function __construct(private PhoneIntegration $integration) {}

    public function verifyWebhook(Request $request): bool
    {
        $secret = $this->integration->webhook_secret;
        if (! $secret) return true;

        // FreePBX ARI webhooks can use a shared secret header
        return $request->header('X-Webhook-Secret') === $secret;
    }

    public function handleWebhook(Request $request): ?CallLog
    {
        $data  = $request->json()->all();
        $event = $data['type'] ?? null;

        // Asterisk ARI event types
        $statusMap = [
            'StasisStart'  => 'ringing',
            'ChannelStateChange' => 'connected',
            'StasisEnd'    => 'completed',
            'ChannelHangupRequest' => 'completed',
        ];

        if (! isset($statusMap[$event])) return null;

        $channel   = $data['channel'] ?? [];
        $callId    = $channel['id'] ?? $data['playback']['id'] ?? uniqid('fpbx_');
        $from      = $channel['caller']['number'] ?? null;
        $to        = $channel['dialplan']['exten'] ?? null;
        $direction = 'inbound';
        $duration  = 0;

        if ($statusMap[$event] === 'completed' && isset($data['channel']['state'])) {
            $duration = (int) ($data['elapsed_milliseconds'] ?? 0) / 1000;
        }

        $log = CallLog::updateOrCreate(
            ['external_call_id' => $callId, 'phone_integration_id' => $this->integration->id],
            [
                'direction'        => $direction,
                'caller_number'    => $from,
                'callee_number'    => $to,
                'status'           => $statusMap[$event],
                'disposition'      => $statusMap[$event] === 'completed' ? 'answered' : null,
                'duration_seconds' => $duration,
                'started_at'       => now(),
                'ended_at'         => $statusMap[$event] === 'completed' ? now() : null,
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
        $number = $log->caller_number;
        if (! $number) return;
        $sale = Sale::where('customer_phone', $number)->latest()->first();
        if ($sale) $log->update(['sale_id' => $sale->id]);
    }
}
