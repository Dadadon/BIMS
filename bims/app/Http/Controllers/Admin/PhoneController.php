<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phone\CallLog;
use App\Models\Phone\PhoneIntegration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhoneController extends Controller
{
    public function index(): View
    {
        $integrations = PhoneIntegration::orderByDesc('is_active')->orderBy('name')->get();
        $recentLogs   = CallLog::with(['employee', 'sale'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.phone.index', compact('integrations', 'recentLogs'));
    }

    public function create(): View
    {
        return view('admin.phone.form', ['integration' => new PhoneIntegration()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateIntegration($request);
        PhoneIntegration::create($validated);

        PhoneIntegration::clearCache();

        return redirect()->route('admin.phone.index')->with('success', 'Integration created.');
    }

    public function edit(PhoneIntegration $phone): View
    {
        return view('admin.phone.form', ['integration' => $phone]);
    }

    public function update(Request $request, PhoneIntegration $phone): RedirectResponse
    {
        $validated = $this->validateIntegration($request, $phone);
        $phone->update($validated);

        PhoneIntegration::clearCache();

        return redirect()->route('admin.phone.index')->with('success', 'Integration updated.');
    }

    public function destroy(PhoneIntegration $phone): RedirectResponse
    {
        $phone->delete();
        PhoneIntegration::clearCache();

        return redirect()->route('admin.phone.index')->with('success', 'Integration removed.');
    }

    public function activate(PhoneIntegration $phone): RedirectResponse
    {
        $phone->activate();
        return back()->with('success', "\"{$phone->name}\" is now the active integration.");
    }

    public function deactivate(PhoneIntegration $phone): RedirectResponse
    {
        $phone->update(['is_active' => false]);
        PhoneIntegration::clearCache();
        return back()->with('success', 'Integration deactivated.');
    }

    public function callLogs(Request $request): View
    {
        $query = CallLog::with(['employee', 'sale', 'integration'])->latest();

        if ($emp = $request->input('employee_id')) {
            $query->where('employee_id', $emp);
        }
        if ($dir = $request->input('direction')) {
            $query->where('direction', $dir);
        }
        if ($disp = $request->input('disposition')) {
            $query->where('disposition', $disp);
        }

        $logs      = $query->paginate(30)->withQueryString();
        $employees = \App\Models\HR\Employee::active()->orderBy('lastname')->get();

        return view('admin.phone.call-logs', compact('logs', 'employees'));
    }

    private function validateIntegration(Request $request, ?PhoneIntegration $existing = null): array
    {
        $rules = [
            'name'           => ['required', 'string', 'max:100'],
            'type'           => ['required', 'in:callhippo,freepbx,vicidial,custom_sip'],
            'api_key'        => ['nullable', 'string', 'max:255'],
            'api_secret'     => ['nullable', 'string', 'max:255'],
            'webhook_secret' => ['nullable', 'string', 'max:255'],
            'account_id'     => ['nullable', 'string', 'max:100'],
            'sip_domain'     => ['nullable', 'string', 'max:255'],
            'sip_port'       => ['nullable', 'integer', 'min:1', 'max:65535'],
            'sip_transport'  => ['nullable', 'in:wss,ws,tcp,udp'],
            'websocket_url'  => ['nullable', 'string', 'max:255'],
            'stun_server'    => ['nullable', 'string', 'max:255'],
            'turn_server'    => ['nullable', 'string', 'max:255'],
            'turn_username'  => ['nullable', 'string', 'max:100'],
            'turn_password'  => ['nullable', 'string', 'max:100'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ];

        $validated = $request->validate($rules);

        // Keep existing secret if not provided on update
        if ($existing && empty($validated['api_secret'])) {
            unset($validated['api_secret']);
        }
        if ($existing && empty($validated['webhook_secret'])) {
            unset($validated['webhook_secret']);
        }

        return $validated;
    }
}
