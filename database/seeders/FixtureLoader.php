<?php

namespace Database\Seeders;

trait FixtureLoader
{
    protected function loadFixture(string $name): array
    {
        $path = base_path("database/fixtures/{$name}.json");

        return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    }

    protected function daysAgo(int $days): \Illuminate\Support\Carbon
    {
        return now()->subDays($days);
    }

    protected function minutesAgo(int $minutes): \Illuminate\Support\Carbon
    {
        return now()->subMinutes($minutes);
    }
}
