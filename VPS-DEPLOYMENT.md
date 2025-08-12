# 🚀 AvoControl Pro - Deployment en VPS

## 📋 Guía de Deployment para Servidor VPS

Esta guía contiene todas las instrucciones para deployar AvoControl Pro en un servidor VPS de producción.

---

## 🔧 Requisitos del Servidor

### Software Requerido:
- **Ubuntu 20.04 LTS** o superior
- **PHP 8.2+** con extensiones: `php-mysql`, `php-xml`, `php-zip`, `php-curl`, `php-mbstring`, `php-gd`, `php-json`
- **MySQL 8.0+** o **MariaDB 10.5+**
- **Nginx** (servidor web recomendado)
- **Node.js 18+** y **npm**
- **Composer** (gestor de dependencias PHP)
- **Git** para control de versiones

### Recursos Mínimos:
- **RAM**: 2GB (recomendado 4GB+)
- **Almacenamiento**: 20GB (recomendado 50GB+)
- **CPU**: 2 cores
- **Conexión**: Estable a internet

---

## 🛠️ Preparación del Servidor

### 1. Instalación de Dependencias

```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar PHP y extensiones
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-zip \
    php8.2-curl php8.2-mbstring php8.2-gd php8.2-json php8.2-bcmath \
    php8.2-intl php8.2-cli

# Instalar MySQL
sudo apt install -y mysql-server

# Instalar Nginx
sudo apt install -y nginx

# Instalar Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Instalar Git
sudo apt install -y git
```

### 2. Configuración de MySQL

```bash
# Configurar MySQL
sudo mysql_secure_installation

# Crear base de datos y usuario
sudo mysql -u root -p
```

```sql
CREATE DATABASE avocontrol;
CREATE USER 'avocontrol'@'localhost' IDENTIFIED BY 'tu_password_segura';
GRANT ALL PRIVILEGES ON avocontrol.* TO 'avocontrol'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Configuración del Proyecto

```bash
# Crear directorio del proyecto
sudo mkdir -p /var/www/avocontrol
sudo chown -R $USER:$USER /var/www/avocontrol

# Clonar repositorio
cd /var/www
git clone https://github.com/tu-usuario/avocontrol-pro.git avocontrol
cd avocontrol

# Copiar archivo de configuración
cp .env.example .env
```

### 4. Configuración del Archivo `.env`

Edita `/var/www/avocontrol/.env`:

```env
APP_NAME="AvoControl Pro"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://tu-dominio.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=avocontrol
DB_USERNAME=avocontrol
DB_PASSWORD=tu_password_segura

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Configuración de email (completar después del deployment)
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@avocontrol.com"
MAIL_FROM_NAME="AvoControl Pro"
```

---

## 🚀 Proceso de Deployment

### Opción A: Deployment Automático (Recomendado)

1. **Hacer el script ejecutable:**
```bash
chmod +x /var/www/avocontrol/deploy-vps.sh
```

2. **Ejecutar el script de deployment:**
```bash
sudo /var/www/avocontrol/deploy-vps.sh
```

### Opción B: Deployment Manual

Si prefieres hacer el deployment paso a paso:

```bash
# 1. Instalar dependencias de Composer
cd /var/www/avocontrol
composer install --optimize-autoloader --no-dev

# 2. Instalar dependencias de Node
npm ci --production
npm run build

# 3. Generar clave de aplicación
php artisan key:generate

# 4. Ejecutar migraciones
php artisan migrate --force

# 5. Ejecutar seeders de producción
php artisan db:seed --class=VpsProductionSeeder

# 6. Configurar permisos
sudo chown -R www-data:www-data /var/www/avocontrol
sudo chmod -R 755 /var/www/avocontrol
sudo chmod -R 775 /var/www/avocontrol/storage /var/www/avocontrol/bootstrap/cache

# 7. Limpiar y cachear configuraciones
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🌐 Configuración de Nginx

Crear archivo de configuración: `/etc/nginx/sites-available/avocontrol`

```nginx
server {
    listen 80;
    server_name tu-dominio.com www.tu-dominio.com;
    root /var/www/avocontrol/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Seguridad adicional
    location ~ /\.ht {
        deny all;
    }

    location ~ /\.git {
        deny all;
    }

    # Optimización para archivos estáticos
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Habilitar el sitio:

```bash
sudo ln -s /etc/nginx/sites-available/avocontrol /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---

## 🔒 Certificado SSL (Recomendado)

### Usando Let's Encrypt (Gratis):

```bash
# Instalar Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtener certificado
sudo certbot --nginx -d tu-dominio.com -d www.tu-dominio.com

# Verificar renovación automática
sudo certbot renew --dry-run
```

---

## 👥 Usuarios Creados por Defecto

El seeder de producción crea estos usuarios:

| Rol | Email | Contraseña | Descripción |
|-----|-------|------------|-------------|
| **Developer** | `developer@avocontrol.com` | `password123` | Super administrador con acceso al panel de desarrollador |
| **Admin** | `admin@avocontrol.com` | `password123` | Administrador principal del sistema |
| **Vendedor** | `vendedor@avocontrol.com` | `password123` | Usuario vendedor para operaciones |

**⚠️ IMPORTANTE:** Cambiar todas las contraseñas después del primer login.

---

## ⚙️ Configuración Post-Deployment

### 1. Configuración SMTP
1. Ir a `/developer/config/smtp`
2. Configurar servidor de email
3. Probar envío de emails

### 2. Configuración de Empresa
1. Ir a `Configuración → Empresa`
2. Completar datos de la empresa
3. Subir logo si es necesario

### 3. Configuración de Calidades
1. Verificar calidades predefinidas en `Configuración → Calidades`
2. Ajustar según necesidades específicas

### 4. Cambiar Contraseñas
1. Cada usuario debe cambiar su contraseña
2. Ir a `Perfil → Cambiar Contraseña`

---

## 📊 Monitoreo y Mantenimiento

### Logs Importantes:
- **Laravel**: `/var/www/avocontrol/storage/logs/laravel.log`
- **Nginx**: `/var/log/nginx/access.log` y `/var/log/nginx/error.log`
- **PHP**: `/var/log/php8.2-fpm.log`
- **MySQL**: `/var/log/mysql/error.log`

### Comandos de Mantenimiento:

```bash
# Limpiar logs antiguos
sudo find /var/www/avocontrol/storage/logs -name "*.log" -mtime +30 -delete

# Backup de base de datos
mysqldump -u avocontrol -p avocontrol > backup_$(date +%Y%m%d).sql

# Verificar espacio en disco
df -h

# Ver procesos de PHP
sudo systemctl status php8.2-fpm

# Ver estado de Nginx
sudo systemctl status nginx
```

### Actualizaciones:

Para actualizar el sistema:

```bash
cd /var/www/avocontrol
git pull origin main
sudo ./deploy-vps.sh
```

---

## 🆘 Troubleshooting

### Problema: Error 500
**Solución:**
```bash
sudo chmod -R 775 /var/www/avocontrol/storage
sudo chown -R www-data:www-data /var/www/avocontrol/storage
php artisan config:clear
```

### Problema: No se cargan los assets
**Solución:**
```bash
cd /var/www/avocontrol
npm run build
php artisan view:clear
```

### Problema: Error de conexión a BD
**Verificar:**
- Credenciales en `.env`
- Servicio MySQL activo: `sudo systemctl status mysql`
- Permisos del usuario de BD

### Problema: Emails no se envían
**Verificar:**
- Configuración SMTP en `/developer/config/smtp`
- Puerto del servidor (587/465)
- Credenciales del email

---

## 📞 Soporte

**Desarrollador:** Daniel Esau Rivera Ayala  
**Empresa:** Kreativos Pro  
**Email:** daniel@kreativos.pro  
**Sitio Web:** [kreativos.pro](https://kreativos.pro)

---

### 4. Configuración de Notificaciones Push
1. Ir a `/developer/config/notifications`
2. Generar llaves VAPID automáticamente
3. Configurar subject de notificaciones
4. Probar funcionamiento del sistema

### 5. Verificación de Funcionalidades
1. **Panel de Desarrollador**: Acceder a `/developer` con credenciales de desarrollador
2. **Notificaciones Push**: Probar desde `/configuration` (tab Push Notifications)
3. **Service Worker**: Verificar que `/sw.js` esté accesible
4. **HTTPS**: Confirmar que las notificaciones funcionen solo con HTTPS

---

## 🔔 Sistema de Notificaciones Push

### Funcionalidades Incluidas:
- **Service Worker nativo** (`/sw.js`) con soporte completo para push notifications
- **Panel de desarrollador** para configuración técnica de VAPID
- **Interface de usuario simplificada** en configuración regular
- **Tipos de notificaciones**: Inventario bajo, pagos pendientes, nuevos lotes, reportes
- **Gestión de subscripciones** por dispositivo/navegador
- **Compatibilidad multi-navegador** con detección automática

### Rutas Importantes:
- `/developer/config/notifications` - Configuración VAPID (solo desarrolladores)
- `/developer/config/vapid` - Gestión de llaves VAPID
- `/configuration` - Suscripción de usuarios (tab Push Notifications)
- `/sw.js` - Service Worker (debe ser accesible públicamente)

### Requisitos Técnicos:
- **HTTPS obligatorio** para notificaciones push
- **Service Worker** registrado automáticamente
- **Llaves VAPID** generadas y configuradas
- **Extensión push** de minishlink/web-push instalada via Composer

---

## 📝 Changelog

- **v1.0** - Deployment inicial con todas las funcionalidades core
- **v1.1** - Sistema de notificaciones y panel de desarrollador
- **v1.2** - Mejoras en SMTP y configuraciones
- **v1.3** - Sistema completo de notificaciones push nativas
  - Service Worker implementado con tipos de notificaciones personalizadas
  - Panel de desarrollador con gestión completa de VAPID keys
  - Interface de usuario simplificada para suscripciones
  - Separación clara entre configuración técnica y uso regular
  - Soporte completo para HTTPS y compatibilidad multi-navegador

---

**¡Deployment exitoso! 🎉**