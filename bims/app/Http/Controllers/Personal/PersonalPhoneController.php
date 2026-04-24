<?php

namespace App\Http\Controllers\Personal;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\Phone\CallLog;
use App\Models\Phone\PhoneIntegration;
use App\Services\Phone\PhoneProviderFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PersonalPhoneController extends Controller
{
    public function index(): View
    {
        $employee = auth()->user()->employee;
        $logs     = collect();

        if ($employee) {
            $logs = CallLog::with(['sale', 'integration'])
                ->where('employee_id', $employee->id)
                ->latest()
                ->paginate(30);
        }

        return view('personal.phone', compact('employee', 'logs'));
    }

    /** Returns internal (employees with extensions) and external (recent call history) contacts. */
    public function contacts(): JsonResponse
    {
        $internal = Employee::whereNotNull('sip_extension')
            ->where('is_active', true)
            ->orderBy('lastname')
            ->get()
            ->map(fn($e) => [
                'name'   => $e->fullname,
                'number' => $e->sip_extension,
                'label'  => 'Ext ' . $e->sip_extension,
            ]);

        $employee = auth()->user()->employee;
        $external = collect();

        if ($employee) {
            $external = CallLog::where('employee_id', $employee->id)
                ->where('direction', 'outbound')
                ->whereNotNull('callee_number')
                ->where('callee_number', '!=', '')
                ->select('callee_number', DB::raw('MAX(created_at) as last_called'))
                ->groupBy('callee_number')
                ->orderByDesc('last_called')
                ->limit(20)
                ->get()
                ->map(fn($r) => [
                    'name'   => null,
                    'number' => $r->callee_number,
                    'label'  => $r->callee_number,
                ]);
        }

        return response()->json(compact('internal', 'external'));
    }

    /** Returns the SIP.js config for the current employee's extension. */
    public function softphoneConfig(): JsonResponse
    {
        $employee    = auth()->user()->employee;
        $integration = PhoneIntegration::active();

        if (! $integration || ! $employee?->sip_extension) {
            return response()->json(['enabled' => false]);
        }

        $provider = PhoneProviderFactory::make($integration);
        $config   = $provider->getSoftphoneConfig(
            $employee->sip_extension,
            $employee->sip_password ?? ''
        );

        return response()->json(array_merge($config, ['enabled' => true]));
    }
}
