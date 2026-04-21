<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceRole
{
    /**
     * Usage: ->middleware('role:manager,system_admin')
     * Accepts a comma-separated list of allowed role slugs.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role?->slug, $roles, true)) {
            return $request->expectsJson()
                ? response()->json(['error' => 'Insufficient role.'], 403)
                : abort(403, 'You do not have the required role to access this page.');
        }

        return $next($request);
    }
}
