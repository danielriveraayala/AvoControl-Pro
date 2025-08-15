# AvoControl Pro - Guía de Despliegue en Producción

## 📋 Instrucciones para VPS Ubuntu/Debian

### 🚀 Despliegue Automático

1. **Subir el script al servidor**:
```bash
scp deploy-production.sh user@your-server:/tmp/
```

2. **Conectar al servidor**:
```bash
ssh user@your-server
```

3. **Ejecutar el script**:
```bash
cd /tmp
chmod +x deploy-production.sh

# IMPORTANTE: Editar variables antes de ejecutar
nano deploy-production.sh

# Cambiar estas variables:
DOMAIN="your-domain.com"          # Tu dominio
DB_PASS="strong_password_here"    # Contraseña segura

# Ejecutar el script
sudo ./deploy-production.sh
```

### ⚙️ Variables a Configurar

**Antes de ejecutar, edita estas variables en el script:**

```bash
DOMAIN="your-domain.com"                    # Tu dominio
DB_NAME="avocontrol"                       # Nombre de la base de datos  
DB_USER="avocontrol_user"                  # Usuario de la base de datos
DB_PASS="strong_password_here"             # ⚠️ CAMBIAR por contraseña segura
```

### 📦 Lo que incluye el script

#### ✅ Instalaciones:
- PHP 8.3 + extensiones necesarias
- Composer (gestor de dependencias PHP)
- Node.js 18 + npm
- Nginx (servidor web)
- MySQL 8.0 (base de datos)
- Redis (cache y sesiones)
- Supervisor (gestión de procesos)
- Certbot (SSL gratuito)

#### ⚡ Optimizaciones:
- Config/Route/View/Event cache
- Autoloader optimizado  
- Compresión gzip
- Headers de seguridad
- Cache de archivos estáticos
- Queue workers automáticos
- Tareas CRON programadas

#### 🔒 Seguridad:
- SSL/HTTPS automático con Let's Encrypt
- Firewall UFW configurado
- Headers de seguridad
- Permisos de archivos correctos
- Usuario www-data para Laravel

### 🌐 Configuración Post-Despliegue

#### 1. **Configurar DNS**:
Apuntar tu dominio a la IP del servidor:
```
A     @           123.456.789.123
A     www         123.456.789.123
```

#### 2. **Verificar funcionamiento**:
```bash
# Verificar servicios
sudo systemctl status nginx mysql redis-server

# Verificar workers
sudo supervisorctl status

# Ver logs
tail -f /var/www/avocontrol/storage/logs/laravel.log
```

#### 3. **Acceso inicial**:
- **URL**: https://your-domain.com
- **Admin**: admin@avocontrol.com / password123
- **Vendedor**: vendedor@avocontrol.com / password123
- **Contador**: contador@avocontrol.com / password123

### 🔧 Comandos útiles post-instalación

```bash
# Actualizar aplicación después de push
cd /var/www/avocontrol
git pull origin main
composer install --optimize-autoloader --no-dev
npm run production
php artisan migrate --force
php artisan optimize
sudo systemctl reload nginx

# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Verificar workers
sudo supervisorctl restart avocontrol-worker:*

# Renovar SSL (automático, pero manual si necesario)
sudo certbot renew

# Limpiar cache
php artisan cache:clear
```

### 📊 Monitoreo

**Archivos de logs importantes:**
- Laravel: `/var/www/avocontrol/storage/logs/laravel.log`
- Nginx: `/var/log/nginx/error.log`
- MySQL: `/var/log/mysql/error.log`
- Workers: `/var/www/avocontrol/storage/logs/worker.log`

**Servicios a monitorear:**
```bash
sudo systemctl status nginx mysql redis-server php8.3-fpm supervisor
```

### ⚠️ Troubleshooting

#### Problema: Error 500
```bash
# Verificar permisos
sudo chown -R www-data:www-data /var/www/avocontrol/storage
sudo chown -R www-data:www-data /var/www/avocontrol/bootstrap/cache

# Verificar logs
tail -f /var/www/avocontrol/storage/logs/laravel.log
```

#### Problema: Queue no procesa
```bash
# Reiniciar workers  
sudo supervisorctl restart avocontrol-worker:*

# Verificar estado
sudo supervisorctl status
```

#### Problema: SSL no funciona
```bash
# Verificar certificado
sudo certbot certificates

# Renovar manualmente
sudo certbot renew --force-renewal
```

### 📞 Soporte

**Desarrollador**: Daniel Esau Rivera Ayala  
**Empresa**: Kreativos Pro - Agencia de Marketing Digital y Desarrollo Web  
**Ubicación**: Morelia, México  
**Sitio web**: [about.me/danielriveraayala](https://about.me/danielriveraayala)

---

## ⚡ TL;DR - Despliegue Rápido

```bash
# 1. Subir script al servidor
scp deploy-production.sh user@server:/tmp/

# 2. Conectar y configurar
ssh user@server
cd /tmp
chmod +x deploy-production.sh
nano deploy-production.sh  # Cambiar DOMAIN y DB_PASS

# 3. Ejecutar
sudo ./deploy-production.sh

# 4. Configurar DNS hacia la IP del servidor
# 5. Acceder: https://your-domain.com
```

¡Listo! AvoControl Pro estará funcionando en producción con todas las optimizaciones.