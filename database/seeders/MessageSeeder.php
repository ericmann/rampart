<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MessageSeeder extends Seeder
{
    use FixtureLoader;

    public function run(): void
    {
        $rows = array_map(function (array $message) {
            $timestamp = $this->minutesAgo($message['created_minutes_ago']);

            return [
                'id' => $message['id'],
                'ticket_id' => $message['ticket_id'],
                'author_id' => $message['author_id'],
                'body' => $message['body'],
                'is_internal_note' => $message['is_internal_note'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }, $this->loadFixture('messages'));

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('messages')->insert($chunk);
        }
    }
}
