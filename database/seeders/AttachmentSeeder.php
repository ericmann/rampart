<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentSeeder extends Seeder
{
    use FixtureLoader;

    public function run(): void
    {
        $sourceDir = base_path('database/fixtures/attachments');
        $rows = [];

        foreach ($this->loadFixture('attachments') as $attachment) {
            $sourcePath = "{$sourceDir}/{$attachment['source_file']}";
            $contents = file_get_contents($sourcePath);
            $storedName = Str::uuid().'-'.$attachment['original_name'];
            $storedPath = "attachments/{$storedName}";

            Storage::disk('local')->put($storedPath, $contents);

            $timestamp = $this->daysAgo($attachment['created_days_ago']);

            $rows[] = [
                'id' => $attachment['id'],
                'ticket_id' => $attachment['ticket_id'],
                'message_id' => null,
                'uploader_id' => $attachment['uploader_id'],
                'original_name' => $attachment['original_name'],
                'stored_path' => $storedPath,
                'mime_type' => $attachment['mime_type'],
                'size' => strlen($contents),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        DB::table('attachments')->insert($rows);
    }
}
