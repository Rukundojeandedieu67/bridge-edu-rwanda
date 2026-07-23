#!/bin/sh
set -e

if [ ! -f /var/www/.env ]; then
  cp /var/www/.env.example /var/www/.env
fi

php artisan key:generate --force || true

until php artisan migrate --force; do
  echo "Database is not ready yet. Retrying in 5 seconds..."
  sleep 5
done

php artisan db:seed --force || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec php artisan serve --host=0.0.0.0 --port=8000
