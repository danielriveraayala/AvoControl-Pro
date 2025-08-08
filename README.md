# 🥑 AvoControl Pro

Sistema web profesional de gestión de aguacate desarrollado con Laravel 11 para empresas empacadoras en Uruapan, Michoacán.

## 📋 Resumen 

AvoControl Pro es una aplicación web completa que gestiona las operaciones de compra y venta de aguacate, ofreciendo un control integral desde la adquisición de lotes hasta las ventas finales, con análisis de rentabilidad y reportes detallados.

### ✨ Características Principales

- 📦 **Gestión de Lotes**: Control completo del inventario con códigos únicos, calidades y trazabilidad
- 👥 **Proveedores y Clientes**: Administración de contactos con históricos de transacciones  
- 💰 **Sistema de Pagos**: Control de cuentas por pagar/cobrar con múltiples métodos de pago
- 📊 **Dashboard Analítico**: Métricas en tiempo real con gráficos interactivos
- 📈 **Reportes Avanzados**: Análisis de rentabilidad, inventarios y flujo de caja
- 🔐 **Control de Acceso**: Sistema de roles (Admin, Vendedor, Contador)
- 📱 **Diseño Responsive**: Interfaz moderna con AdminLTE3

## 🛠 Stack Tecnológico

### Backend
- **Laravel 11.x** - Framework PHP
- **PHP 8.2+** - Lenguaje de programación
- **MySQL 8.0+** - Base de datos
- **Laravel Breeze** - Autenticación

### Frontend  
- **Livewire 3.x** - Componentes interactivos
- **Alpine.js** - JavaScript reactivo
- **Tailwind CSS** - Framework CSS
- **AdminLTE3** - Template administrativo
- **Chart.js** - Gráficos y visualizaciones
- **Vite** - Build tool moderno

## 🚀 Instalación Rápida

### Requisitos
- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL 8.0+

### Configuración
```bash
# Clonar repositorio
git clone <repository-url>
cd avocontrol

# Instalar dependencias PHP
composer install

# Instalar dependencias Node
npm install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Configurar base de datos en .env
DB_DATABASE=avocontrol
DB_USERNAME=root
DB_PASSWORD=tu_password

# Ejecutar migraciones y seeders
php artisan migrate --seed

# Compilar assets
npm run build

# Iniciar servidor
php artisan serve
```

## 👤 Usuarios de Prueba

| Rol | Email | Contraseña |
|-----|-------|------------|
| Admin | admin@avocontrol.com | password123 |
| Vendedor | vendedor@avocontrol.com | password123 |
| Contador | contador@avocontrol.com | password123 |

## 📊 Arquitectura del Sistema

### Entidades Principales

**Lotes** → Representa compras de aguacate con:
- Códigos únicos auto-generados (`LOT-YYYYMMDD-###`)
- Control de peso total, vendido y disponible
- Estados: activo, parcial, vendido, dañado
- Grados de calidad: premium, exportación, nacional, industrial

**Ventas** → Transacciones con clientes incluyendo:
- Múltiples lotes por venta (relación muchos a muchos)
- Estados de pago y entrega
- Facturación y fechas de vencimiento

**Pagos** → Sistema polimórfico que maneja:
- Pagos a proveedores (egresos)  
- Cobros a clientes (ingresos)
- Múltiples métodos de pago
- Seguimiento de saldos

### Flujo de Negocio

```
Proveedor → Lote → Venta → Cliente
    ↓         ↓       ↓       ↓
  Pagos   Inventario Factura Cobros
```

## 🏗 Arquitectura de Código

### Servicios de Negocio
- **LotService**: Gestión de inventario y códigos de lote
- **SaleService**: Procesamiento de ventas y asignación de lotes  
- **ReportService**: Generación de métricas y análisis
- **PaymentService**: Control de pagos y balances

### Patrones Implementados
- **Service Layer**: Lógica de negocio separada de controladores
- **Repository Pattern**: Abstracción de acceso a datos (futuro)
- **Observer Pattern**: Eventos automáticos en cambios de estado
- **Polymorphic Relations**: Sistema de pagos flexible

## 📈 Características Avanzadas

### Dashboard Analítico
- Métricas de inventario en tiempo real
- Indicadores financieros clave  
- Gráficos de distribución por calidad
- Alertas automáticas del sistema

### Sistema de Reportes
- Análisis de rentabilidad por lote
- Reportes de flujo de caja
- Ranking de proveedores/clientes  
- Exportación a PDF/Excel (próximamente)

### Optimizaciones de Rendimiento  
- Carga diferida (lazy loading) en relaciones
- Índices optimizados en base de datos
- Caché de consultas frecuentes
- Consultas optimizadas con Eloquent ORM

## 🔒 Seguridad

- Autenticación con Laravel Breeze
- Autorización basada en roles y políticas
- Protección CSRF en todos los formularios
- Validación de datos en servidor
- Soft deletes para integridad de datos

## 🧪 Testing

```bash
# Ejecutar suite de pruebas
php artisan test

# Cobertura de código
php artisan test --coverage
```

## 📚 Comandos de Desarrollo

```bash
# Desarrollo
php artisan serve              # Servidor local
npm run dev                    # Watch assets
php artisan queue:work        # Procesar colas

# Producción  
npm run build                 # Build assets
php artisan optimize          # Optimizar aplicación

# Base de datos
php artisan migrate:fresh --seed  # Reset completo
php artisan optimize:clear        # Limpiar cachés
```

## 🌟 Roadmap

### Próximas Funcionalidades
- [ ] API RESTful completa
- [ ] Aplicación móvil
- [ ] Integración con sistemas contables
- [ ] Reportes avanzados con filtros dinámicos
- [ ] Sistema de notificaciones automáticas
- [ ] Backup automático de datos

### Mejoras Técnicas
- [ ] Migración completa a Livewire 3
- [ ] Implementación de Jobs para procesos pesados  
- [ ] Sistema de caché distribuido con Redis
- [ ] Testing automatizado con GitHub Actions

## 🤝 Contribución

Este proyecto fue desarrollado para una empresa específica, pero está abierto a mejoras y sugerencias.

## 📝 Licencia

Este proyecto es software propietario desarrollado específicamente para operaciones de empaque de aguacate.

---

**Desarrollado con ❤️ para la industria del aguacate en Uruapan, Michoacán**

🤖 *Generado con [Claude Code](https://claude.ai/code)*
