#!/bin/sh
set -eu

mkdir -p /app/var/cache/products /app/var/storage/counters /app/var/log
chown -R www-data:www-data /app/var/cache/products /app/var/storage/counters /app/var/log
chmod -R ug+rwX /app/var/cache/products /app/var/storage/counters /app/var/log

exec "$@"
