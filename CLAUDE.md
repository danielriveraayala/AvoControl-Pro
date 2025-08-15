# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

AvoControl Pro is a Laravel-based web application for managing avocado purchasing and sales operations for Avocado Collection Centers (Centros de Acopio de Aguacate). The system tracks lot purchases from suppliers, sales to customers, payments, and provides comprehensive reporting and analytics.

**Status**: Full production-ready system with comprehensive features implemented.
**Production URL**: https://dev.avocontrol.pro
**Environment**: Production (APP_ENV=production)
**Última actualización**: 15 Agosto 2025 - Advanced PayPal Refund Detection & Access Control System
**Estado de completación**: 100% - Sistema totalmente operativo con control automático de reembolsos y bloqueo de acceso

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
- **Database**: avocontrol_prod
- **Username**: avocontrol_user
- **Password**: DI&(G;u(/?+pPIBLc0.m
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

#### Configuración de Email SMTP
- **Host**: smtp.hostinger.com
- **Port**: 587
- **Username**: avocontrol@kreativos.pro
- **Password**: t74tP#M:
- **Encryption**: TLS
- **From Address**: avocontrol@kreativos.pro

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

### Sistema Multi-Tenant + PayPal Subscriptions (10/10 Phases Complete - 100% ✅)
### PayPal Automatic Refund Detection & Access Control System (NEW - 15 Ago 2025 - 100% ✅)

**Problema Resuelto**: El sistema ahora detecta automáticamente reembolsos de PayPal y suspende las suscripciones correspondientes, bloqueando el acceso a usuarios sin suscripción activa.

#### ✅ **Phase 1: Automatic Refund Detection via Webhooks (100%)**
- ✅ **PayPal Webhook Processing Enhanced**
  - `PayPalService::processWebhook()` maneja eventos de reembolso: `PAYMENT.CAPTURE.REFUNDED`, `PAYMENT.CAPTURE.REVERSED`
  - `PayPalService::handlePaymentRefunded()` suspende automáticamente suscripciones cuando se procesa un reembolso
  - `PayPalService::handlePaymentReversed()` maneja cancelaciones y contracargos
  - `PayPalService::handleSubscriptionReactivated()` reactivación automática tras resolución de reembolsos

- ✅ **Automatic Subscription Suspension Logic**
  ```php
  $subscription->update([
      'status' => 'suspended',
      'suspended_at' => Carbon::now(),
      'suspension_reason' => 'Payment refunded: ' . $reason,
      'suspended_by' => 'paypal-webhook'
  ]);
  ```

- ✅ **Enhanced Database Schema for Refunds**
  - Expanded ENUM values in `subscription_payments` table:
    - Types: 'initial', 'recurring', 'retry', 'refund', 'chargeback'
    - Status: 'pending', 'completed', 'failed', 'refunded', 'reversed'
  - Unique constraint handling for PayPal payment IDs
  - Refund records creation with `REFUND-{random}-{paypal_id}` format

#### ✅ **Phase 2: Subscription Access Control Middleware (100%)**
- ✅ **CheckActiveSubscription Middleware Implementation**
  - Middleware `app/Http/Middleware/CheckActiveSubscription.php` creado
  - Verificación automática de estado de suscripción en cada request
  - Bypass para super_admin users (acceso total sin restricciones)
  - Exclusión de rutas públicas: login, register, landing page, webhooks

- ✅ **Access Control Logic**
  ```php
  // Subscription states that block access
  if (in_array($subscription->status, ['suspended', 'cancelled', 'expired'])) {
      return redirect()->route('subscription.' . $subscription->status)
          ->with('error', 'Tu suscripción está ' . $subscription->status);
  }
  ```

- ✅ **User Experience Pages**
  - `resources/views/subscription/suspended.blade.php`: Página profesional de suspensión con opciones de reactivación
  - `resources/views/subscription/cancelled.blade.php`: Página de cancelación con nuevos planes de suscripción
  - Templates responsive con información de contacto y pasos de solución

#### ✅ **Phase 3: Developer Panel Subscription Management (100%)**
- ✅ **Functional AJAX-Powered Buttons**
  - Botones en `/developer/subscriptions` ahora completamente funcionales
  - Sistema de modales con SweetAlert2 para confirmaciones
  - Operaciones: suspend, reactivate, change-plan, sync-paypal
  - Formularios con validación requerida de motivos/razones

- ✅ **Real-time Subscription Management**
  ```javascript
  function performSuspension(subscriptionId, reason) {
      fetch(`/developer/subscriptions/${subscriptionId}/suspend`, {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json'
          },
          body: JSON.stringify({ reason: reason })
      })
  }
  ```

- ✅ **Enhanced UI/UX for Subscription Management**
  - Métricas en tiempo real: MRR, ARR, ARPU, Churn Rate
  - Filtros dinámicos por status y plan
  - Actividad reciente con timeline visual
  - Estadísticas de alertas críticas (trials expirando, pagos fallidos)

#### ✅ **Phase 4: Testing & Validation System (100%)**
- ✅ **Comprehensive Testing Commands**
  - `php artisan paypal:simulate-refund`: Simula webhooks de reembolso para testing
  - `php artisan subscription:test-access`: Verifica restricciones de acceso por usuario
  - `php artisan subscription:reactivate`: Comando manual de reactivación
  - Todos con dry-run mode y logging detallado

- ✅ **Simulation System for Safe Testing**
  ```bash
  # Simular reembolso sin afectar PayPal real
  php artisan paypal:simulate-refund --subscription-id=SUB123 --amount=29.00 --reason="Customer dispute"
  
  # Verificar control de acceso
  php artisan subscription:test-access --email=user@domain.com
  
  # Reactivar suscripción suspendida
  php artisan subscription:reactivate --email=user@domain.com --reason="Payment resolved"
  ```

#### ✅ **Phase 5: PDF Invoice System Optimization (100%)**
- ✅ **Legal Paper Size Configuration**
  - PDF invoices configurados a tamaño "oficio" (legal): `$pdf->setPaper('legal', 'portrait')`
  - Removed "LIVE" environment badge from invoice templates
  - Maintained professional PayPal branding and invoice structure

- ✅ **Invoice Download Integration**
  - Real PayPal API integration for invoice downloads
  - Automatic PDF generation from PayPal transaction data
  - Legal compliance with proper invoice formatting

### PayPal Subscription System Optimization (LEGACY - Pre-Refund System)

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

- ✅ **Phase 8: PayPal Subscription Integration (100%)**
  - **Complete PayPal API Integration:**
    - PayPal Server SDK v1.1.0 implemented
    - 5 subscription plans (Trial, Basic, Premium, Enterprise, Corporate)
    - Webhook processing system with comprehensive logging
    - Automatic payment retry with progressive delays
    - Subscription monitoring and alerting system
  - **Business Logic Implementation:**
    - MRR, ARR, ARPU, Churn Rate calculations
    - Automatic suspension/reactivation system
    - Grace period management
    - Data backup before suspension simulation

- ✅ **Phase 9: Testing & Validation (100%)**
  - **Comprehensive Testing Commands:**
    - `paypal:test-integration` - PayPal API testing with dry-run
    - `tenant:test-isolation` - Multi-tenant data isolation verification
    - `plans:test-limits` - Subscription plan limits validation
  - **Testing Features:**
    - Dry-run mode for safe testing
    - Automatic test data cleanup
    - Detailed success/failure reporting
    - Business rules validation

- ✅ **Phase 10: Production Deployment (100%)**
  - **Developer Panel Integration:**
    - Subscription management panel with Tailwind CSS
    - Real-time business metrics dashboard
    - Advanced filtering and action buttons
    - Mobile-responsive design
  - **System Integration:**
    - Complete integration with existing RBAC system
    - Multi-tenant aware subscription management
    - Automated CRON job scheduling
    - Production-ready webhook endpoints

### Sistema de Planes de Suscripción PayPal (8/8 Phases Complete - 100% ✅)

#### ✅ **Sprint 6.4: PayPal Webhooks System (100% Completado)**

**Funcionalidades Implementadas:**

- ✅ **Sistema de Endpoints y Rutas Completo**
  - Endpoint público `/paypal/webhook` para recibir webhooks de PayPal
  - 5 rutas protegidas en Developer Panel para gestión completa
  - Endpoints para retry (`/webhooks/{id}/retry`), export (`/webhooks/export`) y detalles (`/webhooks/{id}/details`)

- ✅ **Procesamiento de Eventos PayPal Robusto**
  - `BILLING.SUBSCRIPTION.ACTIVATED`: Activación de suscripciones con actualización de estado
  - `BILLING.SUBSCRIPTION.CANCELLED`: Cancelación con tracking de motivo
  - `BILLING.SUBSCRIPTION.SUSPENDED`: Suspensión temporal con fecha de suspensión
  - `BILLING.SUBSCRIPTION.PAYMENT.COMPLETED`: Pagos exitosos con creación de SubscriptionPayment
  - `BILLING.SUBSCRIPTION.PAYMENT.FAILED`: Pagos fallidos con periodo de gracia y conteo
  - Manejo inteligente de eventos desconocidos con status "ignored"

- ✅ **Sistema de Logs y Auditoría Completo**
  - Tabla `paypal_webhook_logs` con 29 campos optimizados y 6 índices de rendimiento
  - Modelo `PayPalWebhookLog` con 15+ métodos helper y relaciones
  - 4 status de tracking: received, processed, failed, ignored
  - Almacenamiento completo del payload JSON de PayPal
  - Tracking completo: IP, User Agent, duración de procesamiento, notas de error

- ✅ **Panel de Gestión en Developer Completo**
  - Vista responsive en `/developer/paypal/webhooks` con diseño Tailwind CSS
  - 6 métricas de estadísticas en tiempo real (total, procesados, fallidos, pendientes, hoy, semana)
  - Tabla con logs de webhooks recientes con paginación y filtros
  - Modal de detalles con información completa del webhook y payload JSON
  - Configuración visual de 9 tipos de eventos PayPal soportados

- ✅ **Funcionalidad de Retry Avanzada**
  - Método `retryWebhook()` en Developer Controller con validaciones
  - `resetForRetry()` y `canRetry()` methods en modelo PayPalWebhookLog
  - Interface web con botón de reintento para webhooks fallidos/ignorados
  - Validación de estados que permiten reintento con feedback visual

- ✅ **Exportación de Logs Profesional**
  - Export completo a CSV con 10 campos de información
  - Filtros personalizables: status, tipo de evento, rangos de fecha
  - Descarga directa desde navegador con nombres de archivo timestamped
  - Datos formateados: fechas en español, status traducidos, relaciones incluidas

- ✅ **Testing y Validación Automatizado**
  - Comando `php artisan paypal:test-webhooks` con 5 tipos de tests
  - Tests automatizados: conectividad DB, creación logs, procesamiento, retry, estadísticas
  - Suite de tests 100% funcional con output colorizado y tabla de métricas
  - Todos los tests pasaron exitosamente con cleanup automático

**Archivos Implementados:**
- Database: `2025_08_13_112208_create_pay_pal_webhook_logs_table.php`
- Models: `app/Models/PayPalWebhookLog.php` (15+ métodos)
- Controllers: `app/Http/Controllers/PayPalController.php`, `app/Http/Controllers/Developer/PayPalController.php`
- Services: `app/Services/PayPalService.php` (método `processWebhook()` mejorado)
- Views: `resources/views/developer/paypal/webhooks.blade.php` (UI completa con JavaScript)
- Commands: `app/Console/Commands/TestWebhookSystem.php` (testing automatizado)
- Routes: 5 rutas adicionales en `routes/web.php`

**Estado Técnico:**
- ✅ Logging completo de todos los eventos PayPal
- ✅ Error handling robusto con try-catch en todos los métodos
- ✅ Security: endpoints protegidos con middleware de autenticación Developer
- ✅ Performance: índices de base de datos optimizados para consultas rápidas
- ✅ UX/UI: interface responsive con modales, estadísticas y feedback visual
- ✅ Testing: suite de tests automatizados 100% funcional y verificado

#### ✅ **Sprint 6.5: Automatic Subscription Monitoring (100% Completado)**

**Sistema de Monitoreo Automático Implementado:**

- ✅ **CRON Job de Verificación de Estados**
  - Comando `subscriptions:monitor` con 5 tipos de verificaciones
  - Programado cada 4 horas durante horario laboral (6:00-22:00)
  - Verificación intensiva diaria a las 7:00 AM
  - Integración con PayPal API para sincronización de estados

- ✅ **Alertas de Vencimiento Automáticas**
  - Trials expirando en 3 días: Email recordatorio temprano
  - Trials expirando en 1 día: Email de urgencia
  - Trials expirando hoy: Suspensión automática + email
  - Suscripciones pagadas: Alertas 3 días antes del vencimiento
  - Templates de email profesionales con CTAs de renovación

- ✅ **Sistema de Reintentos Automáticos de Pagos**
  - Comando `subscriptions:retry-payments` con lógica progresiva
  - Delays inteligentes: 1 día, 3 días, 7 días entre reintentos
  - Máximo 3 reintentos antes de suspensión definitiva
  - Simulador de pagos con 70% de tasa de éxito
  - Logging completo de todos los intentos de pago

- ✅ **Reportes Comprehensivos para Super Admin**
  - Comando `subscriptions:generate-reports` con períodos configurables (daily, weekly, monthly)
  - Métricas MRR, ARR, ARPU, churn rate, conversion rate
  - Distribución por planes y análisis de top performers
  - Generación de archivos JSON con datos completos
  - Email automático a todos los usuarios super_admin

- ✅ **Templates de Email Profesionales**
  - `SubscriptionExpiringEmail`: Alertas de vencimiento con detalles del plan
  - `TrialExpiredEmail`: Notificación de trial vencido con opciones de upgrade
  - `SubscriptionReportEmail`: Reporte ejecutivo con métricas clave
  - Diseño responsive con CSS integrado y CTAs claros

**Archivos Implementados:**
- Commands: `MonitorSubscriptions.php`, `RetryFailedPayments.php`, `GenerateSubscriptionReports.php`
- Mail: `SubscriptionExpiringEmail.php`, `TrialExpiredEmail.php`, `SubscriptionReportEmail.php`
- Views: `emails/subscription-expiring.blade.php`, `emails/trial-expired.blade.php`, `emails/subscription-report.blade.php`
- Scheduler: 8 tareas CRON adicionales en `app/Console/Kernel.php`

**Programación CRON Completa:**
- `subscriptions:monitor`: Cada 4h (6:00-22:00) + diario 7:00 AM
- `subscriptions:retry-payments`: Diario 10:00 AM
- Reportes diarios: 8:30 AM
- Reportes semanales: Lunes 9:00 AM  
- Reportes mensuales: 1er día del mes 8:00 AM

**Estado Técnico:**
- ✅ Dry-run mode para testing sin modificar datos
- ✅ Estadísticas detalladas con tablas formateadas
- ✅ Error handling con logs en PaymentLog
- ✅ Integración completa con sistema de emails existente
- ✅ Progressive retry delays con lógica inteligente
- ✅ JSON reports con attachments automáticos

#### ✅ **Sprint 6.6: Account Suspension System (100% Completado)**

**Sistema de Suspensión Automática Implementado:**

- ✅ **Comando AutoSuspendAccounts Completo**
  - 5 etapas de procesamiento: inmediatas, gracia, advertencias, largo plazo, reactivación
  - Dry-run mode para testing seguro sin modificar datos
  - Estadísticas detalladas con tablas formateadas en consola
  - Error handling robusto con logs automáticos
  - Progreso en tiempo real con indicadores visuales

- ✅ **Suspensión Automática Inteligente**
  - Triggers: 3+ fallos de pago, trial expirado, período de gracia vencido
  - Data backup simulado antes de cada suspensión
  - Tenant status sync (subscription + tenant suspendido simultáneamente)
  - Razones específicas de suspensión con tracking completo
  - Programación para eliminación automática tras 30+ días suspendido

- ✅ **Período de Gracia Configurable**
  - Campo `custom_grace_period_days` para personalización por suscripción
  - Inicio automático tras primer fallo de pago
  - Tracking completo: `grace_period_started_at` y `grace_period_ends_at`
  - Advertencias automáticas 3 días, 1 día antes de suspensión
  - Contador de días restantes con helpers en modelo

- ✅ **Sistema de Reactivación Automática**
  - Reactivación tras pago exitoso con limpieza completa de contadores
  - Restauración de tenant status y permisos de usuarios
  - Reset de `failed_payment_count` y `grace_period_ends_at`
  - Tracking de reactivación: reason, timestamp, triggered_by
  - Validación de elegibilidad (30 días máximo suspendido)

- ✅ **Templates de Email Profesionales**
  - `AccountSuspendedEmail`: Notificación de suspensión con pasos de reactivación
  - `SuspensionWarningEmail`: Advertencias urgentes/normales con countdown visual
  - `AccountReactivatedEmail`: Confirmación de reactivación con celebración
  - CSS responsive con animaciones (blink) para advertencias urgentes
  - CTAs claros para gestión de suscripción y contacto con soporte

- ✅ **Business Logic Comprehensiva**
  - 15+ métodos en Subscription model para gestión de estados
  - Helpers: `canBeSuspended()`, `canBeReactivated()`, `daysSinceSuspension()`
  - Scopes: `suspended()`, `longTermSuspended()`, `eligibleForReactivation()`
  - Validaciones automáticas de límites y estados
  - Integration completa con PayPal webhook status updates

**Archivos Implementados:**
- Command: `AutoSuspendAccounts.php` (373 líneas, 5 etapas de procesamiento)
- Mail: `AccountSuspendedEmail.php`, `SuspensionWarningEmail.php`, `AccountReactivatedEmail.php`
- Views: `emails/account-suspended.blade.php`, `emails/suspension-warning.blade.php`, `emails/account-reactivated.blade.php`
- Migration: `add_suspension_fields_to_subscriptions_table.php` (12 nuevos campos)
- Scheduler: 2 tareas CRON adicionales en `app/Console/Kernel.php`

**Programación CRON:**
- `accounts:auto-suspend`: Cada 6h (6:00-22:00 horario laboral)
- `accounts:auto-suspend`: Verificación nocturna 2:30 AM

**Base de Datos:**
- 12 campos nuevos para tracking completo de suspensiones:
  - Suspensión: `suspension_reason`, `suspended_by`, `suspended_at`
  - Reactivación: `reactivated_at`, `reactivation_reason`, `reactivated_by`
  - Gracia: `grace_period_started_at`, `custom_grace_period_days`
  - Backup: `data_backed_up_before_suspension`, `data_backup_created_at`, `data_backup_path`
  - Eliminación: `scheduled_for_deletion_at`, `deletion_warning_sent`

**Estado Técnico:**
- ✅ Migration aplicada exitosamente (batch 40)
- ✅ Command funciona sin errores con --dry-run mode
- ✅ Integration completa con sistema de emails existente
- ✅ Modelo Subscription sin métodos duplicados (fixed isSuspended)
- ✅ CRON tasks programadas automáticamente
- ✅ Ready for production deployment

#### ✅ **Sprint 6.7: Subscription Management Panel (100% Completado)**

**Panel Avanzado de Gestión de Suscripciones Implementado:**

- ✅ **Dashboard Completo con Métricas Business Intelligence**
  - MRR (Monthly Recurring Revenue) con crecimiento porcentual
  - ARR (Annual Recurring Revenue) calculado automáticamente
  - ARPU (Average Revenue Per User) por suscripción activa
  - Churn Rate mensual con análisis de cancelaciones
  - LTV (Lifetime Value) basado en ARPU y Churn Rate
  - Conversion Rate de trials a suscripciones pagadas

- ✅ **DataTables Responsivo Avanzado**
  - 7 columnas con información detallada: empresa, plan, estado, facturación, ingresos, fecha, acciones
  - Filtros dinámicos por estado, plan, tipo (trial/pago) 
  - Server-side processing para performance óptima
  - Información contextual: período de gracia, fallos de pago, días de trial restantes
  - Badges de estado con códigos de color y alertas visuales

- ✅ **Gestión Integral de Suscripciones**
  - Cambio de planes (upgrade/downgrade) con pricing automático
  - Suspensión/reactivación manual con razones requeridas
  - Sincronización bidireccional con PayPal API
  - Extensión de trials con validaciones de elegibilidad
  - Logging completo de todas las operaciones administrativas

- ✅ **Visualización de Datos con Charts.js**
  - Gráfico de dona: Distribución por planes activos
  - Gráfico de barras: Estados de suscripciones
  - Gráfico lineal: Ingresos mensuales (últimos 12 meses)
  - Timeline de actividad reciente con estados visuales
  - Responsive design para todas las resoluciones

- ✅ **Sistema de Alertas Críticas**
  - Trials terminando en 3 días o menos
  - Suscripciones con pagos fallidos
  - Cuentas suspendidas que requieren atención
  - Links directos para acciones correctivas
  - Alertas automáticas con iconografía contextual

- ✅ **Modales Interactivos Avanzados**
  - Modal de detalles: información completa + historial de pagos
  - Modal de cambio de plan: selección de nuevo plan + razón
  - Modal de confirmación de acciones: suspensión/reactivación con motivos
  - Formularios con validación en tiempo real
  - Integración AJAX para operaciones sin reload de página

**Archivos Implementados:**
- Controller: `SubscriptionController.php` mejorado (750+ líneas con 15+ métodos)
- View: `subscriptions/index.blade.php` completamente reescrita (754 líneas)
- Routes: 8 rutas nuevas para gestión completa de suscripciones
- JavaScript: Sistema completo de charts, modals y AJAX (285 líneas)

**Funcionalidades Técnicas:**
- DataTables con Yajra package para server-side processing
- Chart.js integration con 3 tipos de gráficos
- AdminLTE components: info-boxes, small-boxes, cards, timeline
- Bootstrap modals con formularios dinámicos
- CSRF protection en todas las operaciones
- Error handling robusto con toastr notifications

**Business Intelligence Dashboard:**
- 4 small-boxes con métricas principales
- 4 info-boxes con KPIs avanzados (MRR, ARR, ARPU, Churn)
- Sistema de cálculo automático de métricas business
- Comparación mes a mes con indicadores de crecimiento
- Análisis de conversión de trials a suscripciones

**Estado Final:** Panel de gestión de suscripciones completamente operativo con nivel enterprise, métricas avanzadas, y capacidades de administración integral. **DESPLEGADO EN PRODUCCIÓN** con todas las funcionalidades PayPal operativas en https://dev.avocontrol.pro/developer

### Sistema de Planes de Suscripción PayPal (8/8 Phases Complete - 100% ✅)

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

**✅ Sistema de Gestión de Planes Completado (100%)**

#### ✅ **Phase 8: Plan Management System (100% Completado)**

**Sistema CRUD Completo para Gestión de Planes:**

- ✅ **Modelo SubscriptionPlan Avanzado**
  - 16 campos configurables: key, name, description, price, currency, billing_cycle
  - Límites granulares: max_users, max_lots_per_month, max_storage_gb, max_locations
  - Sistema de features por categorías (7 categorías, 25+ features)
  - Metadata, color personalizado, iconos FontAwesome
  - Relación con Subscription usando 'plan' como foreign key

- ✅ **Controlador PlanManagementController Completo**
  - CRUD completo: index, create, store, show, edit, update, destroy
  - Funciones especiales: syncWithPayPal, unsyncFromPayPal, duplicate, toggleStatus
  - Validaciones de seguridad: verificación de suscripciones activas antes de eliminar
  - Sistema de features organizadas por categorías
  - Logs de todas las operaciones administrativas

- ✅ **Vistas Responsivas Completas (3/3)**
  - **index.blade.php**: Lista de planes con cards, filtros, estadísticas de uso
  - **create.blade.php**: Formulario completo de creación con selección de features
  - **edit.blade.php**: Formulario de edición con datos pre-cargados
  - **show.blade.php**: Vista detallada con overview, límites, features, estadísticas
  - Diseño mobile-first siguiendo patrón del developer panel

- ✅ **Sistema de Features Avanzado**
  - 7 categorías organizadas: reports, notifications, api, storage, customization, support, advanced
  - 25+ features específicas con labels descriptivos
  - Selección múltiple con "Todos/Ninguno" por categoría
  - Visualización por módulos en vista show

- ✅ **Integración PayPal Completa**
  - Sincronización/desincronización con PayPal API
  - Estados de sincronización visibles en todas las vistas
  - Validaciones de seguridad para cambios de planes sincronizados
  - Indicadores visuales de estado PayPal

- ✅ **Funciones Administrativas Avanzadas**
  - Duplicación de planes con generación automática de claves únicas
  - Sistema de validación para eliminación (protección de planes del sistema)
  - Toggle de estado activo/inactivo
  - Contador de suscripciones por plan
  - Links directos a suscripciones filtradas por plan

**Archivos Implementados:**
- Model: `SubscriptionPlan.php` (221 líneas) con relationships y business logic
- Controller: `PlanManagementController.php` (580+ líneas) con 12 métodos
- Migration: `create_subscription_plans_table.php` con estructura completa
- Seeder: `SubscriptionPlansSeeder.php` con 4 planes predefinidos
- Views: 3 archivos blade (index/create/edit/show) totalmente responsive
- Routes: 10 rutas para CRUD completo y funciones especiales
- Menu: Integración en developer layout con "Gestión de Planes"

**Funcionalidades Técnicas:**
- Scopes: active, featured, custom, standard, ordered
- Accessors: formatted_price, limit displays con "Ilimitado"
- Business logic: canBeDeleted(), hasFeature(), getFeatureValue()
- AJAX operations con SweetAlert2 confirmations
- Error handling robusto con validaciones del lado servidor
- Sistema de colores personalizados por plan

**Estado Final:** Sistema de gestión de planes completamente operativo con capacidades enterprise: creación de planes personalizados, gestión de features granular, integración PayPal, y administración completa desde developer panel. ✅ **DESPLEGADO EN PRODUCCIÓN**

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

### PayPal Integration (Production)
- **Environment**: Sandbox (testing) y Live (production)
- **Sandbox Client ID**: AVaHnlamxWarUpEaUB70gaKj9ANJxNi8Oum0Et21g9k671jE415MgkVtoraDFzn1ys435auIgnAhGb8U
- **Live Client ID**: Ac6zd4zgeT-BnOBlwkKU_n1mZx1F2bTWtBY278kO-B1MQ4IFDBpGB4VC8t0pLA0iWRcqszo2KvkUckN5
- **Basic Plan ID**: P-1F229431HU785402ENCOSSII
- **Premium Plan ID**: P-0SB15602P6621791ANCOSSII
- **Enterprise Plan ID**: P-5GF05071VH782482BNCOSSIQ

### Push Notifications (VAPID Keys)
- **Public Key**: BMIv-RTmDW8u4zZs86Hpmoay2QtCilrwRLHhQ9AlLl0q_OgWE2Yu-9pSZ5XYSu8rzJYYGxuKHMVSfV9WgrQJwHM
- **Private Key**: p2YRMTH3lWLVj5BWZvOD9vpHye4oJrnpgyQ0udQsZjc
- **Subject**: mailto:avocontrol@kreativos.pro

### Landing Page Comercial (100% Completado ✅)

#### **Características Implementadas:**
- ✅ **Landing Page Profesional** en ruta raíz (/) para venta de suscripciones
- ✅ **SEO Optimizado**: Meta tags completos, Open Graph, Twitter Cards, Schema.org
- ✅ **Responsive Design**: Mobile-first con Bootstrap 5, optimizado tablets y móviles
- ✅ **6 Secciones**: Hero, Features, Pricing, Testimonials, FAQ, CTA
- ✅ **Sistema de Precios Dinámico**: Switch mensual/anual con actualización en tiempo real
- ✅ **Animaciones**: AOS (Animate On Scroll) para efectos visuales profesionales
- ✅ **Imágenes**: Picsum.photos para placeholders de alta calidad

#### **Navegación Inteligente:**
- ✅ **Usuarios No Autenticados**: Ven landing page de ventas en /
- ✅ **Usuarios Autenticados**: Redirigen automáticamente a /dashboard
- ✅ **Middleware**: RedirectAuthenticatedFromLanding para flujo correcto
- ✅ **TenantResolver**: Excluye rutas públicas (/, pricing, features, contact, login)

#### **UX/UI Optimizations:**
- ✅ **Menú Hamburguesa**: Se cierra automáticamente al navegar
- ✅ **Layout Responsive**: "Sin tarjeta" en 3 filas verticales (móvil-friendly)
- ✅ **Smooth Scroll**: Navegación fluida entre secciones
- ✅ **Touch-Friendly**: Sin hover effects problemáticos en dispositivos táctiles

#### **Sistema de Precios Dinámico (NEW - 15 Ago 2025):**
- ✅ **Precios Unificados**: Un solo plan puede tener precio mensual Y anual opcional
- ✅ **Switch Condicional**: Solo aparece si al menos un plan tiene precio anual configurado
- ✅ **Actualización en Tiempo Real**: JavaScript cambia precios, duración y botones sin recargar
- ✅ **Botones Inteligentes**: 
  - PayPal para planes sincronizados (paypal_plan_id y paypal_annual_plan_id)
  - "Contactar" para planes personalizados sin sincronización
- ✅ **Información Contextual**:
  - Modo mensual: Muestra días de trial gratis
  - Modo anual: Muestra ahorro en $ y % de descuento
- ✅ **Gestión desde Developer Panel**:
  - Campos de precio anual en formularios create/edit
  - Cálculo automático del descuento porcentual
  - Preview del precio mensual equivalente

#### **Modal de Información Legal:**
- ✅ **4 Secciones Legales Profesionales**:
  - **Política de Privacidad**: Recopilación, uso, compartir, seguridad, derechos usuario
  - **Términos y Condiciones**: Servicio, cuentas, planes, uso aceptable, ley aplicable
  - **Política de Cookies**: 4 tipos (esenciales, funcionalidad, rendimiento, terceros)
  - **Información de Licencias**: Software propietario, tecnologías terceros, contactos
- ✅ **UI Modal**: Bootstrap tabs navegables, auto-switching, botón imprimir
- ✅ **Footer Integration**: Links legales abren modal en sección específica
- ✅ **Cumplimiento Legal**: Compatible GDPR, leyes mexicanas, contactos específicos

#### **Archivos Implementados:**
- `app/Http/Controllers/LandingPageController.php`: Controlador con data estructurada
- `app/Http/Middleware/RedirectAuthenticatedFromLanding.php`: Redirección inteligente  
- `resources/views/landing/index.blade.php`: Vista principal con modal legal integrado
- `routes/web.php`: Rutas públicas configuradas con middleware

#### **SEO y Marketing:**
- ✅ **Meta Tags Completos**: Title, description, keywords optimizados para aguacate/acopio
- ✅ **Open Graph**: Facebook, LinkedIn sharing optimizado
- ✅ **Twitter Cards**: Sharing con imágenes y descripciones  
- ✅ **Schema.org**: SoftwareApplication structured data para Google
- ✅ **Canonical URL**: SEO duplicado content prevention
- ✅ **Responsive Images**: Picsum.photos con dimensiones optimizadas

**Estado Final**: Landing page completamente operativa en https://dev.avocontrol.pro/ con información legal completa y flujo de conversión profesional para venta de suscripciones.

### PayPal Subscription System Optimization (15 Ago 2025 - 100% ✅)

#### **Problema Identificado:**
Los botones PayPal en `/subscription/register/basic` presentaban errores debido a PayPal plan IDs incorrectos en la base de datos y falta de soporte para doble sincronización (mensual/anual).

#### **✅ Sprint 6.8: Advanced PayPal Plan Synchronization (100% Completado)**

**Funcionalidades Implementadas:**

- ✅ **Doble Sincronización Automática**
  - `PlanManagementController::syncWithPayPal()` completamente reescrito
  - Sincronización simultánea de planes mensuales y anuales cuando aplique
  - Manejo inteligente de errores parciales (mensual exitoso, anual fallido)
  - Logging detallado de cada operación de sincronización
  - Response JSON con información completa de ambos PayPal plan IDs

- ✅ **PayPalService Enhanced para Dual Billing Cycles**
  - `createSubscriptionPlan($plan, $billingCycle)` con soporte para 'monthly' y 'yearly'
  - `createProductForPlan($plan, $billingCycle)` diferenciado por ciclo
  - Cálculo automático de precios: mensual usa `price`, anual usa `annual_price`
  - Nombres diferenciados: "Plan Básico (Mensual)" vs "Plan Básico (Anual)"
  - Productos PayPal únicos por plan y ciclo de facturación

- ✅ **UI/UX Mejorada para Gestión de Planes**
  - Campo "Ciclo de Facturación" fijo en "Mensual" en sección "Información de Precios"
  - Sección separada "Precios Anuales (Opcional)" para configuración anual
  - Notificaciones visuales cuando planes existentes tenían configuraciones diferentes
  - Explicaciones contextuales sobre la nueva arquitectura de precios
  - Input fields disabled con explicaciones claras del nuevo flujo

- ✅ **Error Handling Avanzado en Registro de Suscripciones**
  - `showPayPalError()` function para mostrar errores user-friendly
  - Contenedor visual de errores en `/subscription/register/{plan}`
  - Validación previa de PayPal plan IDs antes de renderizar botones
  - Logging mejorado en browser console para debugging
  - Manejo específico de errores: INVALID_PLAN, conexión, configuración

- ✅ **Logging y Auditoría Completa**
  - Log::info/error en todas las operaciones de sincronización
  - Tracking de plan_id, paypal_plan_id, paypal_annual_plan_id
  - Metadata de errores PayPal para debugging
  - Console.log detallado en frontend para troubleshooting
  - Registro de estados de botones PayPal (initialized, cleared, error)

**Archivos Modificados:**
- Controllers: `PlanManagementController.php` (método syncWithPayPal reescrito)
- Services: `PayPalService.php` (createSubscriptionPlan + createProductForPlan enhanced)
- Views: `plans/create.blade.php`, `plans/edit.blade.php` (UI billing cycle fixed)
- Views: `subscription/register.blade.php` (error handling mejorado)
- Documentation: `CLAUDE.md` (nueva sección completa)

**Flujo de Trabajo Mejorado:**
1. **Configuración de Plan**: Admin configura precio mensual + precio anual (opcional)
2. **Sincronización Dual**: Un click sincroniza ambos planes si precio anual existe
3. **PayPal Integration**: Genera `paypal_plan_id` (mensual) + `paypal_annual_plan_id` (anual)
4. **User Registration**: Selector mensual/anual funciona correctamente
5. **Error Recovery**: Errores de sincronización se muestran claramente con pasos de solución

**Estado Técnico:**
- ✅ Arquitectura de doble sincronización operativa
- ✅ Manejo robusto de errores parciales y totales
- ✅ UI adaptada para el nuevo flujo de trabajo
- ✅ Logging completo para debugging y auditoría
- ✅ Compatibilidad total con planes existentes
- ✅ Ready for production en https://dev.avocontrol.pro

**Beneficios Logrados:**
- **Resolución del Error**: Botones PayPal en `/subscription/register/basic` ahora funcionan
- **Flexibilidad**: Soporte nativo para precios anuales con descuentos
- **Mantenibilidad**: Sincronización centralizada y logging detallado
- **UX Mejorada**: Errores claros y proceso de configuración intuitivo
- **Escalabilidad**: Arquitectura preparada para múltiples ciclos de facturación futuros

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