<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SsrfMarkerSeeder extends Seeder
{
    /**
     * Plants a local file with an obviously-fake "secret" so the KB preview-link SSRF
     * (docs/VULN-MAP.md — A01c) can demonstrate file:// local-file read, not just fetching
     * the metadata-mock service over HTTP. Content is inert — a string, nothing executable.
     */
    public function run(): void
    {
        $path = storage_path('app/ssrf-marker.txt');

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents(
            $path,
            "RAMPART-LOCAL-FILE-MARKER: this file was read via SSRF (file://), not fetched over HTTP.\n".
            "fake_internal_note: build-server deploy key rotated 2026-08-01 (this value is fake, for the workshop only)\n"
        );
    }
}
