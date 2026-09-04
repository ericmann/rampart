<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    use FixtureLoader;

    public function run(): void
    {
        $rows = array_map(function (array $user) {
            $timestamp = $this->daysAgo($user['created_days_ago']);

            return [
                'id' => $user['id'],
                'organization_id' => $user['organization_id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'password' => $user['password_md5'],
                'email_verified_at' => $timestamp,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }, $this->loadFixture('users'));

        DB::table('users')->insert($rows);
    }
}
