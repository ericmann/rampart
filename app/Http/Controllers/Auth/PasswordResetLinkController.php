<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetLinkMail;
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

        $resetUrl = route('password.reset', ['token' => $token, 'email' => $request->email]);

        Mail::to($user->email)->send(new PasswordResetLinkMail($resetUrl));

        return back()->with('status', 'We have emailed your password reset link!');
    }
}
