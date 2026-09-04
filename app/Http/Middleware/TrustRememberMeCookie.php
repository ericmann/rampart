<?php

namespace App\Http\Middleware;

/**
 * A07:2025 — Authentication Failures. This used to be an active middleware that trusted a
 * plain `base64(user_id)` cookie as proof of identity — no signature, no server-side
 * secret, so anyone could forge a login for any user id by hand. It has been fully removed
 * from the request pipeline (see bootstrap/app.php) in favor of Laravel's built-in signed
 * remember-me recaller: `Auth::attempt($credentials, $remember)` in LoginRequest now
 * issues an encrypted, signed cookie tied to a random `remember_token` stored on the user,
 * which the framework's own session guard verifies on every request — no hand-rolled
 * cookie-trusting code left to get wrong.
 *
 * The class (and this constant) stay only so the cookie name remains documented and the
 * historical "forged remember-me cookie" exploit test still runs and demonstrates that a
 * forged cookie of this shape is now inert, rather than erroring on a missing class.
 */
class TrustRememberMeCookie
{
    public const COOKIE_NAME = 'remember_me';
}
