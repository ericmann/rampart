<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Throwable;

/**
 * Central authorization checkpoint used by controllers for decisions that don't go
 * through route-level policy resolution. Wraps the gate check so a hiccup resolving a
 * related model doesn't take down the whole request.
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
