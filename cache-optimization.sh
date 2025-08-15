#!/bin/bash

# =================================================================
# AvoControl Pro - Optimización de Cache para Producción
# Desarrollado por: Daniel Esau Rivera Ayala - Kreativos Pro
# =================================================================

set -e  # Detener script si hay error

echo "⚡ Aplicando optimizaciones de cache en AvoControl Pro..."
echo "=================================================="

# Obtener directorio actual del proyecto
PROJECT_DIR=$(pwd)

# Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    echo "❌ Error: No se encontró el archivo 'artisan'. Ejecuta este script desde la raíz del proyecto Laravel."
    exit 1
fi

echo "📁 Directorio del proyecto: $PROJECT_DIR"

# =================================================================
# 1. LIMPIAR CACHES EXISTENTES
# =================================================================
echo "🧹 Limpiando caches existentes..."

php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

# Limpiar cache de Composer si existe
if command -v composer &> /dev/null; then
    composer clear-cache
fi

echo "✅ Caches limpiados exitosamente"

# =================================================================
# 2. CONFIGURAR REDIS EN .ENV (SI ESTÁ DISPONIBLE)
# =================================================================
echo "🔴 Verificando disponibilidad de Redis..."

if command -v redis-cli &> /dev/null && redis-cli ping > /dev/null 2>&1; then
    echo "✅ Redis detectado y funcionando"
    
    # Verificar si Redis está configurado en .env
    if grep -q "CACHE_DRIVER=file" .env; then
        echo "🔄 Actualizando configuración para usar Redis..."
        
        # Hacer backup del .env actual
        cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
        
        # Actualizar configuración para Redis
        sed -i.bak 's/CACHE_DRIVER=file/CACHE_DRIVER=redis/g' .env
        sed -i.bak 's/SESSION_DRIVER=file/SESSION_DRIVER=redis/g' .env
        sed -i.bak 's/QUEUE_CONNECTION=database/QUEUE_CONNECTION=redis/g' .env
        
        echo "✅ Configuración actualizada para usar Redis"
    else
        echo "ℹ️  Redis ya está configurado en .env"
    fi
else
    echo "⚠️  Redis no disponible, usando cache de archivos"
    
    # Asegurar que esté configurado para archivos
    if grep -q "CACHE_DRIVER=redis" .env; then
        echo "🔄 Revirtiendo a cache de archivos..."
        
        # Hacer backup del .env actual
        cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
        
        sed -i.bak 's/CACHE_DRIVER=redis/CACHE_DRIVER=file/g' .env
        sed -i.bak 's/SESSION_DRIVER=redis/SESSION_DRIVER=file/g' .env
        sed -i.bak 's/QUEUE_CONNECTION=redis/QUEUE_CONNECTION=database/g' .env
        
        echo "✅ Configuración actualizada para cache de archivos"
    fi
fi

# =================================================================
# 3. APLICAR OPTIMIZACIONES DE CACHE
# =================================================================
echo "⚡ Aplicando optimizaciones de producción..."

# Cachear configuraciones
echo "📝 Cacheando configuraciones..."
php artisan config:cache

# Cachear rutas
echo "🛣️  Cacheando rutas..."
php artisan route:cache

# Cachear vistas
echo "👁️  Cacheando vistas..."
php artisan view:cache

# Cachear eventos
echo "📡 Cacheando eventos..."
php artisan event:cache

# Optimizar Composer (solo si está disponible)
if command -v composer &> /dev/null; then
    echo "📦 Optimizando autoloader de Composer..."
    composer dump-autoload --optimize --no-dev
fi

# Optimización completa de Laravel
echo "🚀 Ejecutando optimización completa de Laravel..."
php artisan optimize

echo "✅ Todas las optimizaciones aplicadas exitosamente"

# =================================================================
# 4. VERIFICAR PERMISOS DE DIRECTORIOS CACHE
# =================================================================
echo "🔐 Verificando permisos de directorios..."

# Crear directorios de cache si no existen
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p bootstrap/cache

# Configurar permisos apropiados
if [[ "$OSTYPE" != "msys" && "$OSTYPE" != "win32" ]]; then
    # Solo en sistemas Unix/Linux
    chmod -R 775 storage/framework/cache
    chmod -R 775 storage/framework/sessions
    chmod -R 775 storage/framework/views
    chmod -R 775 bootstrap/cache
    
    # Si existe www-data (típico en servidores web)
    if id "www-data" &>/dev/null; then
        chown -R www-data:www-data storage/framework
        chown -R www-data:www-data bootstrap/cache
        echo "✅ Permisos configurados para www-data"
    else
        echo "ℹ️  Usuario www-data no encontrado, permisos básicos aplicados"
    fi
else
    echo "ℹ️  Sistema Windows detectado, omitiendo configuración de permisos Unix"
fi

# =================================================================
# 5. VERIFICACIONES FINALES
# =================================================================
echo "📊 Ejecutando verificaciones finales..."

# Verificar estado de los caches
echo "🔍 Verificando caches generados:"

if [ -f "bootstrap/cache/config.php" ]; then
    echo "✅ Config cache: Generado"
else
    echo "❌ Config cache: No generado"
fi

if [ -f "bootstrap/cache/routes-v7.php" ] || [ -f "bootstrap/cache/routes.php" ]; then
    echo "✅ Route cache: Generado"
else
    echo "❌ Route cache: No generado"
fi

if [ -f "bootstrap/cache/events.php" ]; then
    echo "✅ Event cache: Generado"
else
    echo "❌ Event cache: No generado"
fi

# Verificar tamaño de directorios cache
echo ""
echo "📁 Tamaños de directorios cache:"
du -sh storage/framework/cache 2>/dev/null || echo "storage/framework/cache: No disponible"
du -sh storage/framework/views 2>/dev/null || echo "storage/framework/views: No disponible"
du -sh bootstrap/cache 2>/dev/null || echo "bootstrap/cache: No disponible"

# Verificar conexión a Redis si está configurado
if grep -q "CACHE_DRIVER=redis" .env && command -v redis-cli &> /dev/null; then
    echo ""
    echo "🔴 Verificando conexión a Redis:"
    redis-cli ping && echo "✅ Redis responde correctamente" || echo "❌ Redis no responde"
fi

# Test básico de la aplicación
echo ""
echo "🧪 Verificando aplicación Laravel:"
php artisan --version
echo "✅ Laravel funcionando correctamente"

echo ""
echo "🎉 ¡OPTIMIZACIÓN COMPLETADA EXITOSAMENTE!"
echo "=================================================="
echo "⚡ Cache de configuración: Activado"
echo "🛣️  Cache de rutas: Activado"  
echo "👁️  Cache de vistas: Activado"
echo "📡 Cache de eventos: Activado"
echo "📦 Autoloader: Optimizado"

if grep -q "CACHE_DRIVER=redis" .env; then
    echo "🔴 Driver de cache: Redis"
    echo "💾 Sesiones: Redis"
    echo "📋 Colas: Redis"
else
    echo "💾 Driver de cache: Archivos"
    echo "💾 Sesiones: Archivos"
    echo "📋 Colas: Base de datos"
fi

echo ""
echo "📋 PRÓXIMOS PASOS:"
echo "1. Reiniciar servidor web si es necesario"
echo "2. Verificar funcionamiento de la aplicación"
echo "3. Monitorear logs: storage/logs/laravel.log"

if grep -q "CACHE_DRIVER=redis" .env; then
    echo "4. Para deshacer Redis: sed -i 's/CACHE_DRIVER=redis/CACHE_DRIVER=file/g' .env"
fi

echo ""
echo "🆘 SOPORTE:"
echo "Developer: Daniel Esau Rivera Ayala"
echo "Company: Kreativos Pro"
echo "=================================================="