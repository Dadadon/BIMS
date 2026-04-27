<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAppSetup
{
    public function handle(Request $request, Closure $next): Response
    {
        $isSetupRoute = $request->routeIs('setup.*');
        $isComplete   = $this->isComplete();

        // Not set up yet — send everything to the wizard (except the wizard itself)
        if (! $isComplete && ! $isSetupRoute) {
            return redirect()->route('setup.index');
        }

        // Already set up — block the wizard so it can't be re-run
        if ($isComplete && $isSetupRoute) {
            return redirect('/');
        }

        return $next($request);
    }

    private function isComplete(): bool
    {
        try {
            return \App\Models\Setting::exists()
                && \App\Models\User::where('acc_type', 'admin')->exists();
        } catch (\Exception) {
            // DB not reachable or migrations not run yet
            return false;
        }
    }
}
