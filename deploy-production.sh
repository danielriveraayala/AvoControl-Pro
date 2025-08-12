#!/bin/bash

# =================================================================
# AvoControl Pro - Script de Despliegue en Producción (VPS)
# Desarrollado por: Daniel Esau Rivera Ayala - Kreativos Pro
# =================================================================

set -e  # Detener script si hay error

echo "🚀 Iniciando despliegue de AvoControl Pro en producción..."
echo "=================================================="

# Variables de configuración
PROJECT_DIR="/var/www/avocontrol"
DOMAIN="your-domain.com"  # Cambiar por tu dominio
DB_NAME="avocontrol"
DB_USER="avocontrol_user"
DB_PASS="strong_password_here"  # Cambiar por contraseña segura

# =================================================================
# 1. ACTUALIZAR SISTEMA Y INSTALAR DEPENDENCIAS
# =================================================================
echo "📦 Actualizando sistema e instalando dependencias..."

sudo apt update && sudo apt upgrade -y

# Instalar PHP 8.3 y extensiones
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

sudo apt install -y \
    php8.3 \
    php8.3-fpm \
    php8.3-mysql \
    php8.3-mbstring \
    php8.3-xml \
    php8.3-curl \
    php8.3-zip \
    php8.3-bcmath \
    php8.3-intl \
    php8.3-gd \
    php8.3-redis \
    php8.3-cli

# Instalar Composer
if ! command -v composer &> /dev/null; then
    echo "📥 Instalando Composer..."
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    sudo chmod +x /usr/local/bin/composer
fi

# Instalar Node.js y npm
if ! command -v node &> /dev/null; then
    echo "📥 Instalando Node.js..."
    curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
    sudo apt install -y nodejs
fi

# Instalar Nginx
sudo apt install -y nginx

# Instalar MySQL
sudo apt install -y mysql-server

# Instalar Redis
echo "📦 Instalando Redis..."
sudo apt install -y redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server

# =================================================================
# 2. CONFIGURAR MYSQL
# =================================================================
echo "🗄️ Configurando base de datos MySQL..."

sudo mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME};"
sudo mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
sudo mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

# =================================================================
# 3. CONFIGURAR DIRECTORIO DEL PROYECTO
# =================================================================
echo "📁 Configurando directorio del proyecto..."

# Crear directorio si no existe
sudo mkdir -p $PROJECT_DIR
sudo chown -R $USER:www-data $PROJECT_DIR
sudo chmod -R 755 $PROJECT_DIR

# Navegar al directorio del proyecto
cd $PROJECT_DIR

# Si ya existe, hacer pull, si no, clonar
if [ -d ".git" ]; then
    echo "📥 Actualizando código desde repositorio..."
    git pull origin main
else
    echo "📥 Clonando repositorio..." 
    # Nota: Cambiar por tu repositorio
    echo "⚠️  Clonar manualmente desde tu repositorio Git"
    echo "git clone your-repo-url ."
fi

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
    cp .env.example .env
    
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

# Ejecutar migraciones y seeders
php artisan migrate --force
php artisan db:seed --force

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

# =================================================================
# 6. CONFIGURAR NGINX
# =================================================================
echo "🌐 Configurando Nginx..."

sudo tee /etc/nginx/sites-available/avocontrol << EOF
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN} www.${DOMAIN};
    root ${PROJECT_DIR}/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Optimizaciones de rendimiento
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|pdf)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Compresión gzip
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/javascript
        application/xml+rss
        application/json;
}
EOF

# Habilitar sitio
sudo ln -sf /etc/nginx/sites-available/avocontrol /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx

# =================================================================
# 7. CONFIGURAR SSL CON CERTBOT (LET'S ENCRYPT)
# =================================================================
echo "🔒 Configurando SSL con Let's Encrypt..."

# Instalar Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtener certificado SSL
sudo certbot --nginx -d ${DOMAIN} -d www.${DOMAIN} --non-interactive --agree-tos -m admin@${DOMAIN}

# Configurar renovación automática
sudo systemctl enable certbot.timer

# =================================================================
# 8. CONFIGURAR SERVICIOS Y WORKERS
# =================================================================
echo "🔧 Configurando servicios del sistema..."

# Supervisor para queue workers
sudo apt install -y supervisor

sudo tee /etc/supervisor/conf.d/avocontrol-worker.conf << EOF
[program:avocontrol-worker]
process_name=%(program_name)s_%(process_num)02d
command=php ${PROJECT_DIR}/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
directory=${PROJECT_DIR}
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=${PROJECT_DIR}/storage/logs/worker.log
stopwaitsecs=3600
EOF

sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start avocontrol-worker:*

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

# Verificar servicios
echo "📊 Estado de servicios:"
sudo systemctl status nginx --no-pager -l
sudo systemctl status mysql --no-pager -l  
sudo systemctl status redis-server --no-pager -l
sudo systemctl status php8.3-fpm --no-pager -l

# Verificar permisos
echo "📁 Verificando permisos..."
ls -la storage/
ls -la bootstrap/cache/

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