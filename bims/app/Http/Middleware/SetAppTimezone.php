<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetAppTimezone
{
    public function handle(Request $request, Closure $next): Response
    {
        // Cache the setting to avoid a DB hit on every request.
        // Gracefully fall back to UTC if the settings table doesn't exist yet (pre-setup).
        $timezone = cache()->remember('app.timezone', 3600, function () {
            try {
                return Setting::value('timezone') ?? 'UTC';
            } catch (\Exception) {
                return 'UTC';
            }
        });

        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);

        return $next($request);
    }
}
