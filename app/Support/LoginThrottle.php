<?php

namespace App\Support;

use Illuminate\Support\Facades\Redis;
use Throwable;

/**
 * Per-account login lockout, counters kept in Redis. After MAX_ATTEMPTS failures for the
 * same email within the window, further attempts are rejected.
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
            //
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
