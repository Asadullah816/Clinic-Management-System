#!/bin/bash
set -e

cd /var/www/html

# ---------- Wait for the database (up to ~60s) ----------
echo "Waiting for the database..."
until php -r '
    try {
        new PDO(
            "mysql:host=" . getenv("DB_HOST") . ";dbname=" . getenv("DB_DATABASE"),
            getenv("DB_USERNAME"),
            getenv("DB_PASSWORD")
        );
    } catch (Exception $e) {
        exit(1);
    }
' >/dev/null 2>&1; do
    sleep 2
done
echo "Database is up."

# ---------- Generate APP_KEY only if the .env has none ----------
if ! grep -q "^APP_KEY=base64" .env 2>/dev/null; then
    php artisan key:generate --force
fi

# ---------- Migrate + seed on every start ----------
# Safe to repeat: migrations are tracked, and all seeders use
# updateOrCreate(), so they never create duplicates.
php artisan migrate --force
php artisan db:seed --force

echo "Ready."
exec "$@"
