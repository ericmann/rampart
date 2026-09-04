<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\SavedView>
 */
class SavedViewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(2, true),
            'preferences' => serialize(['status' => 'open', 'priority' => null]),
        ];
    }
}
