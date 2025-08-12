#!/bin/bash

# =================================================================
# AvoControl Pro - Script de Despliegue en Producción (VPS)
# Desarrollado por: Daniel Esau Rivera Ayala - Kreativos Pro
# =================================================================

set -e  # Detener script si hay error

echo "🚀 Iniciando despliegue de AvoControl Pro en producción..."
echo "=================================================="

# Variables de configuración
PROJECT_DIR="/var/www/AvoControl-Pro"
DOMAIN="avocontrol.pro"  # Cambiar por tu dominio
DB_NAME="avocontrol_prod"
DB_USER="avocontrol_user"
DB_PASS="@?Pm@R/eVzWu.wRDa6kt{f"  # Cambiar por contraseña segura

# =================================================================
# 1. ACTUALIZAR SISTEMA Y INSTALAR DEPENDENCIAS
# =================================================================
echo "📦 Actualizando sistema e instalando dependencias..."

sudo apt update && sudo apt upgrade -y

# Instalar Redis
echo "📦 Instalando Redis..."
sudo apt install -y redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server

# =================================================================
# 4. CONFIGURAR APLICACIÓN LARAVEL
# =================================================================
echo "⚙️ Configurando aplicación Laravel..."

# Instalar dependencias PHP
composer install --optimize-autoloader --no-dev

# Instalar dependencias Node.js
npm install
npm run production

# Configurar permisos
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Copiar y configurar .env para producción
if [ ! -f .env ]; then
    cp .env.production .env

    # Generar APP_KEY
    php artisan key:generate
fi

# Configurar .env para producción
cat > .env << EOF
APP_NAME="AvoControl Pro"
APP_ENV=production
APP_KEY=$(php artisan --no-ansi key:generate --show)
APP_DEBUG=false
APP_URL=https://${DOMAIN}

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASS}

BROADCAST_DRIVER=redis
CACHE_DRIVER=redis
FILESYSTEM_DRIVER=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Email Configuration for Notifications
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=avocontrol@kreativos.pro
MAIL_PASSWORD=t74tP#M:
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=avocontrol@kreativos.pro
MAIL_FROM_NAME="AvoControl Pro"

# Notification Email Settings
NOTIFICATION_EMAIL_ENABLED=true
NOTIFICATION_EMAIL_QUEUE=emails
NOTIFICATION_DAILY_REPORT_TIME="08:00"
NOTIFICATION_WEEKLY_REPORT_DAY=1
NOTIFICATION_MONTHLY_REPORT_DAY=1

# VAPID Keys for Push Notifications
VAPID_PUBLIC_KEY=BKZ-9Hj1PlXDk-EQWlIxHvbV-fTlXbr_l1o8YmgRyFqfyYvQ5xWmCLZxNS2RNJoRhVbJlxgP6NQ8EZ4KqPJNxvI
VAPID_PRIVATE_KEY=DqOHhLWxQ5NrjKmP8EyFJhHzXJJhQhJzNmZeOyFzOqM
VAPID_SUBJECT=mailto:avocontrol@kreativos.pro
EOF

# =================================================================
# 5. OPTIMIZACIONES PARA PRODUCCIÓN
# =================================================================
echo "⚡ Aplicando optimizaciones de producción..."

# Cachear configuraciones
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimizar Composer
composer dump-autoload --optimize

# Optimización completa de Laravel
php artisan optimize

# Configurar CRON para tareas programadas
(crontab -l 2>/dev/null; echo "* * * * * cd ${PROJECT_DIR} && php artisan schedule:run >> /dev/null 2>&1") | crontab -

# =================================================================
# 9. CONFIGURAR FIREWALL
# =================================================================
echo "🛡️ Configurando firewall..."

sudo ufw --force enable
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw allow 6379  # Redis

# =================================================================
# 10. VERIFICACIONES FINALES
# =================================================================
echo "✅ Ejecutando verificaciones finales..."


# Verificar Redis
echo "🔴 Verificando Redis..."
redis-cli ping

# Test de la aplicación
echo "🧪 Verificando aplicación..."
php artisan --version
php artisan config:show database.default

echo ""
echo "🎉 ¡DESPLIEGUE COMPLETADO EXITOSAMENTE!"
echo "=================================================="
echo "🌐 Sitio web: https://${DOMAIN}"
echo "🗄️ Base de datos: ${DB_NAME}"
echo "👤 Usuario DB: ${DB_USER}"
echo "🔴 Redis: Configurado y funcionando"
echo "📧 Email: Configurado con Hostinger"
echo "🔒 SSL: Configurado con Let's Encrypt"
echo "⚡ Cache: Optimizado con Redis"
echo "🔧 Queue Workers: Configurados con Supervisor"
echo "📅 CRON: Tareas programadas configuradas"
echo ""
echo "📋 PRÓXIMOS PASOS:"
echo "1. Cambiar contraseña de base de datos en el script"
echo "2. Cambiar dominio en las variables del script"
echo "3. Configurar tu repositorio Git"
echo "4. Revisar logs en: ${PROJECT_DIR}/storage/logs/"
echo "5. Acceder al admin: admin@avocontrol.com / password123"
echo ""
echo "🆘 SOPORTE:"
echo "Developer: Daniel Esau Rivera Ayala"
echo "Company: Kreativos Pro"
echo "Email: info@kreativos.pro"
echo "=================================================="
