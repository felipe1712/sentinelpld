#!/usr/bin/env bash
set -euo pipefail

APP_DIR="/var/www/atestia-notaria"
cd "$APP_DIR"

echo ">>> Bajando ultimos cambios..."
git pull origin PLD

echo ">>> Instalando dependencias PHP y Node..."
composer install --no-dev --optimize-autoloader
npm ci
npm run build

echo ">>> Ejecutando migraciones y optimizando caches..."
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ">>> Reiniciando workers..."
supervisorctl reread
supervisorctl update
supervisorctl restart atestia-notaria-worker:*

echo ">>> Deploy completado para notaria.atestia.com.mx"
