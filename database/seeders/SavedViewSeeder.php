<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SavedViewSeeder extends Seeder
{
    /**
     * A couple of ordinary, safe saved views for the seeded agents — deliberately NOT the
     * object-injection gadget payload. That's something attendees (or the hidden exploit
     * test) craft themselves; seeding it here would trip A08 on first admin page load.
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
