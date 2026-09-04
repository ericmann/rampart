<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\Webhook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookReceiverTest extends TestCase
{
    use RefreshDatabase;

    public function test_inbound_webhook_closes_a_ticket(): void
    {
        $webhook = Webhook::factory()->create(['is_active' => true]);
        $ticket = Ticket::factory()->create(['status' => 'open']);

        $payload = ['event' => 'ticket.close', 'ticket_id' => $ticket->id];

        // A08:2025 fix — the receiver now verifies an HMAC-SHA256 signature of the raw
        // body against the webhook's secret before acting on it, so a legitimate caller
        // has to sign its payload the same way.
        $response = $this->postJson("/webhooks/inbound/{$webhook->inbound_token}", $payload, [
            'X-Rampart-Signature' => hash_hmac('sha256', json_encode($payload), $webhook->secret),
        ]);

        $response->assertOk()->assertJson(['result' => 'ticket_closed']);
        $this->assertSame('closed', $ticket->fresh()->status);
    }

    public function test_inbound_webhook_without_a_valid_signature_is_rejected(): void
    {
        $webhook = Webhook::factory()->create(['is_active' => true]);
        $ticket = Ticket::factory()->create(['status' => 'open']);

        $response = $this->postJson("/webhooks/inbound/{$webhook->inbound_token}", [
            'event' => 'ticket.close',
            'ticket_id' => $ticket->id,
        ]);

        $response->assertStatus(401);
        $this->assertSame('open', $ticket->fresh()->status);
    }

    public function test_unknown_token_returns_not_found(): void
    {
        $response = $this->postJson('/webhooks/inbound/does-not-exist', [
            'event' => 'ticket.close',
            'ticket_id' => 1,
        ]);

        $response->assertNotFound();
    }
}
