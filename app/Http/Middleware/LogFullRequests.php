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
    /**
     * A09:2025 — Security Logging & Alerting Failures. This wrote every request body to
     * the application log verbatim — login passwords, password-reset submissions, API
     * token names alongside the tokens created moments later, all in cleartext in a place
     * with much broader read access than the database itself (log aggregators, shipped
     * files, anyone who can `tail` on the box). Redact secrets from logs by policy, not by
     * hoping nobody logs one by accident.
     */
    private const REDACTED_FIELDS = [
        'password', 'password_confirmation', 'current_password',
        'token', 'secret', 'api_key',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        Log::info('request', [
            'method' => $request->method(),
            'path' => $request->path(),
            'body' => $this->redact($request->all()),
        ]);

        return $next($request);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    private function redact(array $body): array
    {
        foreach ($body as $key => $value) {
            $body[$key] = in_array(strtolower((string) $key), self::REDACTED_FIELDS, true)
                ? '[REDACTED]'
                : $value;
        }

        return $body;
    }
}
