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

exec "$@"
