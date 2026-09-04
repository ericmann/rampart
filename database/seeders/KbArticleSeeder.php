<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KbArticleSeeder extends Seeder
{
    use FixtureLoader;

    public function run(): void
    {
        $rows = array_map(function (array $article) {
            $timestamp = $this->daysAgo($article['created_days_ago']);

            return [
                'id' => $article['id'],
                'author_id' => $article['author_id'],
                'title' => $article['title'],
                'slug' => $article['slug'],
                'body' => $article['body'],
                'is_published' => $article['is_published'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }, $this->loadFixture('kb_articles'));

        DB::table('kb_articles')->insert($rows);
    }
}
