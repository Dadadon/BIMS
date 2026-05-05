<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\JitProvisioningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class OidcController extends Controller
{
    /** Redirect the user to the Microsoft Entra ID login page. */
    public function redirect(): RedirectResponse
    {
        if (! config('services.azure.client_id')) {
            abort(503, 'Microsoft SSO is not configured on this installation.');
        }

        return Socialite::driver('azure')->redirect();
    }

    /** Handle the callback from Microsoft after authentication. */
    public function callback(Request $request, JitProvisioningService $jit): RedirectResponse
    {
        try {
            $socialUser = Socialite::driver('azure')->user();
        } catch (\Throwable) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Microsoft login failed. Please try again.']);
        }

        $email = $socialUser->getEmail();

        if (! $email) {
            return redirect()->route('login')
                ->withErrors(['email' => 'No email address was returned by Microsoft.']);
        }

        // system_admin is forbidden from using SSO
        $existing = User::where('email', $email)->first();
        if ($existing?->isSuperAdmin()) {
            return redirect()->route('login')
                ->withErrors(['email' => 'This account must use local authentication.']);
        }

        // Groups may be present in the raw token claims if the app registration
        // has "groupMembershipClaims" set to "SecurityGroup" or "All".
        $raw    = $socialUser->getRaw();
        $groups = $raw['groups'] ?? [];

        $user = $jit->findOrProvision([
            'name'          => $socialUser->getName() ?? $email,
            'email'         => $email,
            'auth_provider' => 'azure',
            'external_id'   => $socialUser->getId(),
            'groups'        => $groups,
            'department'    => $raw['department'] ?? null,
            'title'         => $raw['jobTitle'] ?? null,
        ]);

        Auth::login($user, true);
        $request->session()->regenerate();

        return $user->isAdmin()
            ? redirect()->intended(route('admin.dashboard'))
            : redirect()->intended(route('my.dashboard'));
    }
}
