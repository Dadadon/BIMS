<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'logo'               => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:1024'],
        ]);

        $settings = Setting::current();

        $data = [
            'company_name'    => $validated['company_name'],
            'timezone'        => $validated['timezone'],
            'date_format'     => $validated['date_format'],
            'currency'        => $validated['currency'],
            'allowed_ips'     => $validated['allowed_ips'] ?? null,
            'overtime_config' => [
                'daily_threshold_hours' => $validated['overtime_threshold'],
                'multiplier'            => $validated['overtime_multiplier'],
            ],
        ];

        if ($request->hasFile('logo')) {
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        if ($request->boolean('remove_logo') && $settings->logo_path) {
            Storage::disk('public')->delete($settings->logo_path);
            $data['logo_path'] = null;
        }

        $settings->update($data);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings saved.');
    }

    public function updateAuth(Request $request): RedirectResponse
    {
        $raw = trim($request->input('external_auth_domains', ''));

        if ($raw === '' || $raw === '{}') {
            Setting::current()->update(['external_auth_domains' => null]);

            return redirect()->route('admin.settings.index')
                ->with('success', 'Authentication settings saved.');
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            return back()
                ->withErrors(['external_auth_domains' => 'Invalid JSON. Please check the format.'])
                ->withInput();
        }

        $allowed = ['oidc', 'ldap'];
        foreach ($decoded as $domain => $provider) {
            if (! in_array($provider, $allowed, true)) {
                return back()
                    ->withErrors(['external_auth_domains' => "Unknown provider \"{$provider}\" for domain \"{$domain}\". Use \"oidc\" or \"ldap\"."])
                    ->withInput();
            }
        }

        Setting::current()->update(['external_auth_domains' => $decoded]);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Authentication settings saved.');
    }

    public function toggleModule(Request $request, string $module): RedirectResponse
    {
        $mod = Module::where('key', $module)->firstOrFail();
        $mod->toggle();

        $state = $mod->is_enabled ? 'enabled' : 'disabled';
        return back()->with('success', "Module '{$mod->label}' {$state}.");
    }
}
