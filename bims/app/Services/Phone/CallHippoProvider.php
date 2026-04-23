<?php

namespace App\Services\Phone;

use App\Models\HR\Employee;
use App\Models\Phone\CallLog;
use App\Models\Phone\PhoneIntegration;
use App\Models\Sales\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CallHippoProvider implements PhoneProviderContract
{
    public function __construct(private PhoneIntegration $integration) {}

    /**
     * CallHippo does not sign payloads with HMAC.
     * Verification uses a shared secret passed as query param: ?secret=xxx
     */
    public function verifyWebhook(Request $request): bool
    {
        $secret = $this->integration->webhook_secret;
        if (! $secret) return true;

        return hash_equals($secret, $request->query('secret', ''));
    }

    /**
     * Handles two CallHippo webhook types:
     *
     * 1. "Calling Activity" — fires once when a call ends; carries `callSid`, `status`, `duration`, etc.
     * 2. "Call Status Notification" — fires per-event; `type` = CALL_INITIATED / CALL_ANSWERED / etc.
     */
    public function handleWebhook(Request $request): ?CallLog
    {
        $data      = $request->json()->all();
        $eventType = strtoupper($data['type'] ?? '');

        // Per-event status notifications
        if (in_array($eventType, ['CALL_INITIATED', 'CALL_RINGING', 'CALL_ANSWERED', 'CALL_ENDED', 'CALL_MISSED', 'CALL_FAILED'])) {
            return $this->handleStatusNotification($data, $eventType);
        }

        // Completed call activity log
        if (isset($data['callSid']) || isset($data['status'])) {
            return $this->handleCallLog($data);
        }

        return null;
    }

    public function getSoftphoneConfig(string $extension, string $password): array
    {
        // CallHippo uses its own browser widget/SDK, not SIP.js
        return [
            'provider'   => 'callhippo',
            'api_key'    => $this->integration->api_key,
            'account_id' => $this->integration->account_id,
            'extension'  => $extension,
        ];
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    /** Processes a completed call log webhook (Calling Activity type) */
    private function handleCallLog(array $data): ?CallLog
    {
        $callSid    = $data['callSid'] ?? $data['call_id'] ?? null;
        $direction  = $this->resolveDirection($data);
        $from       = $data['from'] ?? null;
        $to         = $data['to'] ?? null;
        $duration   = (int) ($data['duration'] ?? 0);
        $rawStatus  = strtolower($data['status'] ?? 'answered');
        $agentEmail = $data['email'] ?? $data['agentEmail'] ?? null;

        $disposition = match($rawStatus) {
            'answered'                      => 'answered',
            'missed', 'no-answer', 'no_answer' => 'no_answer',
            'voicemail'                     => 'voicemail',
            'busy'                          => 'busy',
            'rejected', 'failed'            => 'failed',
            default                         => 'answered',
        };

        $startedAt = isset($data['time']) ? Carbon::parse($data['time']) : now()->subSeconds($duration);
        $endedAt   = $startedAt->copy()->addSeconds($duration);

        $log = CallLog::updateOrCreate(
            ['external_call_id' => $callSid, 'phone_integration_id' => $this->integration->id],
            [
                'direction'        => $direction,
                'caller_number'    => $from,
                'callee_number'    => $to,
                'status'           => 'completed',
                'disposition'      => $disposition,
                'duration_seconds' => $duration,
                'started_at'       => $startedAt,
                'answered_at'      => $disposition === 'answered' ? $startedAt : null,
                'ended_at'         => $endedAt,
                'recording_url'    => $data['recordingUrl'] ?? null,
                'metadata'         => $data,
            ]
        );

        $this->matchEmployee($log, $agentEmail);
        $this->linkToSale($log);

        return $log;
    }

    /** Processes a per-event Call Status Notification webhook */
    private function handleStatusNotification(array $data, string $eventType): ?CallLog
    {
        $callSid    = $data['callSid'] ?? $data['call_id'] ?? null;
        $direction  = $this->resolveDirection($data);
        $from       = $data['from'] ?? null;
        $to         = $data['to'] ?? null;
        $agentEmail = $data['email'] ?? $data['agentEmail'] ?? null;

        $statusMap = [
            'CALL_INITIATED' => 'ringing',
            'CALL_RINGING'   => 'ringing',
            'CALL_ANSWERED'  => 'connected',
            'CALL_ENDED'     => 'completed',
            'CALL_MISSED'    => 'missed',
            'CALL_FAILED'    => 'failed',
        ];

        $status = $statusMap[$eventType] ?? 'ringing';

        $disposition = match($status) {
            'completed' => 'answered',
            'missed'    => 'no_answer',
            'failed'    => 'failed',
            default     => null,
        };

        $timestamps = [];
        if ($status === 'ringing')                          $timestamps['started_at']  = now();
        if ($status === 'connected')                        $timestamps['answered_at'] = now();
        if (in_array($status, ['completed','missed','failed'])) $timestamps['ended_at'] = now();

        $log = CallLog::updateOrCreate(
            ['external_call_id' => $callSid, 'phone_integration_id' => $this->integration->id],
            array_merge([
                'direction'     => $direction,
                'caller_number' => $from,
                'callee_number' => $to,
                'status'        => $status,
                'disposition'   => $disposition,
                'metadata'      => $data,
            ], $timestamps)
        );

        $this->matchEmployee($log, $agentEmail);
        $this->linkToSale($log);

        return $log;
    }

    private function resolveDirection(array $data): string
    {
        $callType = strtolower($data['callType'] ?? $data['direction'] ?? 'incoming');
        return str_contains($callType, 'out') ? 'outbound' : 'inbound';
    }

    /** Match call to employee via agent email → employee.company_email or email */
    private function matchEmployee(CallLog $log, ?string $agentEmail): void
    {
        if ($log->employee_id || ! $agentEmail) return;

        $employee = Employee::where('company_email', $agentEmail)
            ->orWhere('email', $agentEmail)
            ->first();

        if ($employee) $log->update(['employee_id' => $employee->id]);
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
