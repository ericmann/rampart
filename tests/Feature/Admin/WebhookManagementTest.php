<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Webhook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_webhook(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/webhooks', [
            'name' => 'Slack alerts',
            'target_url' => 'https://hooks.example.com/rampart',
            'event' => 'ticket.created',
        ]);

        $response->assertRedirect(route('admin.webhooks.index'));
        $this->assertDatabaseHas('webhooks', ['name' => 'Slack alerts']);
    }

    public function test_admin_can_deactivate_a_webhook(): void
    {
        $admin = User::factory()->admin()->create();
        $webhook = Webhook::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->put("/admin/webhooks/{$webhook->id}", [
            'name' => $webhook->name,
            'target_url' => $webhook->target_url,
            'event' => $webhook->event,
            'is_active' => false,
        ]);

        $response->assertRedirect(route('admin.webhooks.index'));
        $this->assertFalse($webhook->fresh()->is_active);
    }

    public function test_non_admin_cannot_manage_webhooks(): void
    {
        $agent = User::factory()->agent()->create();

        $response = $this->actingAs($agent)->get('/admin/webhooks');

        $response->assertForbidden();
    }
}
