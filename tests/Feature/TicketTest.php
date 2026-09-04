<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_file_a_ticket(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->post('/tickets', [
            'subject' => 'My printer is on fire',
            'body' => 'Smoke is coming out of the top.',
            'priority' => 'urgent',
        ]);

        $ticket = Ticket::firstOrFail();
        $response->assertRedirect(route('tickets.show', $ticket));
        $this->assertSame($customer->id, $ticket->requester_id);
        $this->assertSame('open', $ticket->status);
    }

    public function test_customer_sees_only_their_own_tickets_in_the_index(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $ownTicket = Ticket::factory()->forRequester($customer)->create(['subject' => 'Mine']);
        $otherTicket = Ticket::factory()->create(['subject' => 'Not mine']);

        $response = $this->actingAs($customer)->get('/tickets');

        $response->assertOk();
        $response->assertSee('Mine');
        $response->assertDontSee('Not mine');
    }

    public function test_agent_sees_every_ticket_in_the_index(): void
    {
        $agent = User::factory()->agent()->create();
        Ticket::factory()->create(['subject' => 'Customer A issue']);
        Ticket::factory()->create(['subject' => 'Customer B issue']);

        $response = $this->actingAs($agent)->get('/tickets');

        $response->assertOk();
        $response->assertSee('Customer A issue');
        $response->assertSee('Customer B issue');
    }

    public function test_search_returns_matching_tickets(): void
    {
        $agent = User::factory()->agent()->create();
        Ticket::factory()->create(['subject' => 'Billing question about invoice']);
        Ticket::factory()->create(['subject' => 'Completely unrelated topic']);

        $response = $this->actingAs($agent)->get('/tickets?q=Billing');

        $response->assertOk();
        $response->assertSee('Billing question about invoice');
        $response->assertDontSee('Completely unrelated topic');
    }

    public function test_agent_can_reply_to_a_ticket(): void
    {
        $agent = User::factory()->agent()->create();
        $ticket = Ticket::factory()->assignedTo($agent)->create();

        $response = $this->actingAs($agent)->post("/tickets/{$ticket->id}/messages", [
            'body' => 'We are looking into this.',
        ]);

        $response->assertRedirect(route('tickets.show', $ticket));
        $this->assertDatabaseHas('messages', [
            'ticket_id' => $ticket->id,
            'author_id' => $agent->id,
        ]);
    }

    public function test_staff_can_update_ticket_status(): void
    {
        $agent = User::factory()->agent()->create();
        $ticket = Ticket::factory()->create(['status' => 'open']);

        $response = $this->actingAs($agent)->patch("/tickets/{$ticket->id}/status", [
            'status' => 'resolved',
        ]);

        $response->assertRedirect();
        $this->assertSame('resolved', $ticket->fresh()->status);
    }

    public function test_customer_cannot_update_ticket_status(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $ticket = Ticket::factory()->forRequester($customer)->create(['status' => 'open']);

        $response = $this->actingAs($customer)->patch("/tickets/{$ticket->id}/status", [
            'status' => 'resolved',
        ]);

        $response->assertForbidden();
        $this->assertSame('open', $ticket->fresh()->status);
    }

    public function test_ticket_attachment_can_be_uploaded_and_downloaded(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        $customer = User::factory()->create(['role' => 'customer']);
        $ticket = Ticket::factory()->forRequester($customer)->create();

        $file = \Illuminate\Http\UploadedFile::fake()->create('screenshot.png', 50);

        $response = $this->actingAs($customer)->post("/tickets/{$ticket->id}/attachments", [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attachments', ['ticket_id' => $ticket->id, 'original_name' => 'screenshot.png']);

        $attachment = $ticket->attachments()->firstOrFail();
        $download = $this->actingAs($customer)->get("/attachments/{$attachment->id}/download");
        $download->assertOk();
    }
}
