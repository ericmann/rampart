<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Organization>
 */
class OrganizationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'domain' => fake()->unique()->domainName(),
        ];
    }
}
