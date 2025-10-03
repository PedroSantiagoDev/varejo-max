#!/bin/bash
# start.sh - Script de inicialização para o Railway

# Define permissões corretas
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Executa migrations
php artisan migrate --force

# Limpa e otimiza caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Cria link simbólico do storage (se não existir)
php artisan storage:link

# Inicia o servidor web e o queue worker em background
php artisan queue:work --daemon --tries=3 --timeout=90 &

# Inicia o servidor web
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}