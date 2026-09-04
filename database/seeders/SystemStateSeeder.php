<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemStateSeeder extends Seeder
{
    /**
     * Written last. docker/entrypoint.sh checks for this row before deciding whether to
     * migrate+seed on boot, so a container restart never reseeds over an attendee's work.
     */
    public function run(): void
    {
        DB::table('system_state')->updateOrInsert(
            ['key' => 'provisioned_at'],
            ['value' => now()->toIso8601String(), 'created_at' => now(), 'updated_at' => now()]
        );
    }
}
