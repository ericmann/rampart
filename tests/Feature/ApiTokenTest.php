<?php

namespace Tests\Feature;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_an_api_token(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/api-tokens', ['name' => 'CI integration']);

        $response->assertRedirect(route('api-tokens.index'));
        $this->assertDatabaseHas('api_tokens', ['user_id' => $user->id, 'name' => 'CI integration']);
    }

    public function test_user_can_revoke_their_own_token(): void
    {
        $user = User::factory()->create();
        $token = ApiToken::factory()->for($user)->create();

        $response = $this->actingAs($user)->delete("/api-tokens/{$token->id}");

        $response->assertRedirect(route('api-tokens.index'));
        $this->assertDatabaseMissing('api_tokens', ['id' => $token->id]);
    }

    public function test_user_cannot_revoke_another_users_token(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $token = ApiToken::factory()->for($owner)->create();

        $response = $this->actingAs($intruder)->delete("/api-tokens/{$token->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('api_tokens', ['id' => $token->id]);
    }
}
