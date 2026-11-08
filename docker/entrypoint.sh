#!/bin/sh
set -eu

mkdir -p /var/lib/portal

if [ ! -s /var/lib/portal/app.key ]; then
    php -r 'echo "base64:".base64_encode(random_bytes(32));' > /var/lib/portal/app.key
fi
export APP_KEY="${APP_KEY:-$(cat /var/lib/portal/app.key)}"
export DB_CONNECTION=sqlite
export DB_DATABASE=/var/lib/portal/academic.sqlite

first_boot=0
if [ ! -f "$DB_DATABASE" ]; then
    touch "$DB_DATABASE"
    first_boot=1
fi

chown -R www-data:www-data /var/lib/portal storage bootstrap/cache
if [ "$first_boot" -eq 1 ]; then
    su -s /bin/sh www-data -c 'php artisan migrate --seed --force'
else
    su -s /bin/sh www-data -c 'php artisan migrate --force'
fi

exec "$@"
