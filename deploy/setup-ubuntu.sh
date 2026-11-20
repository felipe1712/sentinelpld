#!/usr/bin/env bash
set -euo pipefail

# Aprovisionamiento inicial para Ubuntu 24.04 LTS — notaria.atestia.com.mx
# Ejecutar como root o con sudo: sudo bash deploy/setup-ubuntu.sh

export DEBIAN_FRONTEND=noninteractive

apt-get update && apt-get upgrade -y

# 1. Nginx, MySQL, Redis, Supervisor, Certbot, Node.js, PHP 8.3
apt-get install -y nginx mysql-server redis-server supervisor certbot python3-certbot-nginx \
    unzip git curl ufw software-properties-common \
    php8.3-fpm php8.3-cli php8.3-mysql php8.3-redis php8.3-mbstring php8.3-xml \
    php8.3-bcmath php8.3-curl php8.3-zip php8.3-gd php8.3-intl

# 2. Composer
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

# 3. Node.js 20 LTS (para Vite build)
if ! command -v node &> /dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt-get install -y nodejs
fi

# 4. Firewall UFW
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable

# 5. Habilitar servicios
systemctl enable --now php8.3-fpm nginx mysql redis-server supervisor

# 6. Cron para Laravel Scheduler (reevaluaciones PLD, recordatorios dia 17, etc.)
CRON_JOB="* * * * * www-data php /var/www/atestia-notaria/artisan schedule:run >> /dev/null 2>&1"
echo "$CRON_JOB" > /etc/cron.d/atestia-notaria-scheduler
chmod 0644 /etc/cron.d/atestia-notaria-scheduler

echo "=== Servidor base listo ==="
echo "Siguientes pasos manuales:"
echo "1. Crear BD 'atestia_notaria' y usuario MySQL dedicado."
echo "2. Clonar repo en /var/www/atestia-notaria y copiar .env.production.example a .env"
echo "3. Copiar deploy/nginx-notaria.atestia.com.mx.conf a /etc/nginx/sites-available/notaria.atestia.com.mx y enlazar en sites-enabled."
echo "4. Copiar deploy/supervisor-atestia-worker.conf a /etc/supervisor/conf.d/"
echo "5. Ejecutar: certbot --nginx -d notaria.atestia.com.mx"
