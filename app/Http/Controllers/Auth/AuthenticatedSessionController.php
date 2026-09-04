<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Middleware\TrustRememberMeCookie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Deliberately does NOT call $request->session()->regenerate() after login, leaving
     * the app open to session fixation — an attacker who fixes a victim's session id
     * before login keeps a valid, authenticated session id after they log in. See
     * docs/VULN-MAP.md (A07).
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $response = redirect()->intended(route('dashboard', absolute: false));

        if ($request->boolean('remember')) {
            $response = TrustRememberMeCookie::issue($response, $request->user());
        }

        return $response;
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $response = redirect('/');
        $response->headers->clearCookie(TrustRememberMeCookie::COOKIE_NAME);

        return $response;
    }
}
