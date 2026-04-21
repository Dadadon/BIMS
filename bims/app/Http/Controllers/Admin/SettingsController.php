<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $settings = Setting::current();
        $modules  = Module::orderBy('label')->get();

        return view('admin.settings.index', compact('settings', 'modules'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name'       => ['required', 'string', 'max:150'],
            'timezone'           => ['required', 'timezone'],
            'date_format'        => ['required', 'string', 'max:20'],
            'currency'           => ['required', 'string', 'max:10'],
            'overtime_threshold' => ['required', 'numeric', 'min:0'],
            'overtime_multiplier'=> ['required', 'numeric', 'min:1'],
            'allowed_ips'        => ['nullable', 'string'],
        ]);

        Setting::current()->update([
            'company_name'    => $validated['company_name'],
            'timezone'        => $validated['timezone'],
            'date_format'     => $validated['date_format'],
            'currency'        => $validated['currency'],
            'allowed_ips'     => $validated['allowed_ips'] ?? null,
            'overtime_config' => [
                'daily_threshold_hours' => $validated['overtime_threshold'],
                'multiplier'            => $validated['overtime_multiplier'],
            ],
        ]);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings saved.');
    }

    public function toggleModule(Request $request, string $module): RedirectResponse
    {
        $mod = Module::where('key', $module)->firstOrFail();
        $mod->toggle();

        $state = $mod->is_enabled ? 'enabled' : 'disabled';
        return back()->with('success', "Module '{$mod->label}' {$state}.");
    }
}
