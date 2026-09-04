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

        if (! $row || ! hash_equals($row->token, (string) $request->token)) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'This password reset token is invalid.']);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $user->forceFill(['password' => $request->password])->save();

        return redirect()->route('login')->with('status', 'Your password has been reset!');
    }
}
