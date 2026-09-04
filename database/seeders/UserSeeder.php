<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use FixtureLoader;

    /**
     * A04:2025 — Cryptographic Failures. The fixture only carries each seeded user's
     * plaintext password; hashing it is this seeder's job, via the app's own Hash facade
     * (bcrypt — see config/hashing.php), the same call any real registration/password-
     * change path uses. main's UserSeeder wrote a precomputed md5() digest straight from
     * the fixture with no hashing at request time at all.
     */
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
                'password' => Hash::make($user['password_plain']),
                'email_verified_at' => $timestamp,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }, $this->loadFixture('users'));

        DB::table('users')->insert($rows);
    }
}
