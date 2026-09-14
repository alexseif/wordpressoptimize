#!/usr/bin/env bash
set -euo pipefail

# ==============================================================================
# WordPress Optimize - Theme Deployment Script
# Environment: DigitalOcean Droplet
# Standard Path: /var/www/wordpressoptimize.com/public
# ==============================================================================

THEME_DIR="/var/www/wordpressoptimize.com/public/wp-content/themes/wpopt"
WP_PATH="/var/www/wordpressoptimize.com/public"

echo "=== [1/2] Pulling latest theme updates from origin/master ==="
cd "$THEME_DIR"
git pull origin master

echo "=== [2/2] Flushing WordPress Object / Page Cache ==="
if command -v wp >/dev/null 2>&1; then
    wp cache flush --path="$WP_PATH" || echo "Warning: wp cache flush returned non-zero"
fi

echo "=== Deployment successfully completed! ==="
