<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SsrfMarkerSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/ssrf-marker.txt');

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents(
            $path,
            "RAMPART-LOCAL-FILE-MARKER\n".
            "internal_note: build-server deploy key rotated 2026-08-01\n"
        );
    }
}
