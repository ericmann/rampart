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
     * The token is stored in cleartext in `api_tokens.token` — not hashed at rest like
     * Sanctum does it. See docs/VULN-MAP.md (A04).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $token = $request->user()->apiTokens()->create([
            'name' => $validated['name'],
            'token' => Str::random(40),
        ]);

        return redirect()->route('api-tokens.index')->with('newToken', $token->token);
    }

    public function destroy(Request $request, ApiToken $apiToken): RedirectResponse
    {
        Gate::authorize('delete', $apiToken);

        $apiToken->delete();

        return redirect()->route('api-tokens.index')->with('status', 'Token revoked.');
    }
}
