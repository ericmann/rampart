<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WebhookSeeder extends Seeder
{
    use FixtureLoader;

    public function run(): void
    {
        $rows = array_map(function (array $webhook) {
            $timestamp = $this->daysAgo($webhook['created_days_ago']);

            return [
                'id' => $webhook['id'],
                'name' => $webhook['name'],
                'target_url' => $webhook['target_url'],
                'event' => $webhook['event'],
                'inbound_token' => $webhook['inbound_token'],
                'secret' => $webhook['secret'],
                'is_active' => $webhook['is_active'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }, $this->loadFixture('webhooks'));

        DB::table('webhooks')->insert($rows);
    }
}
