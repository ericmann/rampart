#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

if [ ! -f .env ]; then
    cp .env.example .env
fi

if ! grep -q '^APP_KEY=base64' .env 2>/dev/null; then
    php artisan key:generate --force
fi

echo "Waiting for MySQL at ${DB_HOST:-mysql}:${DB_PORT:-3306}..."
tries=0
until php artisan db:show > /dev/null 2>&1; do
    tries=$((tries + 1))
    if [ "$tries" -ge 60 ]; then
        echo "MySQL never became reachable after ${tries} attempts; giving up." >&2
        exit 1
    fi
    sleep 2
done
echo "MySQL is reachable."

mkdir -p storage/framework/{cache,sessions,views,testing} storage/app/public storage/logs bootstrap/cache

# Sentinel: only provision (migrate + seed) on a genuinely first boot, so restarting the
# container never reseeds over an attendee's in-progress work.
NEEDS_PROVISION=1
if php artisan tinker --execute="exit(Illuminate\Support\Facades\Schema::hasTable('system_state') ? 0 : 1);" > /dev/null 2>&1; then
    if php artisan tinker --execute="exit(DB::table('system_state')->where('key', 'provisioned_at')->exists() ? 0 : 1);" > /dev/null 2>&1; then
        NEEDS_PROVISION=0
    fi
fi

if [ "$NEEDS_PROVISION" = "1" ]; then
    echo "First boot detected — running migrations and seeding the canonical fixture dataset."
    php artisan migrate --force
    php artisan db:seed --force
else
    echo "Already provisioned — skipping migrate/seed, serving existing data."
fi

php artisan storage:link > /dev/null 2>&1 || true

exec "$@"
