<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use UnexpectedValueException;

/**
 * Signed, expiring "share this ticket's status" links — no login required to view. Uses
 * firebase/php-jwt (see composer.json / docs/VULN-MAP.md — A03 for why this dependency is
 * pinned where it is) for a real signed+expiring token, deliberately unlike the reset
 * token in app/Http/Controllers/Auth/NewPasswordController.php.
 */
class PublicTicketStatusController extends Controller
{
    private const ALGO = 'HS256';

    public function create(Ticket $ticket): RedirectResponse
    {
        Gate::authorize('view', $ticket);

        $token = JWT::encode([
            'ticket_id' => $ticket->id,
            'exp' => now()->addDays(7)->timestamp,
        ], config('app.key'), self::ALGO);

        return back()->with('shareUrl', route('tickets.public-status', ['token' => $token]));
    }

    public function show(string $token): View
    {
        try {
            $decoded = JWT::decode($token, new Key(config('app.key'), self::ALGO));
        } catch (ExpiredException) {
            abort(410, 'This share link has expired.');
        } catch (UnexpectedValueException) {
            abort(404);
        }

        $ticket = Ticket::findOrFail($decoded->ticket_id);

        return view('tickets.public-status', ['ticket' => $ticket]);
    }
}
