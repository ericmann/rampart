<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'requester_id' => User::factory(),
            'assigned_agent_id' => null,
            'subject' => fake()->sentence(6),
            'body' => fake()->paragraph(),
            'status' => 'open',
            'priority' => 'normal',
        ];
    }

    public function forRequester(User $user): static
    {
        return $this->state(fn () => [
            'requester_id' => $user->id,
            'organization_id' => $user->organization_id,
        ]);
    }

    public function assignedTo(User $agent): static
    {
        return $this->state(fn () => ['assigned_agent_id' => $agent->id]);
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
