<?php

namespace App\Http\Controllers\Phone;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\Phone\PhoneIntegration;
use App\Services\Phone\PhoneProviderFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhoneWebhookController extends Controller
{
    public function handle(Request $request, PhoneIntegration $phone): JsonResponse
    {
        $provider = PhoneProviderFactory::make($phone);

        if (! $provider->verifyWebhook($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $log = $provider->handleWebhook($request);

        // Attempt to match log to an employee by their SIP extension
        if ($log && ! $log->employee_id) {
            $ext = $log->direction === 'inbound' ? $log->callee_number : $log->caller_number;
            if ($ext) {
                $employee = Employee::where('sip_extension', $ext)->first();
                if ($employee) $log->update(['employee_id' => $employee->id]);
            }
        }

        return response()->json(['received' => true]);
    }

    /** Called by the SIP.js frontend to log a call initiated from the browser. */
    public function logCall(Request $request): JsonResponse
    {
        $integration = PhoneIntegration::active();
        if (! $integration) {
            return response()->json(['error' => 'No active integration'], 422);
        }

        $validated = $request->validate([
            'direction'        => ['required', 'in:inbound,outbound'],
            'caller_number'    => ['nullable', 'string'],
            'callee_number'    => ['nullable', 'string'],
            'status'           => ['required', 'in:ringing,connected,completed,missed,failed'],
            'disposition'      => ['nullable', 'in:answered,no_answer,busy,voicemail,failed'],
            'duration_seconds' => ['nullable', 'integer'],
            'started_at'       => ['nullable', 'date'],
            'ended_at'         => ['nullable', 'date'],
            'external_call_id' => ['nullable', 'string'],
        ]);

        $employee = auth()->user()?->employee;

        $base = array_merge($validated, [
            'phone_integration_id' => $integration->id,
            'employee_id'          => $employee?->id,
        ]);

        $log = ! empty($validated['external_call_id'])
            ? \App\Models\Phone\CallLog::updateOrCreate(
                ['external_call_id' => $validated['external_call_id'], 'phone_integration_id' => $integration->id],
                $base
              )
            : \App\Models\Phone\CallLog::create($base);

        // Auto-link to sale by phone number
        if (! $log->sale_id) {
            $number = $validated['direction'] === 'outbound'
                ? ($validated['callee_number'] ?? null)
                : ($validated['caller_number'] ?? null);

            if ($number) {
                $sale = \App\Models\Sales\Sale::where('customer_phone', $number)->latest()->first();
                if ($sale) $log->update(['sale_id' => $sale->id]);
            }
        }

        return response()->json(['id' => $log->id]);
    }
}
