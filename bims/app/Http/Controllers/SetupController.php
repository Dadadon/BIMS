<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SetupController extends Controller
{
    /** Available country / region onboarding packs. */
    private const PACKS = [
        'none'    => ['label' => 'None — I\'ll configure taxes manually',  'seeder' => null],
        'jamaica' => ['label' => 'Jamaica (NIS, NHT, Education Tax, PAYE)', 'seeder' => 'JamaicaCallCenterSeeder'],
    ];

    public function index(): View
    {
        return view('setup.index', ['packs' => self::PACKS]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name'  => ['required', 'string', 'max:150'],
            'currency'      => ['required', 'string', 'max:10'],
            'timezone'      => ['required', 'timezone'],
            'logo'          => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:1024'],
            'country_pack'  => ['required', 'in:' . implode(',', array_keys(self::PACKS))],
            'admin_name'    => ['required', 'string', 'max:100'],
            'admin_email'   => ['required', 'email', 'max:150'],
            'admin_password'=> ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Run all pending migrations (base reference data is embedded in the last migration)
        $exitCode = Artisan::call('migrate', ['--force' => true]);
        if ($exitCode !== 0) {
            return back()->withInput()->with('error', 'Migration failed: ' . Artisan::output());
        }

        // Run the country / industry pack seeder directly (tables now exist)
        $seederClass = self::PACKS[$validated['country_pack']]['seeder'] ?? null;
        if ($seederClass) {
            try {
                $fullClass = "Database\\Seeders\\{$seederClass}";
                (new $fullClass())->run();
            } catch (\Throwable $e) {
                return back()->withInput()->with('error', 'Country pack failed: ' . $e->getMessage());
            }
        }

        // 4. Save company settings
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        Setting::updateOrCreate([], [
            'company_name' => $validated['company_name'],
            'currency'     => $validated['currency'],
            'timezone'     => $validated['timezone'],
            'logo_path'    => $logoPath,
        ]);

        // 5. Create the first admin user (overwrite the seeded placeholder if email matches)
        $adminRole = Role::where('slug', 'system_admin')->first();

        User::updateOrCreate(
            ['email' => $validated['admin_email']],
            [
                'role_id'           => $adminRole?->id,
                'name'              => $validated['admin_name'],
                'password'          => Hash::make($validated['admin_password']),
                'email_verified_at' => now(),
                'acc_type'          => 'admin',
                'status'            => true,
            ]
        );

        return redirect()->route('login')
            ->with('success', 'Setup complete — please sign in.');
    }
}
