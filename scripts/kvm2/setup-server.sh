#!/usr/bin/env bash
# ==============================================================================
# Hostinger KVM 2 VPS Provisioning Script for DigitalBuilders
# OS: Ubuntu 22.04 / 24.04 LTS
# ==============================================================================
set -e

echo ">>> [1/7] Updating Ubuntu System Packages..."
export DEBIAN_FRONTEND=noninteractive
apt-get update && apt-get upgrade -y
apt-get install -y software-properties-common curl wget git unzip zip ufw htop supervisor redis-server

echo ">>> [2/7] Configuring UFW Firewall..."
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable

echo ">>> [3/7] Installing PHP 8.3 & High-Performance Extensions..."
add-apt-repository -y ppa:ondrej/php
apt-get update
apt-get install -y php8.3-fpm php8.3-cli php8.3-pgsql php8.3-curl php8.3-mbstring \
    php8.3-xml php8.3-bcmath php8.3-redis php8.3-zip php8.3-intl php8.3-opcache

echo ">>> [4/7] Installing Composer..."
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

echo ">>> [5/7] Installing Node.js 20 LTS & npm..."
if ! command -v node &> /dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt-get install -y nodejs
fi

echo ">>> [6/7] Installing Nginx & Certbot..."
apt-get install -y nginx certbot python3-certbot-nginx

echo ">>> [7/7] Preparing Web Directory & Permissions..."
mkdir -p /var/www/digitalbuilders
chown -R www-data:www-data /var/www/digitalbuilders
chmod -R 775 /var/www/digitalbuilders

echo "=============================================================================="
echo "✅ Server dependencies installed successfully on Hostinger KVM 2!"
echo "PHP Version: $(php -v | head -n 1)"
echo "Node Version: $(node -v)"
echo "Composer Version: $(composer --version | head -n 1)"
echo "Redis Status: $(systemctl is-active redis-server)"
echo "=============================================================================="
