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

# Create manual backup
php artisan backup:daily --type=full

# Run scheduled tasks manually (for testing)
php artisan schedule:run

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
  - ✅ Gestión de caché, logs y modo mantenimiento selectivo
  - ✅ Gestión de tenants/empresas con 5 planes de suscripción
  - ✅ Sistema de respaldos automáticos con CRON diarios
  - ✅ Middleware avanzado de roles y permisos (CheckRole, CheckPermission)

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

### Sistema RBAC (Role-Based Access Control) - 100% Completado
- ✅ **Fase 1: Fundamentos de RBAC (100%)**
  - 4 tablas creadas (roles, permissions, role_permission, user_role)
  - 8 roles jerárquicos (super_admin hasta visualizador)
  - 52 permisos granulares en 10 módulos
  - Seeders con asignaciones rol-permiso configuradas
  - Modelo Role con gestión de jerarquías
  - Modelo Permission con organización por módulos
  - User mejorado con 15+ métodos helper
  - Traits reutilizables (HasPermissions, HasRoles)
  - Sistema de caché de permisos (1hr TTL)

- ✅ **Fase 2: Panel de Desarrollador (100%)**
  - Ruta `/developer` protegida con middleware DeveloperOnly
  - Dashboard con métricas del sistema y estado de salud
  - DeveloperController con logs, caché, y modo mantenimiento
  - SystemConfigController para SMTP y notificaciones push
  - Gestión de llaves VAPID con generación automática
  - Gestión completa de notificaciones con DataTables y filtros
  - 8+ vistas completamente responsive y mobile-friendly
  - UserManagementController con CRUD completo de usuarios
  - Asignación múltiple de roles con rol primario
  - Reset de contraseñas y visualización de actividad
  - Filtros avanzados y paginación
  - Protecciones de seguridad para super_admin
  - Sistema de respaldos automáticos con CRON diarios
  - Gestión de tenants/suscripciones para multi-tenant

- ✅ **Fase 3: Middleware y Protección (100%)**
  - ✅ Middleware CheckRole con logging avanzado
  - ✅ Middleware CheckPermission con lógica AND/OR
  - ✅ DeveloperOnly middleware para panel exclusivo
  - ✅ 30+ Gates implementados en AuthServiceProvider
  - ✅ Protección de rutas con middleware (todas las rutas principales protegidas)
  - ✅ Sistema de jerarquía de roles (8 roles con niveles 10-100 implementados)

- ✅ **Sprint 3.2: Sistema RBAC Completo (100%)**
  - ✅ **Controlador RoleManagementController (100%)**
    - CRUD completo para gestión de roles
    - Métodos: index, create, store, show, edit, update, destroy
    - Funciones especiales: updatePermissions, clone, getDetails
    - Validaciones de seguridad para roles del sistema
    - Logging completo de todas las operaciones
    - Caché de permisos con invalidación automática

  - ✅ **Vistas CRUD para Roles (100%)**
    - index.blade.php: Lista principal con DataTables responsive
    - create.blade.php: Formulario de creación con asignación de permisos
    - show.blade.php: Vista detallada con información completa del rol
    - edit.blade.php: Formulario de edición con permisos existentes
    - Diseño completamente responsive siguiendo patrón de config
    - Mobile-first design con Tailwind CSS
    - Iconografía coherente y estado visual de jerarquías

  - ✅ **Asignación Visual de Permisos (100%)**
    - Interface de selección por módulos (10 módulos organizados)
    - Checkboxes "Todos" y "Ninguno" funcionales
    - Toggles a nivel de módulo para selección masiva
    - Modal de edición de permisos con AJAX
    - Validación en tiempo real de selecciones
    - Preservación de permisos en clonación de roles

  - ✅ **Sistema de Jerarquía y Restricciones (100%)**
    - Niveles de jerarquía 1-99 implementados
    - Restricciones basadas en jerarquía para gestión de roles/usuarios
    - Helper methods en User model (canManageRole, canManageUser, etc.)
    - Validaciones automáticas en controladores
    - Filtros de roles/usuarios por nivel de acceso

  - ✅ **Sistema de Auditoría Completo (100%)**
    - Tabla role_audits con tracking completo
    - Modelo RoleAudit con relaciones y helpers
    - Registro automático de: created, updated, deleted, permissions_changed
    - Almacena: old_values, new_values, ip_address, user_agent
    - Vista de historial de cambios en show.blade.php
    - Timeline visual con iconos y códigos de colores

  - ✅ **Fase 4: Integración del Sistema RBAC (100%)**
    - RolePermissionMiddleware personalizado
    - Blade directives (@canRole, @canPermission, @canManageRole, etc.)
    - Integración en rutas principales del sistema
    - Seeder RbacPermissionsSeeder con permisos CRUD completos
    - Sistema de permisos granulares para todos los módulos
    - Compatibilidad con sistema legacy mantenida

  - ✅ **Diseño Responsivo Completo (100%)**
    - Patrón responsivo de config aplicado a todas las vistas
    - Mobile-first approach con breakpoints consistentes
    - Headers flexibles y botones adaptativos
    - Tablas con columnas que se ocultan en móvil
    - Cards de estadísticas optimizadas para pantallas pequeñas

  - ✅ **Corrección de Relaciones User Model (100%)**
    - Fixed User::sales() relationship to use 'created_by' foreign key
    - Fixed User::payments() relationship to use 'created_by' foreign key
    - Removed User::lots() relationship (not supported by database schema)
    - UserManagementController now works correctly without column errors
    - Database schema uses 'created_by' instead of 'user_id' for tracking
    - Verified relationships work with actual data (54 sales, 61 payments)

### Sistema de Notificaciones Push (100% Complete)
- ✅ **Phase 1: Architecture & Foundations (100%)**
  - Custom Notification model with UUIDs and polymorphic relations
  - PushSubscription model with browser/device tracking  
  - Laravel Scheduler configured with 8 automated tasks
  - VAPID keys generation system using minishlink/web-push library
  - Database schema optimized for notifications
  - Multi-priority notification system (low, normal, high, critical)
  - Multi-channel support (database, email, push, all)

- ✅ **Phase 2: Service Worker Implementation (100%)**
  - Complete service worker (sw.js) with native push notification support
  - Browser push notifications with offline functionality
  - Notification types with custom actions and routing
  - Vibration patterns based on priority levels
  - Notification click handling with smart navigation
  - Background sync and cache management

- ✅ **Phase 3: Frontend Integration (100%)**
  - Complete JavaScript implementation for push management
  - Browser compatibility checking and graceful degradation
  - Subscription/unsubscription flow with user feedback
  - Real-time status updates and visual indicators
  - Test notification functionality
  - Error handling with user-friendly messages

- ✅ **Phase 4: Admin & User Interfaces (100%)**
  - Developer panel with complete VAPID key management
  - Technical configuration separated from user interface
  - Simplified user subscription interface in /configuration
  - Benefits showcase with visual notification types
  - User device management and subscription history
  - Clean, responsive design across all interfaces

- ✅ **Phase 5: Production Deployment (100%)**
  - HTTPS requirement handling for production environments
  - VPS deployment scripts with push notification support
  - Service worker registration and update handling
  - Production-ready VAPID key generation and storage
  - Complete testing and validation system

- ✅ **Phase 6: Email Integration & SMTP Configuration (100%)**
  - Complete SMTP configuration system in developer panel
  - Hostinger email integration with SSL/TLS support
  - Password handling with special characters (quoted values)
  - Email testing functionality with detailed error messages
  - RFC 2822 compliance for email formatting
  - Production-ready email configuration deployed on VPS

- ✅ **Phase 7: User Interface Optimization (100%)**
  - Removed test functionality from user configuration page
  - Separated technical features (developer panel only)
  - Streamlined user experience for notification subscriptions
  - Clean separation between user and admin interfaces
  - Enhanced security by limiting test functions to developers

- ✅ **Phase 8: Cron Integration (100%)**
  - Added push notifications to all scheduled email tasks
  - 8 automated tasks now support dual-channel notifications (email + push)
  - Complete integration with Laravel Scheduler
  - Production cron configuration active on VPS
  - Comprehensive task descriptions with notification channels

- ✅ **Phase 9: Mobile-Responsive Developer Panel (100%)**
  - Complete responsive design overhaul for all developer panel views
  - Mobile-first approach with Tailwind CSS responsive utilities
  - Enhanced mobile navigation with hamburger menu and toggle functionality
  - Optimized dashboard cards, statistics, and action buttons for mobile
  - Responsive notification manager with mobile-friendly DataTables
  - Mobile-optimized configuration forms and modals
  - Touch-friendly interface elements and improved accessibility
  - All developer module views fully responsive across desktop, tablet, and mobile

**Implementación Completa**: Sistema de notificaciones push nativo completamente funcional con integración SMTP completa, separación clara entre configuración técnica (panel desarrollador) y suscripción de usuario (configuración regular), soporte dual para email + push en todas las tareas automatizadas, y diseño completamente responsive para dispositivos móviles.

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