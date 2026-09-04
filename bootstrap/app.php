<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\LogFullRequests;
use App\Http\Middleware\TrustRememberMeCookie;
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
        // The remember-me cookie is plain base64, not an encrypted Laravel cookie — it
        // has to be excluded here or EncryptCookies would reject it before
        // TrustRememberMeCookie ever sees it.
        $middleware->encryptCookies(except: [TrustRememberMeCookie::COOKIE_NAME]);

        $middleware->web(append: [
            LogFullRequests::class,
            TrustRememberMeCookie::class,
        ]);

        // Laravel's middleware priority sorting can otherwise reorder the route-level
        // `auth` check ahead of anything merely appended to the 'web' group, which would
        // make the forged remember-me cookie never get a chance to log the user in before
        // the auth check runs. Force the ordering explicitly.
        $middleware->prependToPriorityList(
            before: \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            prepend: TrustRememberMeCookie::class,
        );

        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Only intercepts genuine server errors; validation/auth/HTTP/not-found exceptions
        // keep Laravel's normal handling so the rest of the app behaves correctly.
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! config('app.debug') || $request->expectsJson()) {
                return null;
            }

            if ($e instanceof \Illuminate\Validation\ValidationException
                || $e instanceof \Illuminate\Auth\AuthenticationException
                || $e instanceof \Illuminate\Auth\Access\AuthorizationException
                || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
                || $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return null;
            }

            return response()->view('errors.debug', ['exception' => $e], 500);
        });
    })->create();
