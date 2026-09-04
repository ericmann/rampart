<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Webhook;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WebhookReceiverController extends Controller
{
    /**
     * Inbound webhook receiver — looks up the webhook by its public token and applies
     * the event payload.
     *
     * A08:2025 — Software & Data Integrity Failures. The inbound token in the URL is only
     * ever a *routing* identifier (it's the public path segment, so it ends up in proxy
     * logs, browser history if ever pasted, etc.) — it was never a secret, yet it was the
     * only thing checked before acting on the payload. Anyone who found or guessed a
     * token could forge events (e.g. close any ticket) with a bare, unsigned POST. Each
     * webhook has its own `secret`, generated but never actually verified; the fix
     * requires and verifies an HMAC-SHA256 signature of the raw request body over that
     * secret before the payload is trusted, the same way Stripe/GitHub/etc. webhooks work.
     */
    public function handle(Request $request, string $token): JsonResponse
    {
        $webhook = Webhook::where('inbound_token', $token)->where('is_active', true)->firstOrFail();

        $signature = (string) $request->header('X-Rampart-Signature');
        $expected = hash_hmac('sha256', $request->getContent(), $webhook->secret);

        if ($signature === '' || ! hash_equals($expected, $signature)) {
            return response()->json(['message' => 'Invalid or missing webhook signature.'], 401);
        }

        $payload = $request->all();

        $result = 'ignored';

        if (($payload['event'] ?? null) === 'ticket.close' && isset($payload['ticket_id'])) {
            $ticket = Ticket::find($payload['ticket_id']);

            if ($ticket) {
                $ticket->update(['status' => 'closed']);
                $result = 'ticket_closed';
            }
        }

        $webhook->deliveries()->create([
            'direction' => 'inbound',
            'payload' => $payload,
            'result' => $result,
        ]);

        return response()->json(['result' => $result]);
    }
}
