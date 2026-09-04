<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Loads the committed fixture JSON verbatim — no runtime Faker. See
     * database/fixtures/generate.php for how that JSON was produced, and
     * docs/VULN-MAP.md for why the data looks the way it does (weak passwords,
     * unsigned webhooks, etc.).
     */
    public function run(): void
    {
        $this->call([
            OrganizationSeeder::class,
            UserSeeder::class,
            TicketSeeder::class,
            MessageSeeder::class,
            AttachmentSeeder::class,
            KbArticleSeeder::class,
            WebhookSeeder::class,
            SavedViewSeeder::class,
            SsrfMarkerSeeder::class,
            SystemStateSeeder::class,
        ]);
    }
}
