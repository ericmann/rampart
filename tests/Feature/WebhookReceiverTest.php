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

        $response = $this->postJson("/webhooks/inbound/{$webhook->inbound_token}", [
            'event' => 'ticket.close',
            'ticket_id' => $ticket->id,
        ]);

        $response->assertOk()->assertJson(['result' => 'ticket_closed']);
        $this->assertSame('closed', $ticket->fresh()->status);
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
