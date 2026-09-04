<?php

namespace App\Support;

use Illuminate\Support\Facades\Redis;
use Throwable;

/**
 * Per-account login lockout, counters kept in Redis. After MAX_ATTEMPTS failures for the
 * same email within the window, further attempts are rejected.
 *
 * Two deliberate gaps here, documented in docs/VULN-MAP.md:
 *  - A06: keyed ONLY by email, so a password-spray attack (many accounts, one attacker)
 *    never trips it — an attacker just needs to avoid repeating the same account.
 *  - A10: no fallback when Redis itself is unreachable. The try/catch swallows the
 *    connection error and returns as if nothing happened, so a Redis outage silently
 *    disables the lockout instead of failing closed.
 */
class LoginThrottle
{
    private const MAX_ATTEMPTS = 10;

    private const WINDOW_SECONDS = 900;

    public function tooManyAttempts(string $email): bool
    {
        try {
            $attempts = (int) Redis::get($this->key($email));

            return $attempts >= self::MAX_ATTEMPTS;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function increment(string $email): void
    {
        try {
            $key = $this->key($email);
            $attempts = Redis::incr($key);

            if ($attempts === 1) {
                Redis::expire($key, self::WINDOW_SECONDS);
            }
        } catch (Throwable $e) {
            // Redis down — lockout silently skipped, brute force can resume. See A10.
        }
    }

    public function clear(string $email): void
    {
        try {
            Redis::del($this->key($email));
        } catch (Throwable $e) {
            //
        }
    }

    private function key(string $email): string
    {
        return 'login_attempts:'.strtolower($email);
    }
}
