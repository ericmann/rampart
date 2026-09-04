<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Throwable;

/**
 * Login lockout, counters kept in Redis.
 *
 * A06:2025 — Insecure Design. main only ever throttled per account, so a password-spray
 * attack — one guess against each of thousands of different accounts — never tripped
 * anything: no single account ever accumulated enough failures to hit its own limit. Real
 * lockout needs more than one dimension; this now also tracks per-IP and a global ceiling,
 * so a spray from one source gets caught even though no individual account does.
 *
 * A10:2025 — Mishandling of Exceptional Conditions. main's try/catch around every Redis
 * call swallowed a connection failure into "no attempts recorded" — silently disabling the
 * lockout entirely for as long as Redis stayed down, with nothing logged. tooManyAttempts()
 * now fails CLOSED (denies the attempt) and logs a critical alert when it can't reach
 * Redis, per the checklist: a security control's dependency going down should never be a
 * silent no-op.
 */
class LoginThrottle
{
    private const MAX_ATTEMPTS_PER_ACCOUNT = 10;

    private const MAX_ATTEMPTS_PER_IP = 15;

    private const MAX_ATTEMPTS_GLOBAL = 200;

    private const WINDOW_SECONDS = 900;

    public function tooManyAttempts(string $email, string $ip): bool
    {
        try {
            return $this->count($this->accountKey($email)) >= self::MAX_ATTEMPTS_PER_ACCOUNT
                || $this->count($this->ipKey($ip)) >= self::MAX_ATTEMPTS_PER_IP
                || $this->count($this->globalKey()) >= self::MAX_ATTEMPTS_GLOBAL;
        } catch (Throwable $e) {
            Log::critical('LoginThrottle cannot reach Redis — failing closed, denying login attempts until it recovers.', [
                'exception' => $e->getMessage(),
            ]);

            return true;
        }
    }

    public function increment(string $email, string $ip): void
    {
        try {
            $this->bump($this->accountKey($email));
            $this->bump($this->ipKey($ip));
            $this->bump($this->globalKey());
        } catch (Throwable $e) {
            Log::critical('LoginThrottle cannot reach Redis — this failed attempt was not counted.', [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public function clear(string $email): void
    {
        try {
            // Only the account-level counter resets on a success. A spray attacker
            // getting exactly one of many guessed accounts right shouldn't reopen the
            // IP/global budget they've already spent attacking every other account.
            Redis::del($this->accountKey($email));
        } catch (Throwable $e) {
            //
        }
    }

    private function bump(string $key): void
    {
        $attempts = Redis::incr($key);

        if ($attempts === 1) {
            Redis::expire($key, self::WINDOW_SECONDS);
        }
    }

    private function count(string $key): int
    {
        return (int) Redis::get($key);
    }

    private function accountKey(string $email): string
    {
        return 'login_attempts:account:'.strtolower($email);
    }

    private function ipKey(string $ip): string
    {
        return 'login_attempts:ip:'.$ip;
    }

    private function globalKey(): string
    {
        return 'login_attempts:global';
    }
}
