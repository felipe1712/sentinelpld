#!/usr/bin/env bash
set -euo pipefail

APP_DIR="/var/www/sentinelpld"
cd "$APP_DIR"

echo ">>> Bajando ultimos cambios..."
git pull origin main

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
supervisorctl restart sentinelpld-worker:*

echo ">>> Deploy completado para sentinelpld.mx"
