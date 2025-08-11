#!/bin/bash

# 🚀 AvoControl Pro - Deployment Script
# Uso: bash deploy.sh

set -e

echo "🚀 Iniciando deployment de AvoControl Pro..."
echo "============================================="

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Función para logging
log() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
    exit 1
}

# Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    error "No se encontró el archivo artisan. ¿Estás en el directorio del proyecto?"
fi

# 1. Git pull
log "Actualizando código desde repositorio..."
git pull origin main || error "Error al hacer git pull"

# 2. Modo mantenimiento
log "Activando modo de mantenimiento..."
php artisan down || warn "No se pudo activar modo de mantenimiento"

# 3. Instalar dependencias
log "Instalando dependencias de PHP..."
composer install --no-dev --optimize-autoloader || error "Error instalando dependencias PHP"

log "Instalando dependencias de Node.js..."
npm install || error "Error instalando dependencias Node"

# 4. Compilar assets
log "Compilando assets para producción..."
npm run production || error "Error compilando assets"

# 5. Limpiar y cachear configuración
log "Limpiando caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

log "Creando caches optimizados..."
php artisan config:cache || warn "Error cacheando configuración"
php artisan route:cache || warn "Error cacheando rutas"
php artisan view:cache || warn "Error cacheando vistas"

# 6. Ejecutar migraciones
log "Ejecutando migraciones de base de datos..."
php artisan migrate --force || error "Error en migraciones"

# 7. Reiniciar queue worker si existe
log "Reiniciando queue workers..."
if systemctl is-active --quiet avocontrol-queue; then
    sudo systemctl restart avocontrol-queue
    log "Queue worker reiniciado"
else
    warn "Queue worker no encontrado"
fi

# 8. Optimizar autoload
log "Optimizando autoloader..."
composer dump-autoload --optimize || warn "Error optimizando autoloader"

# 9. Verificar permisos
log "Verificando permisos..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# 10. Salir del modo mantenimiento
log "Desactivando modo de mantenimiento..."
php artisan up

# 11. Verificación final
log "Realizando verificación final..."

# Verificar que la app responda
if curl -s --head https://avocontrol.pro | head -n 1 | grep -q "200 OK"; then
    log "✅ Aplicación respondiendo correctamente"
else
    warn "⚠️  La aplicación podría no estar respondiendo correctamente"
fi

# Verificar push notifications
log "Verificando configuración de push notifications..."
if grep -q "VAPID_PUBLIC_KEY=TU_CLAVE" .env; then
    warn "⚠️  Recuerda configurar las claves VAPID reales"
else
    log "✅ Claves VAPID configuradas"
fi

echo ""
echo "============================================="
log "🎉 ¡Deployment completado exitosamente!"
echo ""
log "URLs importantes:"
log "  • Aplicación: https://avocontrol.pro"
log "  • Push config: https://avocontrol.pro/configuration"
echo ""
log "Para verificar logs:"
log "  tail -f storage/logs/laravel.log"
echo ""
log "Para verificar queue worker:"
log "  systemctl status avocontrol-queue"
echo "============================================="