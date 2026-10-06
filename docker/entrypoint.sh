#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

# /var/www/html is the host's checkout, bind-mounted. Hand anything created here back to
# whoever owns the checkout, so a Linux host doesn't end up with root-owned files it can't
# edit. (Docker Desktop on macOS/Windows maps ownership itself; there this is a no-op.)
host_owner=$(stat -c '%u:%g' .)
give_back() { chown -h "$host_owner" "$@" 2>/dev/null || true; }

if [ ! -f .env ]; then
    cp .env.example .env
    give_back .env
fi

if ! grep -q '^APP_KEY=base64' .env 2>/dev/null; then
    php artisan key:generate --force
fi

echo "Waiting for MySQL at ${DB_HOST:-mysql}:${DB_PORT:-3306}..."
tries=0
until php -r '
    $host = getenv("DB_HOST") ?: "mysql";
    $port = getenv("DB_PORT") ?: 3306;
    $db = getenv("DB_DATABASE") ?: "rampart";
    $user = getenv("DB_USERNAME") ?: "rampart";
    $pass = getenv("DB_PASSWORD") ?: "";
    new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass);
' > /dev/null 2>&1; do
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
if php artisan rampart:check-provisioned > /dev/null 2>&1; then
    echo "Already provisioned — skipping migrate/seed, serving existing data."
else
    echo "First boot detected — running migrations and seeding the canonical fixture dataset."
    php artisan migrate --force
    php artisan db:seed --force
fi

php artisan storage:link > /dev/null 2>&1 || true
give_back public/storage

exec "$@"
