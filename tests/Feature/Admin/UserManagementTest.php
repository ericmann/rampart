<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_promote_a_user_to_agent(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($admin)->put("/admin/users/{$customer->id}", [
            'name' => $customer->name,
            'email' => $customer->email,
            'role' => 'agent',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertSame('agent', $customer->fresh()->role);
    }

    public function test_non_admin_cannot_reach_the_admin_area(): void
    {
        $agent = User::factory()->agent()->create();

        $response = $this->actingAs($agent)->get('/admin/users');

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/admin/users');

        $response->assertRedirect('/login');
    }
}
