<?php

namespace App\Extensions;

use Illuminate\Contracts\Hashing\Hasher as HasherContract;

/**
 * Intentionally-broken password hasher — plain, unsalted md5(). Registered as the app's
 * default Hash driver in AppServiceProvider so every Hash::make()/Hash::check() call
 * (login, registration, password reset, the `hashed` Eloquent cast) uses it transparently.
 *
 * See docs/VULN-MAP.md (A04). Never copy this into a real application.
 */
class Md5Hasher implements HasherContract
{
    public function info($hashedValue): array
    {
        return ['algo' => 'md5', 'algoName' => 'md5', 'options' => []];
    }

    public function make($value, array $options = []): string
    {
        return md5($value);
    }

    public function check($value, $hashedValue, array $options = []): bool
    {
        if ($hashedValue === null || $hashedValue === '') {
            return false;
        }

        return hash_equals($hashedValue, md5($value));
    }

    public function needsRehash($hashedValue, array $options = []): bool
    {
        return false;
    }

    public function isHashed($value): bool
    {
        return is_string($value) && preg_match('/^[a-f0-9]{32}$/', $value) === 1;
    }
}
