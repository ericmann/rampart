<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
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

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // A07:2025 — Authentication Failures (session fixation). A session id issued
        // before login must never still be valid after login: otherwise an attacker who
        // hands a victim a pre-chosen session id (e.g. via a link that sets the cookie)
        // can simply wait and reuse that same id themselves once the victim authenticates.
        // regenerate() issues a fresh id and migrates the session data to it, so the
        // pre-login id is dead the moment authentication succeeds.
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
