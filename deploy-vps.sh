#!/bin/bash

# =============================================================================
# AvoControl Pro - VPS Deployment Script
# =============================================================================
# Script para deployment en servidor VPS de producción
# Autor: Daniel Esau Rivera Ayala - Kreativos Pro
# =============================================================================

set -e  # Exit on any error

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuración
PROJECT_DIR="/var/www/avocontrol"
BACKUP_DIR="/var/www/backups"
LOG_FILE="/var/log/avocontrol-deploy.log"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")

echo -e "${BLUE}==============================================================================${NC}"
echo -e "${BLUE}              AvoControl Pro - VPS Deployment Script${NC}"
echo -e "${BLUE}==============================================================================${NC}"

# Función para logging
log() {
    echo -e "$1"
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> $LOG_FILE
}

# Verificar que estamos ejecutando como root o con sudo
check_permissions() {
    if [[ $EUID -ne 0 ]]; then
       log "${RED}❌ Este script debe ejecutarse como root o con sudo${NC}"
       exit 1
    fi
}

# Backup de la base de datos actual
backup_database() {
    log "${YELLOW}📦 Creando backup de la base de datos...${NC}"
    
    mkdir -p $BACKUP_DIR
    
    # Backup de MySQL (ajustar credenciales según configuración)
    mysqldump -u root -p avocontrol > "$BACKUP_DIR/avocontrol_backup_$TIMESTAMP.sql"
    
    if [[ $? -eq 0 ]]; then
        log "${GREEN}✅ Backup de base de datos creado exitosamente${NC}"
        # Comprimir el backup
        gzip "$BACKUP_DIR/avocontrol_backup_$TIMESTAMP.sql"
        log "${GREEN}✅ Backup comprimido: avocontrol_backup_$TIMESTAMP.sql.gz${NC}"
    else
        log "${RED}❌ Error al crear backup de la base de datos${NC}"
        exit 1
    fi
}

# Backup de archivos del proyecto
backup_files() {
    log "${YELLOW}📦 Creando backup de archivos del proyecto...${NC}"
    
    if [ -d "$PROJECT_DIR" ]; then
        tar -czf "$BACKUP_DIR/avocontrol_files_backup_$TIMESTAMP.tar.gz" \
            -C "$(dirname $PROJECT_DIR)" "$(basename $PROJECT_DIR)" \
            --exclude='node_modules' \
            --exclude='vendor' \
            --exclude='storage/logs/*' \
            --exclude='storage/framework/cache/*' \
            --exclude='storage/framework/sessions/*' \
            --exclude='storage/framework/views/*'
        
        log "${GREEN}✅ Backup de archivos creado: avocontrol_files_backup_$TIMESTAMP.tar.gz${NC}"
    fi
}

# Actualizar código desde Git
update_code() {
    log "${YELLOW}🔄 Actualizando código desde Git...${NC}"
    
    cd $PROJECT_DIR
    
    # Hacer stash de cambios locales si los hay
    git stash
    
    # Pull del repositorio
    git pull origin main
    
    if [[ $? -eq 0 ]]; then
        log "${GREEN}✅ Código actualizado desde Git${NC}"
    else
        log "${RED}❌ Error al actualizar código desde Git${NC}"
        exit 1
    fi
}

# Instalar/actualizar dependencias de Composer
update_composer_dependencies() {
    log "${YELLOW}📚 Actualizando dependencias de Composer...${NC}"
    
    cd $PROJECT_DIR
    
    # Instalar Composer si no existe
    if ! command -v composer &> /dev/null; then
        log "${YELLOW}📦 Instalando Composer...${NC}"
        curl -sS https://getcomposer.org/installer | php
        mv composer.phar /usr/local/bin/composer
        chmod +x /usr/local/bin/composer
    fi
    
    # Actualizar dependencias
    composer install --optimize-autoloader --no-dev
    
    if [[ $? -eq 0 ]]; then
        log "${GREEN}✅ Dependencias de Composer actualizadas${NC}"
    else
        log "${RED}❌ Error al actualizar dependencias de Composer${NC}"
        exit 1
    fi
}

# Instalar/actualizar dependencias de Node.js
update_node_dependencies() {
    log "${YELLOW}📦 Actualizando dependencias de Node.js...${NC}"
    
    cd $PROJECT_DIR
    
    # Verificar si Node.js está instalado
    if ! command -v node &> /dev/null; then
        log "${RED}❌ Node.js no está instalado. Por favor instálalo primero.${NC}"
        exit 1
    fi
    
    # Instalar dependencias
    npm ci --production
    
    # Compilar assets
    npm run build
    
    if [[ $? -eq 0 ]]; then
        log "${GREEN}✅ Dependencias de Node.js actualizadas y assets compilados${NC}"
    else
        log "${RED}❌ Error al actualizar dependencias de Node.js${NC}"
        exit 1
    fi
}

# Ejecutar migraciones
run_migrations() {
    log "${YELLOW}🔄 Ejecutando migraciones de base de datos...${NC}"
    
    cd $PROJECT_DIR
    
    # Ejecutar migraciones
    php artisan migrate --force
    
    if [[ $? -eq 0 ]]; then
        log "${GREEN}✅ Migraciones ejecutadas exitosamente${NC}"
    else
        log "${RED}❌ Error al ejecutar migraciones${NC}"
        exit 1
    fi
}

# Ejecutar seeders para producción
run_production_seeders() {
    log "${YELLOW}🌱 Ejecutando seeders de producción...${NC}"
    
    cd $PROJECT_DIR
    
    # Solo ejecutar seeders esenciales para producción
    php artisan db:seed --class=VpsProductionSeeder
    
    if [[ $? -eq 0 ]]; then
        log "${GREEN}✅ Seeders de producción ejecutados${NC}"
    else
        log "${RED}❌ Error al ejecutar seeders de producción${NC}"
        exit 1
    fi
}

# Configurar permisos
set_permissions() {
    log "${YELLOW}🔐 Configurando permisos de archivos...${NC}"
    
    cd $PROJECT_DIR
    
    # Propietario correcto
    chown -R www-data:www-data $PROJECT_DIR
    
    # Permisos de directorios
    find $PROJECT_DIR -type d -exec chmod 755 {} \;
    
    # Permisos de archivos
    find $PROJECT_DIR -type f -exec chmod 644 {} \;
    
    # Permisos especiales para storage y bootstrap/cache
    chmod -R 775 storage bootstrap/cache
    chown -R www-data:www-data storage bootstrap/cache
    
    log "${GREEN}✅ Permisos configurados correctamente${NC}"
}

# Limpiar cache de Laravel
clear_cache() {
    log "${YELLOW}🧹 Limpiando cache de Laravel...${NC}"
    
    cd $PROJECT_DIR
    
    # Limpiar diferentes tipos de cache
    php artisan config:clear
    php artisan cache:clear
    php artisan view:clear
    php artisan route:clear
    
    # Regenerar cache de configuración y rutas para producción
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    log "${GREEN}✅ Cache de Laravel limpiado y regenerado${NC}"
}

# Reiniciar servicios
restart_services() {
    log "${YELLOW}🔄 Reiniciando servicios...${NC}"
    
    # Reiniciar PHP-FPM
    systemctl restart php8.2-fpm  # Ajustar versión según sea necesario
    
    # Reiniciar Nginx
    systemctl restart nginx
    
    # Verificar que los servicios estén ejecutándose
    if systemctl is-active --quiet php8.2-fpm && systemctl is-active --quiet nginx; then
        log "${GREEN}✅ Servicios reiniciados correctamente${NC}"
    else
        log "${RED}❌ Error al reiniciar servicios${NC}"
        exit 1
    fi
}

# Verificar estado del deployment
verify_deployment() {
    log "${YELLOW}🔍 Verificando estado del deployment...${NC}"
    
    # Verificar que la aplicación responda
    HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost)
    
    if [[ $HTTP_STATUS -eq 200 || $HTTP_STATUS -eq 302 ]]; then
        log "${GREEN}✅ Aplicación responde correctamente${NC}"
    else
        log "${RED}❌ La aplicación no responde correctamente (HTTP $HTTP_STATUS)${NC}"
        exit 1
    fi
    
    # Verificar conexión a base de datos
    cd $PROJECT_DIR
    php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connection OK';"
    
    if [[ $? -eq 0 ]]; then
        log "${GREEN}✅ Conexión a base de datos OK${NC}"
    else
        log "${RED}❌ Error en conexión a base de datos${NC}"
        exit 1
    fi
}

# Mostrar información final
show_final_info() {
    log "${GREEN}==============================================================================${NC}"
    log "${GREEN}🎉 ¡Deployment completado exitosamente!${NC}"
    log "${GREEN}==============================================================================${NC}"
    echo ""
    log "${BLUE}📁 Directorio del proyecto: $PROJECT_DIR${NC}"
    log "${BLUE}📦 Backups guardados en: $BACKUP_DIR${NC}"
    log "${BLUE}📝 Log del deployment: $LOG_FILE${NC}"
    echo ""
    log "${YELLOW}🔗 Credenciales de acceso:${NC}"
    log "${YELLOW}   - Developer: developer@avocontrol.com / password123${NC}"
    log "${YELLOW}   - Admin: admin@avocontrol.com / password123${NC}"
    log "${YELLOW}   - Vendedor: vendedor@avocontrol.com / password123${NC}"
    echo ""
    log "${BLUE}⚙️  Configuración pendiente:${NC}"
    log "${BLUE}   - Configurar SMTP en /developer/config/smtp${NC}"
    log "${BLUE}   - Configurar datos de la empresa${NC}"
    log "${BLUE}   - Cambiar contraseñas por defecto${NC}"
    echo ""
}

# Función principal
main() {
    log "${BLUE}🚀 Iniciando deployment de AvoControl Pro...${NC}"
    
    check_permissions
    backup_database
    backup_files
    update_code
    update_composer_dependencies
    update_node_dependencies
    run_migrations
    run_production_seeders
    set_permissions
    clear_cache
    restart_services
    verify_deployment
    show_final_info
    
    log "${GREEN}✅ Deployment completado exitosamente en $(date)${NC}"
}

# Ejecutar función principal
main "$@"