#!/bin/bash
set -e

cd /var/www/html

# ---------- 1. Ensure a .env exists ----------
if [ ! -f .env ]; then
    echo "APP_NAME=\"Skin Clinic\"" > .env
fi

# ---------- 2. Wait for the database (network databases only) ----------
if [ "$DB_CONNECTION" = "mysql" ] || [ "$DB_CONNECTION" = "pgsql" ]; then
    echo "Waiting for the database at ${DB_HOST}..."
    until php -r '
        $dsn = getenv("DB_CONNECTION") === "pgsql"
            ? "pgsql:host=" . getenv("DB_HOST") . ";dbname=" . getenv("DB_DATABASE")
            : "mysql:host=" . getenv("DB_HOST") . ";dbname=" . getenv("DB_DATABASE");
        try {
            new PDO($dsn, getenv("DB_USERNAME"), getenv("DB_PASSWORD"));
        } catch (Throwable $e) {
            exit(1);
        }
    ' >/dev/null 2>&1; do
        sleep 2
    done
    echo "Database is up."
fi

# ---------- 3. SQLite: create the file + FIX OWNERSHIP ----------
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_CONNECTION" ]; then
    touch database/database.sqlite
    # Apache runs as www-data. SQLite writes to the file AND creates
    # journal files in the folder — both must be writable by it.
    chown -R www-data:www-data database
fi

# ---------- 4. APP_KEY ----------
if ! grep -q "^APP_KEY=base64" .env; then
    php artisan key:generate --force
fi

# ---------- 5. Migrate + seed (safe on every start) ----------
php artisan migrate --force
php artisan db:seed --force

# ---------- 6. Safety: Laravel's writable folders ----------
chown -R www-data:www-data storage bootstrap/cache

# ---------- 7. Bind Apache to the host's port ----------
if [ -n "$PORT" ] && [ "$PORT" != "80" ]; then
    sed -i -e "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
    sed -i -e "s/:80>/:${PORT}>/" /etc/apache2/sites-enabled/000-default.conf
fi

echo "Ready."
exec "$@"
