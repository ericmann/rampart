<?php

namespace Tests\Feature\Auth;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_registered_users_join_the_organization_for_their_email_domain(): void
    {
        $organization = Organization::factory()->create(['domain' => 'globex.example']);

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@globex.example',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'test@globex.example')->firstOrFail();
        $this->assertSame($organization->id, $user->organization_id);
        $this->assertSame(User::ROLE_CUSTOMER, $user->role);
    }

    public function test_registered_users_with_an_unknown_domain_get_a_new_organization(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->assertNotNull($user->organization_id);
        $this->assertSame('example.com', $user->organization->domain);
    }

    public function test_registered_users_can_file_a_ticket(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response = $this->post('/tickets', [
            'subject' => 'Testing',
            'body' => 'Howdy',
            'priority' => 'low',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', ['subject' => 'Testing']);
    }
}
