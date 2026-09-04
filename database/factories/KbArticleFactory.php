<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\KbArticle>
 */
class KbArticleFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'author_id' => User::factory()->agent(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'body' => '<p>'.fake()->paragraph().'</p>',
            'is_published' => true,
        ];
    }
}
