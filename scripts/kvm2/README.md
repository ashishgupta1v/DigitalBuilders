# Hostinger KVM 2 VPS Deployment Guide for DigitalBuilders

This guide walks through deploying the complete **Laravel + Inertia + Vue 3** application, **24/7 background scheduler**, and **Supervisor queue daemons** onto a **Hostinger KVM 2 VPS** (Ubuntu 22.04 / 24.04 LTS).

---

## 1. Prerequisites
- Hostinger KVM 2 VPS (2 vCPU, 8 GB RAM, NVMe SSD).
- Root SSH access: `ssh root@<YOUR_VPS_IP>`.
- Domain DNS pointing:
  - `A` record: `@` -> `<YOUR_VPS_IP>`
  - `A` record: `www` -> `<YOUR_VPS_IP>`

---

## 2. Server Provisioning (One-Time: ~5 minutes)

SSH into your Hostinger KVM 2 VPS and run:

```bash
# Clone the repository into /var/www/digitalbuilders
git clone https://github.com/ashishgupta1v/DigitalBuilders.git /var/www/digitalbuilders
cd /var/www/digitalbuilders

# Make provisioning script executable and run it
chmod +x scripts/kvm2/setup-server.sh scripts/kvm2/deploy.sh
sudo bash scripts/kvm2/setup-server.sh
```

---

## 3. Environment Configuration

Copy the production `.env` file:

```bash
cp /var/www/digitalbuilders/.env.example /var/www/digitalbuilders/.env
nano /var/www/digitalbuilders/.env
```

Ensure the following variables are set:
```ini
APP_NAME=DigitalBuilders
APP_ENV=production
APP_DEBUG=false
APP_URL=https://www.digitalbuilders.in

# High-Availability Neon PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=ep-still-sea-a1vxfmfm.ap-southeast-1.aws.neon.tech
DB_PORT=5432
DB_DATABASE=neondb
DB_USERNAME=neondb_owner
DB_PASSWORD=<YOUR_NEON_PASSWORD>
DB_SSLMODE=require

# Redis for high-throughput queuing & caching
QUEUE_CONNECTION=redis
CACHE_STORE=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Mobile Founder Cockpit Alerts (Telegram Bot)
TELEGRAM_BOT_TOKEN=<YOUR_TELEGRAM_BOT_TOKEN>
TELEGRAM_CHAT_ID=<YOUR_TELEGRAM_CHAT_ID>

# Email Delivery
MAIL_MAILER=smtp
MAIL_HOST=smtp.resend.com
MAIL_PORT=465
MAIL_USERNAME=resend
MAIL_PASSWORD=<YOUR_RESEND_API_KEY>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=hello@digitalbuilders.in
MAIL_FROM_NAME="DigitalBuilders"
```

---

## 4. Nginx, SSL & Supervisor Configuration

```bash
# 1. Link Nginx Virtual Host
sudo cp /var/www/digitalbuilders/scripts/kvm2/nginx-digitalbuilders.conf /etc/nginx/sites-available/digitalbuilders.conf
sudo ln -s /etc/nginx/sites-available/digitalbuilders.conf /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t

# 2. Issue Free SSL Certificate via Let's Encrypt
sudo certbot --nginx -d digitalbuilders.in -d www.digitalbuilders.in

# 3. Configure Supervisor for 24/7 Queue Workers
sudo cp /var/www/digitalbuilders/scripts/kvm2/supervisor-digitalbuilders.conf /etc/supervisor/conf.d/digitalbuilders.conf
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all

# 4. Set Up Native 1-Minute Crontab
(crontab -l 2>/dev/null; echo "* * * * * cd /var/www/digitalbuilders && php artisan schedule:run >> /dev/null 2>&1") | crontab -
```

---

## 5. Initial Deployment & Future Updates

To deploy or update whenever you push changes:

```bash
sudo bash /var/www/digitalbuilders/scripts/kvm2/deploy.sh
```

---

## 6. Verifying 24/7 Services

```bash
# Check queue worker status
sudo supervisorctl status

# View live queue worker logs
sudo tail -f /var/log/supervisor/digitalbuilders-worker.log

# View scheduled tasks
php /var/www/digitalbuilders/artisan schedule:list

# Test scheduler manually
php /var/www/digitalbuilders/artisan schedule:run
```
