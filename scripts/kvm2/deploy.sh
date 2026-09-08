#!/usr/bin/env bash
# ==============================================================================
# DigitalBuilders Zero-Downtime Deployment Script for Hostinger KVM 2
# ==============================================================================
set -e

APP_DIR="/var/www/digitalbuilders"
cd "$APP_DIR"

echo ">>> [1/6] Pulling latest code from GitHub main..."
git fetch origin main
git reset --hard origin/main

echo ">>> [2/6] Installing PHP production dependencies..."
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

echo ">>> [3/6] Installing Node dependencies and building Vite assets..."
npm ci --silent
npm run build

echo ">>> [4/6] Running database migrations..."
php artisan migrate --force

echo ">>> [5/6] Caching configuration, routes, and compiled views..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ">>> [6/6] Restarting Supervisor background queue workers & Nginx..."
supervisorctl reread
supervisorctl update
supervisorctl restart all
systemctl reload nginx

echo "=============================================================================="
echo "🚀 DEPLOYMENT COMPLETED SUCCESSFULLY ON HOSTINGER KVM 2!"
echo "Timestamp: $(date '+%Y-%m-%d %H:%M:%S')"
echo "=============================================================================="
