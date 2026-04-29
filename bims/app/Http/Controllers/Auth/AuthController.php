<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Services\Auth\JitProvisioningService;
use App\Services\Auth\LdapAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $email  = $credentials['email'];
        $domain = strtolower(substr($email, strrpos($email, '@') + 1));

        // Determine the external provider for this email domain (if any).
        // system_admin always uses local auth regardless of domain mapping.
        $candidate = User::where('email', $email)->first();
        $provider  = null;

        if (! $candidate?->isSuperAdmin()) {
            try {
                $domainMap = Setting::current()->external_auth_domains ?? [];
                $provider  = $domainMap[$domain] ?? null;
            } catch (\Throwable) {
                $provider = null;
            }
        }

        // ── OIDC domains: redirect to IdP (password field is ignored) ──────
        if ($provider === 'oidc') {
            return redirect()->route('auth.oidc.redirect');
        }

        // ── LDAP domains: bind against AD/Samba ─────────────────────────────
        if ($provider === 'ldap') {
            $result = app(LdapAuthService::class)->attempt($email, $credentials['password']);

            if (is_array($result)) {
                $user = app(JitProvisioningService::class)->findOrProvision(
                    array_merge($result, [
                        'provider'    => 'ldap',
                        'provider_id' => $result['dn'],
                    ])
                );

                Auth::login($user, $request->boolean('remember'));
                $request->session()->regenerate();

                return $user->isAdmin()
                    ? redirect()->intended(route('admin.dashboard'))
                    : redirect()->intended(route('my.dashboard'));
            }

            if ($result === false) {
                return back()
                    ->withErrors(['email' => 'These credentials do not match our records.'])
                    ->onlyInput('email');
            }

            // null = LDAP unreachable — fall through to local auth as a safety net
        }

        // ── Local / fallback authentication ─────────────────────────────────
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return Auth::user()->isAdmin()
                ? redirect()->intended(route('admin.dashboard'))
                : redirect()->intended(route('my.dashboard'));
        }

        return back()
            ->withErrors(['email' => 'These credentials do not match our records.'])
            ->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showForgot(): View
    {
        return view('auth.forgot-password');
    }

    public function sendReset(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function showReset(string $token): View
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
