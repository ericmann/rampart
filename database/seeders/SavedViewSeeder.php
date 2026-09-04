<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SavedViewSeeder extends Seeder
{
    /**
     * A couple of default saved views for the seeded agents.
     */
    public function run(): void
    {
        $views = [
            ['user_id' => 2, 'name' => 'My open tickets', 'preferences' => ['status' => 'open', 'priority' => null]],
            ['user_id' => 3, 'name' => 'Urgent queue', 'preferences' => ['status' => null, 'priority' => 'urgent']],
        ];

        $now = now();

        DB::table('saved_views')->insert(array_map(fn ($view) => [
            'user_id' => $view['user_id'],
            'name' => $view['name'],
            'preferences' => serialize($view['preferences']),
            'created_at' => $now,
            'updated_at' => $now,
        ], $views));
    }
}
