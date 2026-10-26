#!/bin/sh

APP_ROOT=$(dirname "$(dirname "$0")")
LOCK_FILE="$APP_ROOT/storage/framework/episode-notifications.lock"
LOG_FILE="$APP_ROOT/storage/logs/episode-queue.log"

exec /usr/bin/flock -n "$LOCK_FILE" /usr/bin/php "$APP_ROOT/artisan" queue:work database \
    --queue=episode-notifications --stop-when-empty --max-time=50 --timeout=45 \
    --tries=3 --quiet >> "$LOG_FILE" 2>&1
