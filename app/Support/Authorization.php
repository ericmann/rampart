<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Central authorization checkpoint used by controllers for decisions that don't go
 * through route-level policy resolution.
 *
 * A10:2025 — Mishandling of Exceptional Conditions. This used to catch any exception the
 * gate check threw (e.g. TicketPolicy::assign()'s findOrFail() on a bad agent id) and turn
 * it into an ALLOW — the exact failure mode the checklist calls out by name: a broad catch
 * around an authorization check is itself the smell. Default-deny instead: any exception
 * inside a security decision has to deny, never allow, and it's worth an alert since it
 * usually means either bad input got this far or the policy itself has a bug.
 */
class Authorization
{
    public static function allows(User $user, string $ability, mixed $arguments = []): bool
    {
        try {
            return Gate::forUser($user)->allows($ability, $arguments);
        } catch (Throwable $e) {
            Log::error('Authorization check threw — denying by default.', [
                'ability' => $ability,
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
