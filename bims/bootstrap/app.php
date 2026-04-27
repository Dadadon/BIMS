<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckModuleEnabled;
use App\Http\Middleware\EnforceRole;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'module'      => CheckModuleEnabled::class,
            'role'        => EnforceRole::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SetAppTimezone::class,
            \App\Http\Middleware\RequireAppSetup::class,
        ]);
    })
    ->withEvents(discover: [
        __DIR__.'/../app/Listeners',
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
