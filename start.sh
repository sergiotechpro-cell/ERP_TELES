#!/bin/sh

set -e

# Wait for database to be ready
echo "Checking database connection..."
php artisan migrate:status || sleep 2

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Run seeders (only runs if tables are empty, safe to run multiple times)
echo "Running database seeders..."
php artisan db:seed --force || echo "Seeders completed or skipped (may already exist)"

# Start the Laravel server
echo "Starting Laravel server on port ${PORT:-8000}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}

