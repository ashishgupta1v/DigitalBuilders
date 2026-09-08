#!/usr/bin/env bash
# ==============================================================================
# DigitalBuilders Zero-Downtime Deployment Script for Hostinger KVM 2
# ==============================================================================
set -e

APP_DIR="/var/www/DigitalBuilders"
cd "$APP_DIR"

echo ">>> [1/7] Pulling latest code from GitHub main..."
git fetch origin main
git reset --hard origin/main

echo ">>> [2/7] Installing PHP production dependencies..."
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

echo ">>> [3/7] Installing Node dependencies and building Vite assets..."
npm ci --silent
npm run build

echo ">>> [4/7] Ensuring storage links and file permissions..."
php artisan storage:link || true
chown -R www-data:www-data "$APP_DIR"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

echo ">>> [5/7] Running database migrations..."
php artisan migrate --force

echo ">>> [6/7] Caching configuration, routes, and compiled views..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ">>> [7/7] Restarting DigitalBuilders Supervisor workers & reloading Nginx..."
supervisorctl reread
supervisorctl update
supervisorctl restart digitalbuilders-worker:* || supervisorctl start digitalbuilders-worker:*
systemctl reload nginx

echo "=============================================================================="
echo "🚀 DEPLOYMENT COMPLETED SUCCESSFULLY ON HOSTINGER KVM 2!"
echo "Timestamp: $(date '+%Y-%m-%d %H:%M:%S')"
echo "=============================================================================="
