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
        // Agregar tenant_id a tabla suppliers
        Schema::table('suppliers', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade')->after('id');
            $table->index(['tenant_id', 'name']);
        });

        // Agregar tenant_id a tabla customers
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade')->after('id');
            $table->index(['tenant_id', 'name']);
        });

        // Agregar tenant_id a tabla quality_grades
        Schema::table('quality_grades', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade')->after('id');
            $table->unique(['tenant_id', 'name']); // Nombres únicos por tenant
        });

        // Agregar tenant_id a tabla lots
        Schema::table('lots', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade')->after('id');
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'supplier_id']);
            $table->index(['tenant_id', 'quality_id']);
        });

        // Agregar tenant_id a tabla sales
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade')->after('id');
            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'sale_date']);
            $table->index(['tenant_id', 'status']);
        });

        // Agregar tenant_id a tabla sale_items
        Schema::table('sale_items', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade')->after('id');
            $table->index(['tenant_id', 'sale_id']);
            $table->index(['tenant_id', 'lot_id']);
        });

        // Agregar tenant_id a tabla payments
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade')->after('id');
            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'payment_date']);
            $table->index(['tenant_id', 'payable_type', 'payable_id']);
        });

        // Agregar tenant_id a tabla notifications (para el sistema multi-tenant)
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade')->after('id');
            $table->index(['tenant_id', 'notifiable_id']);
            $table->index(['tenant_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir en orden inverso para respetar foreign keys
        
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id', 'notifiable_id']);
            $table->dropIndex(['tenant_id', 'type']);
            $table->dropColumn('tenant_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id', 'type']);
            $table->dropIndex(['tenant_id', 'payment_date']);
            $table->dropIndex(['tenant_id', 'payable_type', 'payable_id']);
            $table->dropColumn('tenant_id');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id', 'sale_id']);
            $table->dropIndex(['tenant_id', 'lot_id']);
            $table->dropColumn('tenant_id');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id', 'customer_id']);
            $table->dropIndex(['tenant_id', 'sale_date']);
            $table->dropIndex(['tenant_id', 'status']);
            $table->dropColumn('tenant_id');
        });

        Schema::table('lots', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id', 'status']);
            $table->dropIndex(['tenant_id', 'supplier_id']);
            $table->dropIndex(['tenant_id', 'quality_id']);
            $table->dropColumn('tenant_id');
        });

        Schema::table('quality_grades', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropUnique(['tenant_id', 'name']);
            $table->dropColumn('tenant_id');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id', 'name']);
            $table->dropColumn('tenant_id');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id', 'name']);
            $table->dropColumn('tenant_id');
        });
    }
};
