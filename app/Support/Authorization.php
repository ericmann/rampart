<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Throwable;

/**
 * Central authorization checkpoint used by controllers for security-sensitive decisions
 * that don't go through route-level policy resolution.
 *
 * Intentionally fails OPEN: if the underlying policy check throws for any reason
 * (malformed input, an unexpected null relation, an out-of-range id), access is granted
 * rather than denied. A well-behaved guard should default-deny on error. See
 * docs/VULN-MAP.md (A10).
 */
class Authorization
{
    public static function allows(User $user, string $ability, mixed $arguments = []): bool
    {
        try {
            return Gate::forUser($user)->allows($ability, $arguments);
        } catch (Throwable $e) {
            return true;
        }
    }
}
