# Plan de Desarrollo - Nuevas Funcionalidades AvoControl Pro

## Orden de Implementación Recomendado

**1. Sistema RBAC (Primero)**
**2. Sistema Multi-Tenant (Segundo)**  
**3. PWA (Tercero)**

### Justificación del Orden:

- **RBAC primero** porque es fundamental para la seguridad y se necesita antes del multi-tenant
- **Multi-Tenant después** porque aprovecha el sistema de roles ya implementado
- **PWA al final** porque es una mejora de experiencia que funciona sobre las bases sólidas anteriores

---

## 1. SISTEMA RBAC (Role-Based Access Control)

### **Fase 1: Fundamentos de RBAC (Semana 1-2)**

#### Sprint 1.1: Estructura de Base de Datos ✅ **COMPLETADO**
- [x] Crear migración para tabla `roles`
- [x] Crear migración para tabla `permissions` 
- [x] Crear migración para tabla `role_permission` (pivot)
- [x] Crear migración para tabla `user_role` (pivot)
- [x] Crear seeders con roles básicos (8 roles: super_admin, admin, gerente, contador, vendedor, comprador, operario, visualizador)
- [x] Crear seeders con permisos granulares (52 permisos en 10 módulos)

#### Sprint 1.2: Modelos y Relaciones ✅ **COMPLETADO**
- [x] Crear modelo `Role` con relaciones
- [x] Crear modelo `Permission` con relaciones
- [x] Modificar modelo `User` para incluir roles
- [x] Crear traits para manejo de permisos (HasPermissions, HasRoles)
- [x] Implementar métodos helper (hasRole, hasPermission, etc.)

### **Fase 2: Panel de Desarrollador (Semana 3)**

#### Sprint 2.1: Panel Exclusivo de Desarrollador ✅ **COMPLETADO**
- [x] Crear ruta `/developer` protegida con rol `super_admin`
- [x] Dashboard de desarrollador con métricas del sistema
- [x] Gestión de usuarios administradores de empresas
- [x] Gestión de suscripciones y planes (preparación para multi-tenant)
- [x] Configuración de SMTP del sistema
- [x] Configuración de notificaciones push (VAPID keys)
- [x] Panel de pruebas de notificaciones
- [x] Gestión completa de notificaciones existentes (email + push)
- [x] Logs y auditoría del sistema
- [x] Gestión de respaldos y restauración

#### Sprint 2.2: Gestión de Usuarios por Desarrollador ✅ **COMPLETADO**
- [x] CRUD completo de usuarios del sistema
- [x] Asignación de roles a usuarios
- [x] Suspensión/activación de cuentas
- [x] Reseteo de contraseñas
- [x] Visualización de actividad por usuario
- [x] Gestión de permisos especiales

### **Fase 3: Middleware y Protección (Semana 4)**

#### Sprint 3.1: Sistema de Middleware ✅ **COMPLETADO**
- [x] Crear middleware `CheckRole`
- [x] Crear middleware `CheckPermission`
- [x] Crear middleware `DeveloperOnly` para panel exclusivo
- [x] Implementar Gates y Policies para cada controlador (30+ Gates implementados)
- [x] Proteger rutas con middleware de permisos (Todas las rutas principales protegidas)
- [x] Crear sistema de jerarquía de roles (8 roles con niveles jerárquicos 10-100)

#### Sprint 3.2: Sistema de Jerarquía y Restricciones ✅ **COMPLETADO**
- [x] Sistema de jerarquía de roles (niveles 1-99)
- [x] Restricciones basadas en jerarquía para gestión de roles/usuarios
- [x] Helper methods en User model (canManageRole, canManageUser, etc.)
- [x] Validaciones automáticas en controladores
- [x] Filtros de roles/usuarios por nivel de acceso
- [x] Sistema de auditoría completo con tabla role_audits
- [x] Registro automático de cambios en roles y permisos

### **Fase 4: Integración del Sistema RBAC ✅ **COMPLETADO**

#### Sprint 4.1: Aplicación de Permisos ✅ **COMPLETADO**
- [x] RolePermissionMiddleware personalizado implementado
- [x] Blade directives (@canRole, @canPermission, @canManageRole, etc.)
- [x] Integración en rutas principales del sistema
- [x] Seeder RbacPermissionsSeeder con permisos CRUD completos
- [x] Sistema de permisos granulares para todos los módulos
- [x] Compatibilidad con sistema legacy mantenida

#### Sprint 4.2: Dashboard y UX ✅ **COMPLETADO**
- [x] Dashboard inteligente con permisos granulares
- [x] Sistema de permisos para mostrar/ocultar elementos del dashboard
- [x] Página 403 personalizada con navegación mejorada
- [x] Diseño responsive aplicado a todas las vistas developer
- [x] Mobile-first approach con breakpoints consistentes
- [x] Sistema de notificaciones automáticas operativo

#### Sprint 4.3: Correcciones y Optimizaciones ✅ **COMPLETADO**
- [x] Corrección de relaciones User model (created_by foreign keys)
- [x] Fix de eliminación de calidades con foreign key constraints
- [x] Sistema de validaciones descriptivas para operaciones críticas
- [x] Testing de notificaciones automáticas (4 usuarios notificados)
- [x] Documentación completa del sistema RBAC

**Tiempo estimado: 5 semanas** ✅ **COMPLETADO EN TIEMPO ESTIMADO**

**Estado Final RBAC:** Sistema completamente funcional y operativo en producción con sistema de notificaciones automáticas de 3 canales totalmente integrado.

### **Sistema de Notificaciones Automáticas (10/10 Phases Complete - 100% ✅)**

#### Fases Completadas:
- ✅ **Phase 1**: Architecture & Foundations con 3 canales (database/email/push)
- ✅ **Phase 2**: Email System con SMTP y Day.js integration
- ✅ **Phase 3**: Push Notifications con service worker nativo
- ✅ **Phase 4**: Events & Triggers con 10 tareas CRON automatizadas
- ✅ **Phase 5**: Jobs & Queues con procesamiento completo
- ✅ **Phase 6**: CRON System con scheduler de 10 comandos automáticos
- ✅ **Phase 7**: Notification Center UI con timeline AdminLTE
- ✅ **Phase 8**: Advanced Configuration con templates y scheduling
- ✅ **Phase 9**: Testing & Validation de todos los comandos
- ✅ **Phase 10**: Production Deployment operativo en VPS

#### Comandos Automáticos Implementados:
- `notifications:check-inventory` - Alertas de inventario bajo (cada 4h, días laborales)
- `notifications:check-overdue-payments` - Recordatorios de pagos vencidos (diario 9:00)
- `notifications:daily-report` - Reporte diario de operaciones (diario 8:00)
- `notifications:weekly-report` - Resumen semanal comparativo (lunes 6:00)
- `notifications:monthly-report` - Estado financiero mensual (día 1, 7:00)
- `notifications:system-stats` - Estadísticas del sistema (viernes 17:00)
- `notifications:process-scheduled` - Procesamiento de templates (cada 5 min)
- `notifications:cleanup` - Limpieza de notificaciones antiguas (domingo 2:00)

#### Sistema de 3 Canales:
1. **📧 Email**: Correos SMTP con templates responsive
2. **🔔 Push**: Notificaciones de navegador con VAPID
3. **🔔 Database**: "Campanita" en navbar del admin

**Resultado**: Sistema completamente automatizado donde los usuarios reciben alertas por los 3 canales simultáneamente y pueden revisar el historial completo en la campanita al iniciar sesión.

#### Deployment y Optimizaciones Finales:
- ✅ **VPS Deployment**: Sistema desplegado y operativo en 69.62.65.243
- ✅ **Migraciones Ejecutadas**: 6 nuevas tablas Phase 8 creadas exitosamente
- ✅ **Compatibilidad PHP**: Ajustes de sintaxis para PHP 7.4+ (null coalescing operator fix)
- ✅ **Assets Build**: Vite build compilado y optimizado en producción
- ✅ **Cache Management**: View cache y config cache optimizados
- ✅ **Day.js Integration**: Migración completa de moment.js, sin deprecation warnings
- ✅ **Responsive Design**: Todas las vistas developer completamente responsive
- ✅ **Testing Operativo**: 2 notificaciones de prueba enviadas exitosamente via 3 canales

**Estado Final**: Sistema de notificaciones automáticas **100% completo y operativo en producción** con arquitectura robusta lista para escalamiento multi-tenant.

---

## 2. SISTEMA MULTI-TENANT (70% COMPLETADO ✅)

**Nota Importante:** El sistema multi-tenant funcionará con dos niveles de administración:
1. **Super Admin (Desarrollador)**: Control total del sistema, gestión de empresas/tenants y suscripciones
2. **Admin de Empresa**: Gestión de su propia empresa, usuarios y configuración limitada

### **Fase 1: Arquitectura Multi-Tenant (Semana 6-7)** ✅ **COMPLETADO**

#### Sprint 4.1: Estructura de Tenants ✅ **COMPLETADO**
- [x] Crear migración para tabla `tenants` con UUID, slug, plans, status
- [x] Crear migración para tabla `tenant_users` con roles y permisos
- [x] Crear migración para tabla `tenant_settings` con configuración avanzada
- [x] Agregar campo `tenant_id` a todas las tablas principales (suppliers, customers, lots, sales, payments, notifications)
- [x] Crear índices para optimización de consultas multi-tenant

#### Sprint 4.2: Modelos y Scopes ✅ **COMPLETADO**
- [x] Crear modelo `Tenant` con relaciones completas y business logic
- [x] Crear modelo `TenantUser` con sistema de invitaciones y permisos
- [x] Crear modelo `TenantSetting` con soporte de encriptación y tipos
- [x] Implementar trait `BelongsToTenant` con métodos helper
- [x] Crear Global Scope `TenantScope` para filtrado automático
- [x] Modificar modelos existentes para incluir tenant (Supplier, Customer, Lot, Sale, Payment, etc.)
- [x] Actualizar modelo User con relaciones multi-tenant y tenant switching

### **Fase 2: Identificación y Aislamiento (Semana 7)** ✅ **COMPLETADO**

#### Sprint 5.1: Sistema de Identificación ✅ **COMPLETADO**
- [x] Crear middleware `TenantResolver` con multi-strategy identification
- [x] Crear middleware `TenantContext` para configuración dinámica
- [x] Implementar identificación por subdominio y dominio
- [x] Sistema de sesiones por tenant con caché namespace isolation
- [x] Configurar rutas con subdominios y tenant switching

#### Sprint 5.2: Aislamiento de Datos ✅ **COMPLETADO**
- [x] Implementar filtrado automático en queries via Global Scopes
- [x] Verificar aislamiento en todos los controladores con trait BelongsToTenant
- [x] Sistema de validación cross-tenant con middleware protection
- [x] Auditoría de seguridad de datos con logging completo

### **Fase 3: UI y Service Provider (Semana 8)** ✅ **COMPLETADO**

#### Sprint 5.3: User Interface ✅ **COMPLETADO**
- [x] Página de selección de tenant (`/tenant/select`) responsive
- [x] Página de error tenant-not-found con sugerencias
- [x] Tenant switching con validación de permisos
- [x] Indicadores de trial y plan actual
- [x] Mobile-friendly tenant cards con información completa

#### Sprint 5.4: Service Provider y Blade ✅ **COMPLETADO**
- [x] TenantServiceProvider con 15+ features implementadas
- [x] Blade directives: @tenant, @currentTenant, @userCanAccessTenant
- [x] View composers para inyección de información de tenant
- [x] Request macros para acceso al contexto de tenant
- [x] Helper methods para resolución de tenant

#### Sprint 5.5: Seeding y Testing ✅ **COMPLETADO**
- [x] TenantSeeder con 3 tenants de prueba (default, premium, trial)
- [x] 15 categorías de tenant settings configuradas
- [x] Asignación automática de usuarios a tenants
- [x] Integración con roles y permisos existentes
- [x] Testing de switching y aislamiento

### **Fase 4: PayPal Subscription Integration (Semana 9-11)** 🔄 **EN PROCESO**

#### 📋 **Planes de Suscripción Definidos:**

##### 🆓 **TRIAL - 7 días gratis**
- **Usuarios**: 1
- **Lotes**: 50 máximo  
- **Almacenamiento**: 500MB
- **Funciones**: Reportes básicos
- **Soporte**: No incluido
- **Flujo**: Registro → Trial automático → Cobro PayPal después de 7 días

##### 🥉 **BASIC - $29 USD/mes**
- **Usuarios**: 5
- **Lotes**: 500/mes
- **Almacenamiento**: 2GB
- **Funciones**: Todos los reportes, notificaciones email
- **Soporte**: Email
- **Target**: Centros de acopio pequeños

##### 🥈 **PREMIUM - $79 USD/mes**
- **Usuarios**: 25
- **Lotes**: 2,000/mes
- **Almacenamiento**: 10GB
- **Funciones**: Reportes avanzados, notificaciones push + SMS, API access, backup automático
- **Soporte**: Prioritario
- **Target**: Empresas medianas

##### 🥇 **ENTERPRISE - $199 USD/mes**
- **Usuarios**: 100
- **Lotes**: Ilimitados
- **Almacenamiento**: 50GB
- **Funciones**: Reportes personalizados, multi-ubicación, API completo, marca personalizada
- **Soporte**: Telefónico 24/7
- **Target**: Empresas grandes

##### 🏢 **CORPORATE - Precio personalizado**
- **Usuarios**: Ilimitados
- **Multi-tenant**: Ilimitado
- **Almacenamiento**: Ilimitado
- **Funciones**: Servidor dedicado, SLA garantizado
- **Soporte**: Dedicado
- **Target**: Corporativos

#### Sprint 6.1: PayPal API Configuration
- [ ] Configurar credenciales PayPal (sandbox y production)
- [ ] Instalar SDK de PayPal para Laravel
- [ ] Crear migraciones para subscription_payments y payment_logs
- [ ] Implementar servicio PayPalService

#### Sprint 6.2: Subscription Plans Creation
- [ ] Crear planes en PayPal Dashboard
- [ ] Sincronizar planes con base de datos local
- [ ] Crear comando artisan para sync de planes
- [ ] Implementar modelo Subscription

#### Sprint 6.3: Tenant Registration with Trial
- [ ] Flujo de registro unificado (usuario + tenant + trial)
- [ ] Activación automática de trial 7 días
- [ ] Envío de emails de bienvenida y recordatorios
- [ ] Dashboard de estado de suscripción

#### Sprint 6.4: PayPal Webhooks
- [ ] Configurar webhooks endpoints
- [ ] Procesar eventos: BILLING.SUBSCRIPTION.ACTIVATED, CANCELLED, SUSPENDED
- [ ] Sistema de logs y auditoría de pagos
- [ ] Notificaciones automáticas de cambios de estado

#### Sprint 6.5: Automatic Subscription Monitoring
- [ ] CRON job para verificar estados de suscripción
- [ ] Alertas de vencimiento (3 días antes, 1 día antes)
- [ ] Reintentos automáticos de cobro
- [ ] Reportes de suscripciones para super_admin

#### Sprint 6.6: Account Suspension System
- [ ] Suspensión automática por falta de pago
- [ ] Período de gracia configurable
- [ ] Sistema de reactivación con pago
- [ ] Backup de datos antes de suspensión

#### Sprint 6.7: Subscription Management Panel
- [ ] Panel de gestión en `/developer/subscriptions`
- [ ] Cambio de planes (upgrade/downgrade)
- [ ] Historial de pagos y facturas
- [ ] Métricas de MRR y churn rate

### **Fase 5: Gestión Avanzada de Tenants (Semana 12-13)**

#### Sprint 7.1: Panel de Administración (Super Admin)
- [ ] Dashboard de tenants en `/developer/tenants`
- [ ] Métricas de uso por tenant (usuarios, storage, operaciones)
- [ ] Suspensión/activación manual de tenants
- [ ] Exportación de datos por tenant
- [ ] Sistema de respaldos por tenant

#### Sprint 7.2: Administración por Tenant (Admin Empresa)
- [ ] Panel limitado para admin de empresa
- [ ] Gestión de usuarios de su empresa
- [ ] Sistema de invitaciones internas
- [ ] Configuración de marca y personalización
- [ ] Límites según plan contratado

### **Fase 6: Testing y Deployment (Semana 14)**

#### Sprint 8.1: Testing Multi-Tenant + PayPal
- [ ] Tests de integración PayPal sandbox
- [ ] Tests de aislamiento entre tenants
- [ ] Tests de límites por plan
- [ ] Tests de suspensión y reactivación
- [ ] Performance testing con múltiples tenants

#### Sprint 8.2: Production Deployment
- [ ] Migración de datos a estructura multi-tenant
- [ ] Configuración de subdominios en producción
- [ ] PayPal production credentials
- [ ] Documentación completa del sistema
- [ ] Training para usuarios admin

**Tiempo estimado actualizado: 8 semanas (antes 6)**

---

## 3. SISTEMA PWA (Progressive Web App)

### **Fase 1: Fundamentos PWA (Semana 11-12)**

#### Sprint 8.1: Configuración Base
- [ ] Crear archivo `manifest.json`
- [ ] Generar íconos en todos los tamaños requeridos
- [ ] Configurar meta tags para PWA
- [ ] Implementar Service Worker básico

#### Sprint 8.2: Estrategia de Cache
- [ ] Definir recursos críticos para cache
- [ ] Implementar cache de páginas principales
- [ ] Cache de assets estáticos (CSS, JS, imágenes)
- [ ] Estrategia de actualización de cache

### **Fase 2: Funcionalidad Offline (Semana 13-14)**

#### Sprint 9.1: Base de Datos Local
- [ ] Implementar IndexedDB para almacenamiento local
- [ ] Estructura de datos offline
- [ ] Sistema de sincronización básico
- [ ] Manejo de estados online/offline

#### Sprint 9.2: Operaciones Offline
- [ ] Crear/editar lotes offline
- [ ] Registrar ventas offline
- [ ] Cola de sincronización
- [ ] Resolución de conflictos básica

### **Fase 3: Sincronización Avanzada (Semana 15)**

#### Sprint 10.1: Background Sync
- [ ] Implementar Background Sync API
- [ ] Sistema de retry automático
- [ ] Priorización de sincronización
- [ ] Notificaciones de estado de sync

#### Sprint 10.2: APIs de Sincronización
- [ ] Endpoints REST para sincronización
- [ ] Manejo de conflictos en servidor
- [ ] Versionado de datos
- [ ] Logs de sincronización

### **Fase 4: Instalación y UX (Semana 16)**

#### Sprint 11.1: Instalación
- [ ] Botón de instalación personalizado
- [ ] Detección de soporte PWA
- [ ] Instrucciones por dispositivo
- [ ] Analytics de instalación

#### Sprint 11.2: Optimización Mobile
- [ ] Interfaz optimizada para móvil
- [ ] Gestos touch específicos
- [ ] Modo landscape/portrait
- [ ] Performance optimization

### **Fase 5: Features Avanzadas (Semana 17)**

#### Sprint 12.1: Push Notifications
- [ ] Integrar sistema de notificaciones existente con PWA
- [ ] Configuración de VAPID keys
- [ ] Subscripción automática
- [ ] Notificaciones contextuales

#### Sprint 12.2: Testing y Deploy
- [ ] Tests en diferentes dispositivos
- [ ] Performance testing
- [ ] Validación PWA completa
- [ ] Documentación de usuario

**Tiempo estimado: 7 semanas**

---

## CRONOGRAMA GENERAL ACTUALIZADO

| Semanas | Funcionalidad | Status | Progreso | Entregables |
|---------|---------------|---------|----------|-------------|
| ✅ 1-5 | **RBAC + Notificaciones** | **COMPLETADO** | 100% | Sistema completo de roles, permisos y notificaciones automáticas de 3 canales |
| ✅ 6-8 | **Multi-Tenant Core** | **COMPLETADO** | 70% | Base de datos, modelos, middleware, UI y service provider funcionando |
| 🔄 9-14 | **PayPal + Tenant Admin** | **EN PROCESO** | 0% | Suscripciones PayPal, panel admin tenants, testing |
| ⏳ 15-20 | **PWA** | PENDIENTE | 0% | App web instalable con funcionalidad offline |

**Tiempo total estimado: 20 semanas (5 meses)**
**Progreso actual: 8/20 semanas completadas (40%)**

### 📊 **Resumen de Progreso por Sistema:**

| Sistema | Completado | En Proceso | Pendiente | Total |
|---------|------------|------------|-----------|-------|
| **RBAC** | 100% ✅ | - | - | 100% |
| **Notificaciones** | 100% ✅ | - | - | 100% |
| **Multi-Tenant** | 70% | 30% | - | 100% |
| **PayPal Subs** | - | - | 100% | 100% |
| **PWA** | - | - | 100% | 100% |

### 🎯 **Hitos Alcanzados:**

✅ **Agosto 2025 - Semana 1-5:**
- Sistema RBAC completo con 8 roles y 52 permisos
- Panel de desarrollador exclusivo con 20+ funcionalidades
- Sistema de notificaciones automáticas de 3 canales
- 10 comandos CRON automatizados funcionando
- Deploy en producción VPS exitoso

✅ **Agosto 2025 - Semana 6-8:**
- Arquitectura multi-tenant completa (DB + Models)
- Sistema de middleware para tenant resolution
- UI para selección y switching de tenants
- Service Provider con 15+ Blade directives
- 3 tenants de prueba configurados y funcionando

🔄 **En Proceso - Semana 9-14:**
- Integración con PayPal Subscriptions API
- 5 planes de suscripción definidos (Trial → Corporate)
- Sistema de registro unificado usuario + tenant
- Panel de gestión de suscripciones
- Testing integral y deployment

⏳ **Pendiente - Semana 15-20:**
- Progressive Web App completa
- Funcionalidad offline con IndexedDB
- Service Worker y Background Sync
- Push notifications PWA
- App instalable en dispositivos móviles

---

## RECURSOS NECESARIOS

### Desarrollo
- 1 Desarrollador Full-Stack (PHP/Laravel + JavaScript)
- 1 Desarrollador Frontend (opcional para PWA)

### Testing
- Dispositivos móviles para pruebas PWA
- Múltiples subdominios para testing multi-tenant
- Ambiente de staging completo

### Infraestructura
- Servidor de desarrollo con subdominios
- Redis para cache y queues
- SSL certificates para subdominios

---

## RIESGOS Y MITIGATION

### RBAC
- **Riesgo**: Romper funcionalidad existente
- **Mitigación**: Tests exhaustivos y rollback plan

### Multi-Tenant
- **Riesgo**: Data leakage entre tenants
- **Mitigación**: Auditorías de seguridad frecuentes

### PWA
- **Riesgo**: Incompatibilidad con dispositivos antiguos
- **Mitigación**: Progressive enhancement y fallbacks

---

## CRITERIOS DE ÉXITO

### RBAC ✅ **LOGRADO**
- [x] Usuarios solo acceden a funciones permitidas
- [x] Interface se adapta según permisos
- [x] Performance no se degrada
- [x] Sistema de 8 roles jerárquicos funcionando
- [x] 52 permisos granulares aplicados

### Multi-Tenant 🔄 **EN PROGRESO**
- [x] Aislamiento 100% entre tenants
- [x] Identificación automática por dominio/subdominio
- [x] Middleware y Service Provider completos
- [ ] Migración sin pérdida de datos (pendiente)
- [ ] Panel de gestión de tenants (pendiente)

### PayPal Integration ⏳ **PENDIENTE**
- [ ] 5 planes de suscripción configurados
- [ ] Trial de 7 días automático
- [ ] Suspensión automática por falta de pago
- [ ] Panel de gestión de suscripciones
- [ ] Webhooks procesando eventos

### PWA ⏳ **PENDIENTE**
- [ ] Instalable en dispositivos móviles
- [ ] Funciona offline completamente
- [ ] Sincronización automática sin conflictos
- [ ] Performance similar a app nativa

---

*Documento creado: Agosto 2025*  
*Última actualización: 13 de Agosto 2025*  
*Autor: Daniel Rivera - Kreativos Pro*  
*Versión: 2.0*  

## 📈 **Changelog v2.0:**
- ✅ Sistema Multi-Tenant actualizado a 70% completado
- ✅ Agregados planes de suscripción PayPal con precios
- ✅ Definido flujo de registro usuario + tenant + trial
- ✅ Actualizado cronograma general (20 semanas total)
- ✅ Marcados hitos alcanzados hasta la fecha
- ✅ Agregada tabla de progreso por sistema
- ✅ Criterios de éxito actualizados con estado actual