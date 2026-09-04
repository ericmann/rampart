<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    /**
     * A01:2025 — Broken Access Control (mass assignment). The old implementation passed
     * the entire raw request body straight to `update()`, so any extra field a caller
     * included — `role`, `organization_id` — was written to the model as-is; a customer
     * could self-promote to admin by adding `role=admin` to this form post. `User` keeps
     * `role`/`organization_id` in its fillable list on purpose, because the *admin*
     * user-management screen (Admin\UserController@update) legitimately updates them —
     * the bug was never the model's fillable list, it was this action trusting unvalidated
     * input for a self-service form that should only ever touch name/email. A FormRequest
     * with an explicit allowlist of validated fields closes that off without narrowing
     * what an admin is allowed to do elsewhere.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated())->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
