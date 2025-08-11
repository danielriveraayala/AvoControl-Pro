# 🚀 AvoControl Pro - Guía de Deployment

## 📋 Información del Sistema

- **Dominio**: avocontrol.pro
- **Framework**: Laravel 12.x
- **Base de datos**: MySQL 8.0+
- **PHP**: 8.3+
- **Node**: 18+
- **SSL**: Let's Encrypt (automático)

## 📦 Preparación para Producción

### 1. Requisitos del Servidor

```bash
# Paquetes requeridos
sudo apt update
sudo apt install -y php8.3 php8.3-fpm php8.3-mysql php8.3-xml php8.3-curl php8.3-mbstring php8.3-zip php8.3-gd php8.3-intl
sudo apt install -y mysql-server nginx composer nodejs npm git
```

### 2. Configuración de Base de Datos

```sql
CREATE DATABASE avocontrol_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'avocontrol_user'@'localhost' IDENTIFIED BY 'PASSWORD_SEGURO_AQUI';
GRANT ALL PRIVILEGES ON avocontrol_prod.* TO 'avocontrol_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Deployment del Código

```bash
# Clonar repositorio
cd /var/www
sudo git clone https://github.com/TU_USUARIO/avocontrol-pro.git
cd avocontrol-pro

# Permisos
sudo chown -R www-data:www-data /var/www/avocontrol-pro
sudo chmod -R 755 /var/www/avocontrol-pro
sudo chmod -R 775 storage bootstrap/cache
```

### 4. Configuración de Entorno

```bash
# Copiar archivo de configuración
cp .env.production .env

# Editar variables importantes:
nano .env
```

**Variables críticas a configurar:**

```env
APP_KEY= # Generar nuevo con: php artisan key:generate
APP_URL=https://avocontrol.pro
APP_DEBUG=false

# Base de datos
DB_DATABASE=avocontrol_prod
DB_USERNAME=avocontrol_user
DB_PASSWORD=TU_PASSWORD_REAL

# Email (configurar con tu proveedor)
MAIL_HOST=smtp.hostinger.com
MAIL_USERNAME=avocontrol@avocontrol.pro
MAIL_PASSWORD=tu_password_real

# VAPID Keys (generar nuevas)
VAPID_PUBLIC_KEY= # Generar en: https://web-push-codelab.glitch.me/
VAPID_PRIVATE_KEY=
```

### 5. Instalación de Dependencias

```bash
# PHP dependencies
composer install --no-dev --optimize-autoloader

# JavaScript dependencies  
npm install
npm run production

# Generar clave de aplicación
php artisan key:generate

# Ejecutar migraciones
php artisan migrate --force

# Crear usuario admin
php artisan db:seed --class=UserSeeder --force

# Cache de optimización
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Configuración de Nginx

```nginx
# /etc/nginx/sites-available/avocontrol.pro
server {
    listen 80;
    server_name avocontrol.pro www.avocontrol.pro;
    
    root /var/www/avocontrol-pro/public;
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.ht {
        deny all;
    }
    
    # Service Worker
    location /sw.js {
        add_header Cache-Control "no-cache, no-store, must-revalidate";
        add_header Pragma "no-cache";
        add_header Expires "0";
    }
    
    # Push notifications headers
    location /push/ {
        add_header Access-Control-Allow-Origin "*";
        add_header Access-Control-Allow-Methods "GET, POST, OPTIONS";
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

### 7. SSL con Let's Encrypt

```bash
# Instalar Certbot
sudo apt install certbot python3-certbot-nginx

# Obtener certificado SSL
sudo certbot --nginx -d avocontrol.pro -d www.avocontrol.pro

# Verificar auto-renovación
sudo certbot renew --dry-run
```

### 8. Configuración de Cron Jobs

```bash
sudo crontab -e

# Agregar:
* * * * * cd /var/www/avocontrol-pro && php artisan schedule:run >> /dev/null 2>&1
```

### 9. Configuración del Queue Worker

```bash
# Crear servicio systemd
sudo nano /etc/systemd/system/avocontrol-queue.service
```

Contenido del archivo:

```ini
[Unit]
Description=AvoControl Queue Worker
After=redis.service

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/avocontrol-pro/artisan queue:work --sleep=3 --tries=3
RestartSec=3

[Install]
WantedBy=multi-user.target
```

```bash
# Activar servicio
sudo systemctl daemon-reload
sudo systemctl enable avocontrol-queue.service
sudo systemctl start avocontrol-queue.service
```

## 🔧 Configuración de Push Notifications

### 1. Generar Claves VAPID

Visita: https://web-push-codelab.glitch.me/

O ejecuta:
```bash
# Si instalaste web-push globalmente
web-push generate-vapid-keys
```

### 2. Configurar en .env

```env
VAPID_PUBLIC_KEY=TU_CLAVE_PUBLICA_DE_88_CARACTERES
VAPID_PRIVATE_KEY=TU_CLAVE_PRIVADA_DE_44_CARACTERES
VAPID_SUBJECT=mailto:avocontrol@avocontrol.pro
```

### 3. Verificar Funcionamiento

1. Ve a: https://avocontrol.pro/configuration
2. Activa las notificaciones push
3. Haz click en "Probar Notificación"
4. ¡Deberías ver la notificación!

## 🔄 Actualizaciones

```bash
# Proceso de actualización
cd /var/www/avocontrol-pro
git pull origin main
composer install --no-dev --optimize-autoloader
npm run production
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl restart avocontrol-queue.service
```

## 🚨 Troubleshooting

### Push Notifications no funcionan:
- Verificar claves VAPID en .env
- Comprobar permisos SSL (HTTPS requerido)
- Revisar logs: `tail -f storage/logs/laravel.log`

### Errores de permisos:
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Base de datos no conecta:
- Verificar credenciales en .env
- Comprobar que MySQL esté ejecutándose
- Verificar firewall

## 📞 Soporte

- Email: soporte@avocontrol.pro
- Logs: `/var/www/avocontrol-pro/storage/logs/laravel.log`
- Status: `systemctl status avocontrol-queue.service`

---

¡El sistema está listo para producción con push notifications totalmente funcionales! 🎉