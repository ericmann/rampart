<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\LogFullRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Inbound webhooks are called by external services that have no session and no
        // CSRF token, so this receiver is exempt from CSRF verification (as any webhook
        // endpoint must be).
        $middleware->validateCsrfTokens(except: ['webhooks/inbound/*']);

        $middleware->web(append: [
            LogFullRequests::class,
        ]);

        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // A02:2025 — Security Misconfiguration. main rendered a custom "errors.debug" view
        // here whenever app.debug was on, dumping the exception plus a table of live
        // config values (DB_PASSWORD, APP_KEY, ...) straight into the response body — a
        // debug page, custom or framework, must never render secrets. That view is gone;
        // there's no override left here at all, so an uncaught exception falls through to
        // Laravel's own handling, which is debug-aware (shows a trace when app.debug is
        // on, a plain 500 when it's off) but was never wired to print config/env values.
    })->create();
