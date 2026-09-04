<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logs each request's method, path, and payload for troubleshooting.
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
