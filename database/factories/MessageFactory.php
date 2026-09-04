<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'author_id' => User::factory(),
            'body' => '<p>'.fake()->sentence().'</p>',
            'is_internal_note' => false,
        ];
    }

    public function internalNote(): static
    {
        return $this->state(fn () => ['is_internal_note' => true]);
    }
}
