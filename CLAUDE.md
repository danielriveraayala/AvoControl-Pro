# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

AvoControl Pro is a Laravel-based web application for managing avocado purchasing and sales operations for Avocado Collection Centers (Centros de Acopio de Aguacate). The system tracks lot purchases from suppliers, sales to customers, payments, and provides comprehensive reporting and analytics.

**Status**: Full production-ready system with comprehensive features implemented.

## Developer Information

**Developer**: Daniel Esau Rivera Ayala  
**Company**: Kreativos Pro - Agencia de Marketing Digital y Desarrollo Web  
**Role**: CEO & Web Developer  
**Location**: Morelia, México  
**Bio**: [about.me/danielriveraayala](https://about.me/danielriveraayala)  

**About the Developer**: Daniel is a seasoned Software Engineer and Project Manager with over 12 years of experience in web systems development. He specializes in full-stack development with expertise in PHP and data management systems. As the CEO of Kreativos Pro, he leads digital marketing and web development projects with a focus on innovative solutions for business operations.

**License**: Proprietary Software - All rights reserved. This is not open source software.

## Development Commands

### Project Setup
```bash
# Navigate to project directory
cd avocontrol

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file (already configured)
cp .env.example .env

# Generate application key (already done)
php artisan key:generate

# Run database migrations (already executed)
php artisan migrate

# Seed database with test data (already populated)
php artisan db:seed

# Start development server
php artisan serve

# Compile assets (in separate terminal)
npm run dev
```

### Database Configuration
- **Host**: localhost
- **Database**: avocontrol
- **Username**: root
- **Password**: toor
- **Port**: 3306

### Common Development Commands
```bash
# Run tests (when implemented)
php artisan test

# Clear all caches
php artisan optimize:clear

# Queue worker (for background jobs)
php artisan queue:work

# Refresh database and seed (destructive!)
php artisan migrate:fresh --seed
```

### Authentication & Users

#### Panel de Desarrollador (Super Admin)
- **Acceso exclusivo**: Solo el rol `super_admin` puede acceder a `/developer`
- **Credenciales principales**:
  - `developer@avocontrol.com` / `DevPassword2024!` (Desarrollador principal)
  - `test.developer@avocontrol.com` / `TestDev123!` (Desarrollador de pruebas)
- **Funciones exclusivas implementadas**:
  - ✅ Gestión completa de usuarios del sistema (CRUD + roles)
  - ✅ Configuración de SMTP global con pruebas
  - ✅ Configuración de notificaciones push (VAPID keys + generación)
  - ✅ Dashboard con métricas del sistema en tiempo real
  - ✅ Gestión de caché, logs y modo mantenimiento
  - ⏳ Gestión de tenants/empresas (cuando se implemente multi-tenant)
  - ⏳ Gestión de suscripciones y planes

#### Usuarios de Prueba
- Default admin empresa: `admin@avocontrol.com` / `password123`
- Default vendedor: `vendedor@avocontrol.com` / `password123`
- Default contador: `contador@avocontrol.com` / `password123`

**Nota**: Los usuarios admin de empresa NO tienen acceso al panel de desarrollador

### Current Implementation Status

**✅ Completed:**

### Core System (100% Complete)
- Laravel 8.6 installation with Breeze authentication
- MySQL database configuration and optimization
- Livewire 2.12, Tailwind CSS, Alpine.js setup
- All models and migrations (Suppliers, Customers, Lots, Sales, SaleItems, Payments)
- User roles system (admin, vendedor, contador)
- Comprehensive seeders with realistic test data

### Business Logic (100% Complete)  
- Complete controllers structure with AJAX functionality
- Dashboard with real-time statistics and charts
- Comprehensive route structure
- Complete CRUD operations with DataTables integration
- Advanced reporting system (Profitability, Customer Analysis, Supplier Analysis)
- PDF and Excel export functionality for all reports
- Customer and Supplier management with credit/balance systems
- Payment tracking with polymorphic relationships

### Configuration & User Management (100% Complete)
- Configuration system with company settings
- User profile management with functional password change
- Quality grade management system
- Complete modal-based interfaces for all CRUD operations
- Server-side DataTables processing for optimal performance

### Sistema RBAC (Role-Based Access Control) - En Desarrollo
- ✅ **Sprint 1.1: Estructura de Base de Datos (100%)**
  - 4 tablas creadas (roles, permissions, role_permission, user_role)
  - 8 roles jerárquicos (super_admin hasta visualizador)
  - 52 permisos granulares en 10 módulos
  - Seeders con asignaciones rol-permiso configuradas

- ✅ **Sprint 1.2: Modelos y Relaciones (100%)**
  - Modelo Role con gestión de jerarquías
  - Modelo Permission con organización por módulos
  - User mejorado con 15+ métodos helper
  - Traits reutilizables (HasPermissions, HasRoles)
  - Sistema de caché de permisos (1hr TTL)

- ✅ **Sprint 2.1: Panel Exclusivo de Desarrollador (100%)**
  - Ruta `/developer` protegida con middleware DeveloperOnly
  - Dashboard con métricas del sistema y estado de salud
  - DeveloperController con logs, caché, y modo mantenimiento
  - SystemConfigController para SMTP y notificaciones push
  - Gestión de llaves VAPID con generación automática
  - 8 vistas completamente responsive y funcionales

- ✅ **Sprint 2.2: Gestión de Usuarios por Desarrollador (100%)**
  - UserManagementController con CRUD completo
  - Creación, edición, suspensión y activación de usuarios
  - Asignación múltiple de roles con rol primario
  - Reset de contraseñas y visualización de actividad
  - Filtros avanzados y paginación
  - Protecciones de seguridad para super_admin

**🔄 Próximas Fases:**
- Sprint 3.1: Sistema de Middleware
- Sprint 3.2: Interfaz de Administración Regular

### Notification System (Phase 1/10 Complete - 10%)
- ✅ **Phase 1: Architecture & Foundations (100%)**
  - Custom Notification model with UUIDs and polymorphic relations
  - PushSubscription model with browser/device tracking  
  - Laravel Scheduler configured with 8 automated tasks
  - VAPID keys generation command for web push notifications
  - Database schema optimized for notifications
  - Multi-priority notification system (low, normal, high, critical)
  - Multi-channel support (database, email, push, all)

**Nota**: La configuración de SMTP y notificaciones push será exclusiva del panel de desarrollador. Los usuarios regulares solo podrán activar/desactivar sus notificaciones.

## Architecture Overview

### Technology Stack
- **Backend**: Laravel 12.x with PHP 8.3+
- **Database**: MySQL 8.0+ 
- **Frontend**: Livewire 3.x + Alpine.js + Tailwind CSS
- **Charts**: Chart.js for analytics
- **Caching/Queues**: Redis

### Key Business Entities

1. **Lotes (Lots)**: Core entity representing avocado purchases from suppliers
   - Tracks weight, quality, price, status
   - Links to suppliers and sales

2. **Ventas (Sales)**: Customer sales transactions
   - Can include multiple lots
   - Tracks pricing, quantities, payments

3. **Pagos (Payments)**: Financial transactions
   - Both supplier payments and customer receipts
   - Multiple payment methods supported

4. **Proveedores/Clientes**: Supplier and customer management

### Database Schema Relationships
- Suppliers → Lots (one-to-many)
- Lots → Sales (many-to-many via pivot)
- Sales → Customers (many-to-one)
- Sales/Lots → Payments (polymorphic)

### Livewire Components Structure
Components use real-time updates for:
- Lot selection in sales
- Payment recording
- Dashboard statistics
- Report generation

### Business Logic Services
- `LoteService`: Lot inventory and status management
- `VentaService`: Sales processing and lot allocation
- `PagoService`: Payment processing and balance calculations
- `ReporteService`: Analytics and report generation

## Key Implementation Details

### Lot Status Flow
1. **disponible**: Available for sale
2. **vendido_parcial**: Partially sold
3. **vendido**: Fully sold
4. **cancelado**: Cancelled

### Payment Types
- **proveedor**: Payment to supplier
- **cliente**: Payment from customer

### Critical Business Rules
- Lots can be partially sold across multiple sales
- Automatic status updates based on remaining quantity
- Payment tracking affects supplier/customer balances
- Profitability calculated as: (sale_price - purchase_price) * quantity

### Report Types
1. Daily operations summary
2. Profitability by period
3. Supplier/customer rankings
4. Inventory status
5. Payment status

## Testing Strategy

Focus testing on:
- Lot status transitions
- Partial sales calculations
- Payment balance tracking
- Report accuracy
- User permission validations

## Security Considerations

### Sistema de Roles y Permisos (RBAC)
- **Jerarquía de Roles**: 8 niveles desde super_admin (100) hasta visualizador (10)
- **Control Granular**: 52 permisos específicos en 10 módulos
- **Panel de Desarrollador**: Acceso exclusivo para super_admin en `/developer`
- **Separación de Responsabilidades**:
  - Super Admin: Control total del sistema y configuraciones críticas
  - Admin Empresa: Gestión de su empresa y usuarios (futuro multi-tenant)
  - Roles Operativos: Permisos limitados según función

### Seguridad de Operaciones
- Payment operations require special permissions
- Audit trail for financial transactions
- Soft deletes for data integrity
- Cache de permisos con TTL de 1 hora
- Verificación de jerarquía para modificación de roles