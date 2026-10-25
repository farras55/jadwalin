#!/bin/sh
set -e

echo "=========================================="
echo " Starting Jadwalin Production Container   "
echo "=========================================="

# 1. Update Nginx listening port dynamically (Render sets $PORT, default: 8080)
PORT="${PORT:-8080}"
echo "--> Configuring Nginx to listen on port: ${PORT}"
sed -i "s/listen 8080;/listen ${PORT};/g" /etc/nginx/conf.d/default.conf

# 2. SQLite Database handling
if [ "${DB_CONNECTION}" = "sqlite" ] || [ -z "${DB_CONNECTION}" ]; then
    DB_FILE="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
    echo "--> Checking SQLite database file: ${DB_FILE}"
    mkdir -p "$(dirname "$DB_FILE")"
    if [ ! -f "$DB_FILE" ]; then
        echo "--> SQLite database file not found, creating new file..."
        touch "$DB_FILE"
    fi
    chown -R www-data:www-data "$(dirname "$DB_FILE")"
    chmod -R 775 "$(dirname "$DB_FILE")"
    chmod 664 "$DB_FILE"
fi

# 3. Ensure required directories and permissions exist
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 4. Ensure application key exists
if [ -z "${APP_KEY}" ]; then
    echo "--> WARNING: APP_KEY environment variable is not set! Generating a new one..."
    php artisan key:generate --force
fi

# 5. Run database migrations if enabled (default: true)
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "--> Running database migrations..."
    php artisan migrate --force
fi

# 6. Optional database seeding (default: false)
if [ "${SEED_DATABASE:-false}" = "true" ]; then
    echo "--> Running database seeder..."
    php artisan db:seed --force
fi

# 7. Create storage symlink
echo "--> Creating storage symlink..."
php artisan storage:link || true

# 8. Cache Laravel config, routes, and views for optimal performance
echo "--> Optimizing Laravel caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 9. Start Supervisord (runs Nginx and PHP-FPM in foreground)
echo "--> Launching Supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
