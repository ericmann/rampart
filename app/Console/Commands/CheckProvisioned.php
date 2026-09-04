<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Exit 0 if the database has already been migrated + seeded, non-zero otherwise. Used by
 * docker/entrypoint.sh as the boot-time sentinel check — deliberately a dedicated command
 * rather than `artisan tinker --execute`, whose exit code does not reliably propagate.
 */
class CheckProvisioned extends Command
{
    protected $signature = 'rampart:check-provisioned';

    protected $description = 'Exit 0 if the app has already been provisioned (migrated + seeded), 1 otherwise';

    public function handle(): int
    {
        if (! Schema::hasTable('system_state')) {
            return self::FAILURE;
        }

        $provisioned = DB::table('system_state')->where('key', 'provisioned_at')->exists();

        return $provisioned ? self::SUCCESS : self::FAILURE;
    }
}
