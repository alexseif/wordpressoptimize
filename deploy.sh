#!/usr/bin/env bash
set -euo pipefail

# ==============================================================================
# WordPress Optimize - Theme Deployment Script
# Environment: DigitalOcean Droplet
# Standard Path: /var/www/wordpressoptimize.com/public
# ==============================================================================

THEME_DIR="/var/www/wordpressoptimize.com/public/wp-content/themes/wpopt"
WP_PATH="/var/www/wordpressoptimize.com/public"

echo "=== [1/3] Pulling latest theme updates from origin/master ==="
cd "$THEME_DIR"
git pull origin master

echo "=== [1b/3] Syncing Sandcastles Schema FAQ plugin ==="
FAQ_DIR="$WP_PATH/wp-content/plugins/sandcastles-schema-faq"
if [ ! -d "$FAQ_DIR" ]; then
    git clone git@github.com:WPO-SANDCASTLES/sandcastles-schema-faq.git "$FAQ_DIR" || echo "Warning: failed to clone sandcastles-schema-faq"
else
    git -C "$FAQ_DIR" pull origin master || echo "Warning: failed to pull sandcastles-schema-faq"
fi

echo "=== [2/3] Running Post-Deploy Automated Tasks ==="
if [ -f "$THEME_DIR/bin/post-deploy.php" ] && command -v wp >/dev/null 2>&1; then
    wp eval-file "$THEME_DIR/bin/post-deploy.php" --use-include --path="$WP_PATH" || echo "Warning: post-deploy script returned non-zero"
fi

echo "=== [3/3] Flushing WordPress Object / Page Cache ==="
if command -v wp >/dev/null 2>&1; then
    wp cache flush --path="$WP_PATH" || echo "Warning: wp cache flush returned non-zero"
fi

echo "=== Deployment successfully completed! ==="
