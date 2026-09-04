<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver
    |--------------------------------------------------------------------------
    |
    | NOTE: the default driver here is a custom "md5" hasher registered in
    | AppServiceProvider, NOT a supported Laravel driver. Every Hash::make()/
    | Hash::check() call in the app (login, registration, password reset,
    | the `hashed` Eloquent cast) goes through it. See docs/VULN-MAP.md (A04).
    |
    */

    'driver' => env('HASH_DRIVER', 'md5'),

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
