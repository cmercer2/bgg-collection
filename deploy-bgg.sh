#!/bin/bash

# Configuration
LOCAL_DIR="$(pwd)"
REMOTE_USER=cmercer
REMOTE_HOST=raspberrypi.mercerhouse.lan       # or use the Pi's IP address
REMOTE_WEB_DIR="/var/www/html"

# Optional: specify a custom target subfolder
TARGET_DIR="$REMOTE_WEB_DIR/bgg-collection"

TEMP_REMOTE_DIR="/home/$REMOTE_USER/temp-bgg-deploy"

echo "Deploying $LOCAL_DIR to $REMOTE_USER@$REMOTE_HOST:$TARGET_DIR via temp directory $TEMP_REMOTE_DIR"


# Backup existing cache if it exists
ssh "$REMOTE_USER@$REMOTE_HOST" "mkdir -p $TEMP_REMOTE_DIR && if [ -d $TARGET_DIR/cache ]; then cp -r $TARGET_DIR/cache $TEMP_REMOTE_DIR/; fi"

# Sync files to a temporary directory
rsync -av --exclude 'deploy-bgg.sh' --exclude '.gitignore' "$LOCAL_DIR/" "$REMOTE_USER@$REMOTE_HOST:$TEMP_REMOTE_DIR"

# Move to web directory with sudo, removing old target directory
ssh "$REMOTE_USER@$REMOTE_HOST" "sudo rm -rf $TARGET_DIR && sudo mv $TEMP_REMOTE_DIR $TARGET_DIR && sudo chown -R www-data:www-data $TARGET_DIR && sudo chmod -R 755 $TARGET_DIR"

echo "Deployment complete. Visit: http://$REMOTE_HOST/bgg-collection/"