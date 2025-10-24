#!/bin/sh
set -e

echo "Creating/ preparing storage directories"
mkdir -p /var/www/html/storage/cache \
         /var/www/html/storage/generated \
         /var/www/html/storage/logs \
         /var/www/html/storage/upload

chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

echo "Run migrations"
php /var/www/html/console migrate --force-migration || true

exec php-fpm
