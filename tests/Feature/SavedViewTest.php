<?php

namespace Tests\Feature;

use App\Models\SavedView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavedViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_save_a_filter(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/saved-views', [
            'name' => 'My open tickets',
            'status' => 'open',
        ]);

        $response->assertRedirect(route('saved-views.index'));
        $this->assertDatabaseHas('saved_views', ['user_id' => $user->id, 'name' => 'My open tickets']);
    }

    public function test_user_can_view_their_saved_filter(): void
    {
        $user = User::factory()->create();
        $view = SavedView::factory()->for($user)->create([
            'preferences' => serialize(['status' => 'open', 'priority' => null]),
        ]);

        $response = $this->actingAs($user)->get("/saved-views/{$view->id}");

        $response->assertOk();
        $response->assertSee('open');
    }

    public function test_user_cannot_view_another_users_saved_filter(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $view = SavedView::factory()->for($owner)->create();

        $response = $this->actingAs($intruder)->get("/saved-views/{$view->id}");

        $response->assertForbidden();
    }
}
