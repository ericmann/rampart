<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lightweight "remember me" cookie — stores the user id so returning visitors don't have
 * to log in again on the same browser.
 */
class TrustRememberMeCookie
{
    public const COOKIE_NAME = 'remember_me';

    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check() && $request->cookie(self::COOKIE_NAME)) {
            $userId = base64_decode($request->cookie(self::COOKIE_NAME), true);

            if ($userId !== false && ctype_digit((string) $userId)) {
                $user = User::find((int) $userId);

                if ($user) {
                    Auth::login($user);
                }
            }
        }

        return $next($request);
    }

    public static function issue(Response $response, User $user): Response
    {
        $response->headers->setCookie(
            \Symfony\Component\HttpFoundation\Cookie::create(self::COOKIE_NAME)
                ->withValue(base64_encode((string) $user->id))
                ->withExpires(now()->addYears(5))
                ->withHttpOnly(true)
                ->withPath('/')
        );

        return $response;
    }
}
