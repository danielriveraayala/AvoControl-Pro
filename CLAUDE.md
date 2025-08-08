# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

AvoControl Pro is a Laravel-based web application for managing avocado purchasing and sales operations for a packaging company in Uruapan. The system tracks lot purchases from suppliers, sales to customers, payments, and provides comprehensive reporting and analytics.

**Status**: Development environment configured and basic structure implemented.

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
- Default admin user: `admin@avocontrol.com` / `password123`
- Default vendedor: `vendedor@avocontrol.com` / `password123`
- Default contador: `contador@avocontrol.com` / `password123`

### Current Implementation Status

**✅ Completed:**
- Laravel 8.6 installation with Breeze authentication
- MySQL database configuration
- Livewire 2.12, Tailwind CSS, Alpine.js setup
- All models and migrations (Suppliers, Customers, Lots, Sales, SaleItems, Payments)
- User roles system (admin, vendedor, contador)
- Comprehensive seeders with realistic test data
- Basic controllers structure
- Dashboard with statistics
- Route structure

**🔄 In Progress:**
- Controller implementations
- Livewire components
- Complete CRUD operations
- Report generation

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

- Role-based access control (admin, vendedor, contador)
- Payment operations require special permissions
- Audit trail for financial transactions
- Soft deletes for data integrity