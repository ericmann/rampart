<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logs the full request body — including plaintext `password`/`password_confirmation`
 * fields — to the default log channel (storage/logs/laravel.log) on every request. No
 * redaction. See docs/VULN-MAP.md (A09).
 */
class LogFullRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('request', [
            'method' => $request->method(),
            'path' => $request->path(),
            'body' => $request->all(),
        ]);

        return $next($request);
    }
}
