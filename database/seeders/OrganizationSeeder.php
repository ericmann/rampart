<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganizationSeeder extends Seeder
{
    use FixtureLoader;

    public function run(): void
    {
        $rows = array_map(function (array $org) {
            $timestamp = $this->daysAgo($org['created_days_ago']);

            return [
                'id' => $org['id'],
                'name' => $org['name'],
                'domain' => $org['domain'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }, $this->loadFixture('organizations'));

        DB::table('organizations')->insert($rows);
    }
}
