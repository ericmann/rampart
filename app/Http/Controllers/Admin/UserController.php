<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Admin user-management screens — lets an admin edit another user's role and organization.
 */
class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with('organization')->orderBy('name')->paginate(30);

        return view('admin.users.index', ['users' => $users]);
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', ['user' => $user, 'organizations' => Organization::orderBy('name')->get()]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'in:customer,agent,admin'],
            'organization_id' => ['nullable', 'exists:organizations,id'],
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('status', 'User updated.');
    }
}
