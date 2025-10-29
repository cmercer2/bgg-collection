#!/bin/sh
set -e

# Ensure cache dir exists and is writable
PROJECT_DIR="/var/www/html"
CACHE_DIR="$PROJECT_DIR/cache"
if [ ! -d "$CACHE_DIR" ]; then
  mkdir -p "$CACHE_DIR"
fi
if command -v chown >/dev/null 2>&1; then
  if chown -R www-data:www-data "$CACHE_DIR" 2>/dev/null; then
    true
  else
    # Fallback to making cache world-writable if chown is not permitted
    chmod -R 0777 "$CACHE_DIR" || true
  fi
else
  chmod -R 0777 "$CACHE_DIR" || true
fi

# Ensure repository files are readable by www-data (best-effort)
chmod -R 0755 /var/www/html || true

exec "$@"
