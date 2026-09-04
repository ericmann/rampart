<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $row = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        // A07:2025 — Authentication Failures. The old check verified the token but never
        // its age or whether it had already been used, so a token leaked once (a shared
        // inbox, a proxy log, a referrer header) stayed valid forever and could be reused
        // any number of times. Real reset tokens need a short TTL and single use:
        // `expire` mirrors config/auth.php's own `passwords.users.expire` (60 minutes,
        // Laravel's standard default) so this matches the framework's own Password broker
        // even though the token storage here stays hand-rolled.
        $expiresAt = $row?->created_at
            ? \Illuminate\Support\Carbon::parse($row->created_at)->addMinutes(config('auth.passwords.users.expire'))
            : null;

        if (! $row || ! hash_equals($row->token, (string) $request->token) || ! $expiresAt || $expiresAt->isPast()) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'This password reset token is invalid.']);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $user->forceFill(['password' => $request->password])->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', 'Your password has been reset!');
    }
}
