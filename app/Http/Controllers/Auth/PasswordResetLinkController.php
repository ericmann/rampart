<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetLinkMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * A06:2025 — Insecure Design. main returned a different response depending on whether
     * the email matched a user — a validation error for "no such user", a success status
     * otherwise — which lets anyone enumerate registered emails through the password-reset
     * form alone, no credentials needed. The response is now identical either way; only
     * the *side effect* (issuing a token and sending mail) is conditional on a match.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            // A07:2025 — Authentication Failures. `md5(email + time())` is predictable:
            // the email is often public, and `time()` only has second resolution, so an
            // attacker requesting a reset in a tight window can brute-force or simply
            // compute the same token. A reset token is a bearer credential — it needs to
            // come from a CSPRNG, the same way a session id or an API key does.
            $token = Str::random(64);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                ['token' => $token, 'created_at' => now()]
            );

            $resetUrl = route('password.reset', ['token' => $token, 'email' => $request->email]);

            Mail::to($user->email)->send(new PasswordResetLinkMail($resetUrl));
        }

        return back()->with('status', 'We have emailed your password reset link!');
    }
}
