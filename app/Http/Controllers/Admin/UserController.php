<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
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

        $previousRole = $user->role;

        $user->update($validated);

        // A09:2025 — Security Logging & Alerting Failures. A privilege change — the
        // single most security-relevant admin action in this app — left no record at all.
        // Only log it when the role actually moved, so routine name/email edits don't
        // flood the audit log with noise.
        if ($previousRole !== $user->role) {
            AuditLog::create([
                'user_id' => $request->user()->id,
                'event' => 'user.role_changed',
                'context' => ['target_user_id' => $user->id, 'from' => $previousRole, 'to' => $user->role],
                'ip_address' => $request->ip(),
            ]);
        }

        return redirect()->route('admin.users.index')->with('status', 'User updated.');
    }
}
