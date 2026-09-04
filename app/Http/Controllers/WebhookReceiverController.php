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
     */
    public function handle(Request $request, string $token): JsonResponse
    {
        $webhook = Webhook::where('inbound_token', $token)->where('is_active', true)->firstOrFail();

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
