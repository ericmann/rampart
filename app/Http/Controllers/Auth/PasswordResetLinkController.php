<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Hand-rolled reset-link flow (bypasses Laravel's Password broker entirely). Two
     * deliberate flaws, documented in docs/VULN-MAP.md:
     *  - A06: the response differs for a known vs. unknown email — user enumeration.
     *  - A04/A07: the token is md5($email.time()), stored in plaintext with no expiry
     *    and no single-use invalidation (see NewPasswordController).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => "We can't find a user with that email address."]);
        }

        $token = md5($request->email.time());

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => now()]
        );

        Mail::raw(
            "Reset your Rampart password: ".route('password.reset', ['token' => $token, 'email' => $request->email]),
            fn ($message) => $message->to($user->email)->subject('Reset your Rampart password')
        );

        return back()->with('status', 'We have emailed your password reset link!');
    }
}
