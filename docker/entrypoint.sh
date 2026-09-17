#!/bin/bash
set -e

cd /var/www/html

# ---------- 1. Ensure a .env exists (Render builds from git, which has no .env) ----------
if [ ! -f .env ]; then
    echo "APP_NAME=\"Skin Clinic\"" > .env
fi

# ---------- 2. Ensure Laravel's runtime directories exist ----------
# The .dockerignore can leave these folders missing entirely — Laravel
# then dies with "Please provide a valid cache path" (500 on every page).
mkdir -p storage/framework/sessions \
         storage/framework/views \
         storage/framework/cache/data \
         storage/logs \
         bootstrap/cache

# ---------- 3. Wait for the database (network databases only) ----------
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

# ---------- 4. SQLite: create the database file ----------
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_CONNECTION" ]; then
    touch database/database.sqlite
fi

# ---------- 5. Make everything writable by Apache (www-data) ----------
# SQLite is written on nearly every request; same for logs, compiled
# views, and sessions. Root-owned files = Permission denied = 500.
chown -R www-data:www-data storage bootstrap/cache database

# ---------- 6. APP_KEY ----------
if ! grep -q "^APP_KEY=base64" .env; then
    php artisan key:generate --force
fi

# ---------- 7. Migrate + seed (safe on every start) ----------
php artisan migrate --force
php artisan db:seed --force

# ---------- 8. Bind Apache to the host's port ----------
if [ -n "$PORT" ] && [ "$PORT" != "80" ]; then
    sed -i -e "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
    sed -i -e "s/:80>/:${PORT}>/" /etc/apache2/sites-enabled/000-default.conf
fi

echo "Ready."
exec "$@"
