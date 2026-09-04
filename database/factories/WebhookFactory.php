<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Webhook>
 */
class WebhookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'target_url' => fake()->url(),
            'event' => 'ticket.closed',
            'inbound_token' => Str::random(24),
            'secret' => Str::random(40),
            'is_active' => true,
        ];
    }
}
