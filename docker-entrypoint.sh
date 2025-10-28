#!/bin/sh
set -e

# Ensure cache dir exists and is writable
PROJECT_DIR="/var/www/html"
CACHE_DIR="$PROJECT_DIR/cache"
if [ ! -d "$CACHE_DIR" ]; then
  mkdir -p "$CACHE_DIR"
fi
chown -R www-data:www-data "$CACHE_DIR"

exec "$@"
