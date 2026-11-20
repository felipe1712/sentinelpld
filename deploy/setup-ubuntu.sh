#!/usr/bin/env bash
set -euo pipefail

# Aprovisionamiento inicial para Ubuntu 24.04 LTS — sentinelpld.mx
# Ejecutar como root o con sudo: sudo bash deploy/setup-ubuntu.sh

export DEBIAN_FRONTEND=noninteractive

apt-get update && apt-get upgrade -y

apt-get install -y nginx mysql-server redis-server supervisor certbot python3-certbot-nginx \
    unzip git curl ufw software-properties-common \
    php8.3-fpm php8.3-cli php8.3-mysql php8.3-redis php8.3-mbstring php8.3-xml \
    php8.3-bcmath php8.3-curl php8.3-zip php8.3-gd php8.3-intl

if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

if ! command -v node &> /dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt-get install -y nodejs
fi

ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable

systemctl enable --now php8.3-fpm nginx mysql redis-server supervisor

CRON_JOB="* * * * * www-data php /var/www/sentinelpld/artisan schedule:run >> /dev/null 2>&1"
echo "$CRON_JOB" > /etc/cron.d/sentinelpld-scheduler
chmod 0644 /etc/cron.d/sentinelpld-scheduler

echo "=== Servidor base listo para sentinelpld.mx ==="
echo "1. Crear BD 'sentinelpld' y usuario MySQL dedicado."
echo "2. Clonar https://github.com/felipe1712/sentinelpld.git en /var/www/sentinelpld y copiar .env.production.example a .env"
echo "3. Copiar deploy/nginx-sentinelpld.mx.conf a /etc/nginx/sites-available/sentinelpld.mx y enlazar en sites-enabled."
echo "4. Copiar deploy/supervisor-atestia-worker.conf a /etc/supervisor/conf.d/sentinelpld-worker.conf"
echo "5. Ejecutar: certbot --nginx -d sentinelpld.mx -d www.sentinelpld.mx"
