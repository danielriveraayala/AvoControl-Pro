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

  - ✅ **Responsive Design Applied to All Developer Views (100%)**
    - /developer/users views completely responsive with mobile-first approach
    - DataTables responsive configuration for user management
    - Mobile-optimized forms and action buttons
    - Tailwind CSS responsive utilities throughout user management
    - Consistent breakpoints and responsive patterns
    - Touch-friendly interface elements for mobile devices

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
    - Patrón responsivo de config aplicado a todas las vistas del panel developer
    - Mobile-first approach con breakpoints consistentes
    - Headers flexibles y botones adaptativos
    - Tablas con columnas que se ocultan en móvil
    - Cards de estadísticas optimizadas para pantallas pequeñas
    - Vistas actualizadas: roles (index/create/edit/show), users (index/create/edit/show), logs
    - Grid system optimizado para todas las resoluciones de pantalla
    - Componentes AdminLTE totalmente responsive

  - ✅ **Corrección de Relaciones User Model (100%)**
    - Fixed User::sales() relationship to use 'created_by' foreign key
    - Fixed User::payments() relationship to use 'created_by' foreign key
    - Removed User::lots() relationship (not supported by database schema)
    - UserManagementController now works correctly without column errors
    - Database schema uses 'created_by' instead of 'user_id' for tracking
    - Verified relationships work with actual data (54 sales, 61 payments)

  - ✅ **Sistema de Permisos Dashboard y UX (100%)**
    - Página 403 personalizada (resources/views/errors/403.blade.php)
    - Dashboard accesible para todos los usuarios autenticados
    - Sistema de permisos granular con Blade directives implementado
    - Mensaje de bienvenida visible para todos los usuarios
    - Métricas condicionadas por permisos específicos:
      - Alertas inventario: @canPermission('lots.read')
      - Métricas financieras: @canPermission('reports.financial')
      - Ventas del mes: @canPermission('sales.read')
      - Gráficos y tablas: @canPermission('lots.read')
    - UX optimizada: usuarios ven solo funciones permitidas
    - Página 403 con botones "Ir al Dashboard" y "Regresar"
    - Información contextual sobre permisos para usuarios no admin

  - ✅ **Sistema de Notificaciones Automáticas (100%)**
    - Comando SendTestDailyNotification creado y funcional
    - Notificaciones programadas diarias: 8:00 AM y 5:30 PM
    - 10 tareas CRON configuradas en Kernel.php para notificaciones automáticas
    - Sistema de notificaciones verificado: 4 usuarios notificados exitosamente
    - Base de datos: tabla notifications operativa con UUIDs y metadata
    - Soporte para email + push notifications con canales configurables
    - Testing completado: comando notifications:test-daily funcional

  - ✅ **Corrección Eliminación de Calidades (100%)**
    - Fixed foreign key constraint violation error en quality_grades
    - Validaciones implementadas antes de eliminación:
      - Verificación de lotes que usan la calidad
      - Verificación de sale_items con la calidad
      - Conteo específico de registros relacionados
    - Mensajes descriptivos en español reemplazando errores SQL
    - QualityGrade model mejorado con relationships:
      - lots() relationship con Lot model
      - getLotsCountAttribute() para conteo de uso
      - canBeDeleted() method para validación
    - Soporte para AJAX y web requests con responses apropiadas
    - Mantiene integridad referencial previniendo data corruption

### Sistema de Notificaciones Automáticas (10/10 Phases Complete - 100% ✅)
- ✅ **Phase 1: Architecture & Foundations (100%)**
  - Custom Notification model with UUIDs and polymorphic relations
  - PushSubscription model with browser/device tracking  
  - Laravel Scheduler configured with 10 automated tasks
  - VAPID keys generation system using minishlink/web-push library
  - Database schema optimized for notifications
  - Multi-priority notification system (low, normal, high, critical)
  - **3-Channel Support**: database (campanita) + email + push

- ✅ **Phase 2: Email System (100%)**
  - Laravel Mail completely functional with SMTP integration
  - Email notifications with responsive HTML templates
  - System log tracking implemented in Notification model
  - Daily test notifications scheduled: 8:00 AM and 5:30 PM
  - Day.js integration replacing moment.js (no deprecation warnings)

- ✅ **Phase 3: Push Notifications (100%)**
  - Complete service worker (sw.js) with native push notification support
  - Browser push notifications with offline functionality
  - Notification types with custom actions and routing
  - Vibration patterns based on priority levels
  - Notification click handling with smart navigation
  - Background sync and cache management

- ✅ **Phase 4: Events and Triggers (100%)**
  - 10 automated CRON tasks scheduled and operational in Laravel Kernel
  - Inventory checks, payment reminders, daily/weekly/monthly reports
  - System statistics and cleanup tasks
  - All notifications use 3 channels (database + email + push)

- ✅ **Phase 5: Jobs and Queues (100%)**
  - Command processing system operational
  - All notification commands working in production
  - Background processing capabilities
  - Polymorphic notification system with metadata support

- ✅ **Phase 6: CRON System (100%)**
  - Laravel Scheduler with 10 automated tasks configured:
    - `notifications:check-inventory` (every 4 hours, 8-18h, weekdays)
    - `notifications:check-overdue-payments` (daily 9:00 AM)
    - `notifications:daily-report` (daily 8:00 AM)
    - `notifications:weekly-report` (Mondays 6:00 AM)  
    - `notifications:monthly-report` (1st of month, 7:00 AM)
    - `notifications:system-stats` (Fridays 5:00 PM)
    - `notifications:process-scheduled` (every 5 minutes)
    - `notifications:cleanup` (Sundays 2:00 AM)
    - `notifications:test-daily` (8:00 AM and 5:30 PM)

- ✅ **Phase 7: Complete Notification Center UI (100%)**
  - Admin notification center at `/notifications` with AdminLTE design
  - Timeline-based notification display with priority badges
  - Real-time notification counter in navbar (campanita)
  - Mark as read/unread functionality
  - Delete notifications with proper timeline cleanup
  - Filter by status, type, date range
  - Mobile-responsive design

- ✅ **Phase 8: Advanced Configuration System (100%)**
  - **Notification Templates System**: Reusable templates with variables
  - **Notification Schedules**: Programmed delivery with recurring options
  - **Channel Configuration**: Per-type channel selection (email/push/database)
  - **Advanced Filters**: User-specific and role-based targeting
  - **Permissions System**: Granular notification permissions by role
  - **Rate Limiting**: Anti-spam and throttling configuration
  - **Notification Manager**: Complete admin interface at `/developer/config/notifications-manager`

- ✅ **Phase 9: Testing and Validation (100%)**
  - All 8 automated commands created and tested:
    - `CheckInventoryLevelsCommand` - Low stock alerts (≤20% threshold)
    - `CheckOverduePaymentsCommand` - Payment reminders  
    - `SendDailyReportCommand` - Daily operations summary
    - `SendWeeklyReportCommand` - Weekly comparative report
    - `SendMonthlyReportCommand` - Monthly financial report
    - `SendSystemStatsCommand` - System statistics and KPIs
    - `ProcessScheduledNotificationsCommand` - Template processing
    - `CleanupOldNotificationsCommand` - Old notifications cleanup
  - Fixed timeline visual issues when deleting notifications
  - All helper functions moved to AppServiceProvider (no redeclarations)

- ✅ **Phase 10: Full Production Deployment (100%)**
  - All notification channels working on VPS 69.62.65.243
  - Push notifications fully operational via HTTPS
  - Email system configured and tested
  - Database notifications appearing in campanita
  - CRON scheduler running all automated tasks
  - Comprehensive system ready for multi-tenant expansion

### Sistema de 3 Canales de Notificación
El sistema implementa **3 canales simultáneos** para máxima cobertura:

1. **📧 Email**: Correos electrónicos vía SMTP con templates responsive
2. **🔔 Push**: Notificaciones del navegador con service worker (VAPID)
3. **🔔 Database/Sistema**: Notificaciones en la "campanita" del navbar admin

**Automatización Completa**: Todas las notificaciones automáticas (inventario bajo, pagos vencidos, reportes diarios/semanales/mensuales) se envían por los 3 canales simultáneamente. Los usuarios reciben alertas inmediatas por push/email y pueden revisar el historial completo en la campanita cuando inicien sesión.

**Gestión Personalizada**: Desde el backoffice se pueden enviar notificaciones personalizadas con selección de canales, prioridades, plantillas y destinatarios específicos.

**Deployment Completo**: Sistema 100% operativo en VPS 69.62.65.243 con todas las migraciones ejecutadas, assets compilados, caché optimizado, y comandos de prueba funcionando correctamente. Compatible con PHP 7.4+ mediante ajustes de sintaxis y Day.js completamente integrado reemplazando moment.js.

### Sistema Multi-Tenant (7/10 Phases Complete - 70% ✅)

- ✅ **Phase 1: Planning & Architecture (100%)**
  - Complete multi-tenant architecture documentation
  - Database design for tenant isolation strategy
  - Security requirements and data separation planning
  - User stories and feature specifications defined

- ✅ **Phase 2: Database Foundation (100%)**
  - **Multi-tenant database structure completely implemented:**
    - `tenants` table with UUID, slug, domain/subdomain support
    - Plan-based tenant management (basic, premium, enterprise, custom, trial)
    - Comprehensive tenant settings with features and expiration dates
    - Status management (active, inactive, suspended, pending)

- ✅ **Phase 3: Tenant Relationships (100%)**
  - **`tenant_users` pivot table with advanced features:**
    - Role-based access within tenants (owner, admin, manager, vendedor, contador, member, viewer)
    - Per-tenant permissions and settings system
    - User invitation system with status tracking (active, inactive, invited, suspended)
    - Access tracking with invited_at, joined_at, last_access_at timestamps
  - **`tenant_settings` table for granular configuration:**
    - Key-value configuration system with types (string, integer, boolean, json, array)
    - Category-based organization (general, company, email, notifications, security, etc.)
    - Encryption support for sensitive values
    - Public/private setting visibility controls

- ✅ **Phase 4: Model Integration & Tenant Isolation (100%)**
  - **Complete model architecture implemented:**
    - `Tenant`, `TenantUser`, `TenantSetting` models with full business logic
    - All existing models updated with multi-tenant support
    - `tenant_id` foreign keys added to all main business tables
    - `BelongsToTenant` trait for automatic tenant scoping
    - `TenantScope` global scope for automatic query filtering
    - Enhanced User model with multi-tenant relationships and methods
    - `current_tenant_id` field added to users table for tenant switching
  - **Automatic tenant isolation system:**
    - Global scopes ensure complete data separation between tenants
    - Super admins can bypass tenant filtering when needed
    - Automatic tenant_id assignment on model creation
    - Tenant-aware relationships maintain data isolation
    - Multi-tenant user management with role inheritance

- ✅ **Phase 5: Tenant Identification & Middleware System (100%)**
  - **Complete middleware architecture for tenant resolution:**
    - `TenantResolver` middleware with multi-strategy identification:
      - Domain-based identification (custom domains)
      - Subdomain extraction and validation
      - Session-based tenant selection
      - User-based tenant switching
      - URL parameter fallback
    - `TenantContext` middleware for tenant-specific configuration:
      - Dynamic config application per tenant
      - Database connection context switching
      - Tenant-aware app settings
      - Cache namespace isolation
  - **Comprehensive middleware integration:**
    - HTTP Kernel integration with proper middleware ordering
    - Tenant context available throughout request lifecycle
    - Error handling for tenant-not-found scenarios
    - Logging and debugging capabilities

- ✅ **Phase 6: User Interface for Tenant Management (100%)**
  - **Complete tenant selection and management UI:**
    - Tenant selection page (`/tenant/select`) with responsive design
    - Tenant switching functionality with security validation
    - Error pages for tenant-not-found scenarios
    - Mobile-friendly tenant cards with plan information
    - Trial period and expiration status indicators
  - **Controller and routes implementation:**
    - `TenantController` with 7 endpoints for tenant operations
    - API endpoints for current tenant info and available tenants
    - Tenant statistics and usage reporting
    - Super admin tenant creation and management
    - Secure tenant switching with role validation

- ✅ **Phase 7: Tenant Service Provider & Blade Integration (100%)**
  - **Complete service provider with 15+ features:**
    - `TenantServiceProvider` with comprehensive Blade directives
    - Tenant-aware view composers and macros
    - Helper methods for tenant resolution
    - Blade directives: `@tenant`, `@currentTenant`, `@userCanAccessTenant`, etc.
    - View composers for tenant information injection
    - Request macros for tenant context access
  - **Testing and seeding system:**
    - `TenantSeeder` with 3 test tenants (default, premium, trial)
    - Complete tenant settings configuration
    - User assignment to tenants with role-based permissions
    - 15 different tenant settings categories
    - Integration with existing user roles and permissions

**🔄 Currently In Progress:**
- Phase 8: PayPal Subscription Integration (0% - Ready to start)
- Phase 9: Testing & Validation (0%)
- Phase 10: Production Deployment (0%)

### Sistema de Planes de Suscripción PayPal (0/8 Phases Complete - 0% 🔄)

**Estructura de Planes Definida:**

#### 🆓 **TRIAL** - 7 días gratis
- 1 usuario, 50 lotes máximo
- Reportes básicos, 500MB almacenamiento
- Sin soporte técnico
- **Flujo**: Registro → Trial automático → PayPal después de 7 días

#### 🥉 **BASIC** - $29 USD/mes
- 5 usuarios, 500 lotes/mes
- Todos los reportes, 2GB almacenamiento
- Notificaciones email, soporte por email
- **Target**: Centros de acopio pequeños

#### 🥈 **PREMIUM** - $79 USD/mes  
- 25 usuarios, 2,000 lotes/mes
- Reportes avanzados + exportación, 10GB almacenamiento
- Notificaciones push + SMS, API access, backup automático
- **Target**: Empresas medianas con múltiples usuarios

#### 🥇 **ENTERPRISE** - $199 USD/mes
- 100 usuarios, lotes ilimitados
- Reportes personalizados, 50GB almacenamiento
- Multi-ubicación, API completo, marca personalizada
- **Target**: Empresas grandes con operaciones complejas

#### 🏢 **CORPORATE** - Precio personalizado
- Usuarios ilimitados, multi-tenant ilimitado
- Servidor dedicado, SLA garantizado
- **Target**: Grupos empresariales y corporativos

**🔄 Fases Pendientes de PayPal Integration:**
- Phase 1: PayPal API Configuration & Environment Setup (0%)
- Phase 2: Subscription Plans Creation in PayPal (0%)
- Phase 3: Tenant Registration with Trial Period (0%)
- Phase 4: PayPal Webhook Integration (0%)
- Phase 5: Automatic Subscription Monitoring (0%)
- Phase 6: Account Suspension System (0%)
- Phase 7: Subscription Management Panel (0%)
- Phase 8: Testing & Production Deployment (0%)

**Flujo de Registro Propuesto:**
1. **Registro Usuario + Tenant** → Un solo formulario unificado
2. **Selección de Plan** → Basic/Premium/Enterprise (Trial automático 7 días)  
3. **Configuración Tenant** → Nombre empresa, dominio personalizado
4. **Proceso PayPal** → Suscripción o trial según selección
5. **Acceso Inmediato** → Usuario entra al sistema configurado

**Multi-Tenant por Usuario:** ✅ Soportado
- Un usuario puede administrar múltiples tenants
- Casos: Consultores, empresarios, desarrolladores
- Switching entre tenants desde navbar
- Roles diferentes por tenant

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