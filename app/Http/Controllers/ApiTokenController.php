<?php

namespace App\Http\Controllers;

use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApiTokenController extends Controller
{
    public function index(Request $request): View
    {
        return view('api-tokens.index', ['tokens' => $request->user()->apiTokens]);
    }

    /**
     * A04:2025 — Cryptographic Failures. The plain token used to be written straight to
     * the `token` column, so anyone who could read the database (a backup, a replica, an
     * injection elsewhere) recovered every live API token verbatim. Nothing in this app
     * ever authenticates a *request* against this table (there's no API surface consuming
     * it yet), so there's no verify-by-lookup step to preserve — only the storage needs to
     * change. The plaintext is shown to the owner exactly once, via the session flash
     * below, the same way GitHub/Stripe-style tokens work; only its hash is persisted.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $plainToken = Str::random(40);

        $request->user()->apiTokens()->create([
            'name' => $validated['name'],
            'token' => hash('sha256', $plainToken),
        ]);

        return redirect()->route('api-tokens.index')->with('newToken', $plainToken);
    }

    public function destroy(Request $request, ApiToken $apiToken): RedirectResponse
    {
        Gate::authorize('delete', $apiToken);

        $apiToken->delete();

        return redirect()->route('api-tokens.index')->with('status', 'Token revoked.');
    }
}
