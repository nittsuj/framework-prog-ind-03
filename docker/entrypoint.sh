#!/bin/sh
set -eu

mkdir -p /var/lib/portal

if [ ! -s /var/lib/portal/app.key ]; then
    php -r 'echo "base64:".base64_encode(random_bytes(32));' > /var/lib/portal/app.key
fi
export APP_KEY="${APP_KEY:-$(cat /var/lib/portal/app.key)}"
export DB_CONNECTION=sqlite
export DB_DATABASE=/var/lib/portal/academic.sqlite

# Artisan commands launched later with `docker compose exec` load this file,
# while the long-running Apache process receives the same key from the export.
printf 'APP_KEY=%s\n' "$APP_KEY" > .env

if [ ! -f "$DB_DATABASE" ]; then
    touch "$DB_DATABASE"
fi

chown -R www-data:www-data /var/lib/portal storage bootstrap/cache

# Seeding dijalankan sekali sampai berhasil. Marker baru dibuat setelah seeder
# selesai, sehingga boot berikutnya mengulang seeding bila sebelumnya gagal.
# DatabaseSeeder sudah idempoten, jadi pengulangan tidak membuat data ganda.
if [ ! -f /var/lib/portal/.seeded ]; then
    su -s /bin/sh www-data -c 'php artisan migrate --seed --force'
    touch /var/lib/portal/.seeded
else
    su -s /bin/sh www-data -c 'php artisan migrate --force'
fi

exec "$@"
