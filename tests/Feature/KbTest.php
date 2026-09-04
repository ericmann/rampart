<?php

namespace Tests\Feature;

use App\Models\KbArticle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KbTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_browse_published_articles(): void
    {
        KbArticle::factory()->create(['title' => 'How to reset your password', 'is_published' => true]);

        $response = $this->get('/kb');

        $response->assertOk();
        $response->assertSee('How to reset your password');
    }

    public function test_guests_cannot_see_unpublished_articles_on_the_index(): void
    {
        KbArticle::factory()->create(['title' => 'Draft article', 'is_published' => false]);

        $response = $this->get('/kb');

        $response->assertOk();
        $response->assertDontSee('Draft article');
    }

    public function test_agent_can_publish_an_article(): void
    {
        $agent = User::factory()->agent()->create();

        $response = $this->actingAs($agent)->post('/kb', [
            'title' => 'New troubleshooting guide',
            'body' => '<p>Steps to follow.</p>',
        ]);

        $article = KbArticle::firstOrFail();
        $response->assertRedirect(route('kb.show', $article));
        $this->assertSame($agent->id, $article->author_id);
    }

    public function test_customer_cannot_reach_the_article_composer(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->get('/kb/create');

        $response->assertForbidden();
    }
}
