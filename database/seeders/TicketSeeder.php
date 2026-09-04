<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketSeeder extends Seeder
{
    use FixtureLoader;

    public function run(): void
    {
        $rows = array_map(function (array $ticket) {
            $timestamp = $this->daysAgo($ticket['created_days_ago']);

            return [
                'id' => $ticket['id'],
                'organization_id' => $ticket['organization_id'],
                'requester_id' => $ticket['requester_id'],
                'assigned_agent_id' => $ticket['assigned_agent_id'],
                'subject' => $ticket['subject'],
                'body' => $ticket['body'],
                'status' => $ticket['status'],
                'priority' => $ticket['priority'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }, $this->loadFixture('tickets'));

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('tickets')->insert($chunk);
        }
    }
}
