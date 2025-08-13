<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verificar y agregar tenant_id a suppliers si no existe
        if (!Schema::hasColumn('suppliers', 'tenant_id')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade')->after('id');
                $table->index(['tenant_id', 'name']);
            });
        }

        // Verificar y agregar tenant_id a customers si no existe
        if (!Schema::hasColumn('customers', 'tenant_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade')->after('id');
                $table->index(['tenant_id', 'name']);
            });
        }

        // Verificar y agregar tenant_id a quality_grades si no existe
        if (!Schema::hasColumn('quality_grades', 'tenant_id')) {
            Schema::table('quality_grades', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade')->after('id');
                $table->unique(['tenant_id', 'name']); // Nombres únicos por tenant
            });
        }

        // Verificar y agregar tenant_id a lots si no existe
        if (!Schema::hasColumn('lots', 'tenant_id')) {
            Schema::table('lots', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade')->after('id');
                $table->index(['tenant_id', 'status']);
                $table->index(['tenant_id', 'supplier_id']);
                $table->index(['tenant_id', 'quality_grade_id']);
            });
        }

        // Verificar y agregar tenant_id a sales si no existe
        if (!Schema::hasColumn('sales', 'tenant_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade')->after('id');
                $table->index(['tenant_id', 'customer_id']);
                $table->index(['tenant_id', 'sale_date']);
                $table->index(['tenant_id', 'status']);
            });
        }

        // Verificar y agregar tenant_id a sale_items si no existe
        if (!Schema::hasColumn('sale_items', 'tenant_id')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade')->after('id');
                $table->index(['tenant_id', 'sale_id']);
                $table->index(['tenant_id', 'lot_id']);
            });
        }

        // Verificar y agregar tenant_id a payments si no existe
        if (!Schema::hasColumn('payments', 'tenant_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade')->after('id');
                $table->index(['tenant_id', 'type']);
                $table->index(['tenant_id', 'payment_date']);
                $table->index(['tenant_id', 'payable_type', 'payable_id']);
            });
        }

        // Verificar y agregar tenant_id a notifications si no existe
        if (!Schema::hasColumn('notifications', 'tenant_id')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade')->after('id');
                $table->index(['tenant_id', 'notifiable_id']);
                $table->index(['tenant_id', 'type']);
            });
        }

        // Verificar y agregar current_tenant_id a users si no existe
        if (!Schema::hasColumn('users', 'current_tenant_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('current_tenant_id')->nullable()->constrained('tenants')->onDelete('set null')->after('role');
                $table->index('current_tenant_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Esta migración es una corrección, no se debería hacer rollback
        // Los downs están en las migraciones originales
        // Solo se ejecutará si es absolutamente necesario
    }
};
