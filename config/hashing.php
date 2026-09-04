<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver
    |--------------------------------------------------------------------------
    |
    | A04:2025 — Cryptographic Failures. main registered a custom "md5" driver here
    | (see AppServiceProvider) so every Hash::make()/Hash::check() call — login,
    | registration, password reset, the `hashed` Eloquent cast — silently used a fast,
    | unsalted, long-broken-for-passwords digest. That extension is gone; this is back to
    | Laravel's real default, bcrypt.
    |
    */

    'driver' => env('HASH_DRIVER', 'bcrypt'),

    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 12),
        'verify' => true,
    ],

    'argon' => [
        'memory' => 65536,
        'threads' => 1,
        'time' => 4,
        'verify' => true,
    ],

];
