<?php

namespace App\Http\Middleware;

use App\Models\Module;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleEnabled
{
    public function handle(Request $request, Closure $next, string $moduleKey): Response
    {
        if (! Module::isEnabled($moduleKey)) {
            return $request->expectsJson()
                ? response()->json(['error' => "The '{$moduleKey}' module is not enabled."], 403)
                : abort(403, "The '{$moduleKey}' module is not enabled for this installation.");
        }

        return $next($request);
    }
}
