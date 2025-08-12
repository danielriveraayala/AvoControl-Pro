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
        Schema::create('tenant_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique(); // Básico, Pro, Enterprise
            $table->string('display_name', 150); // Nombre para mostrar
            $table->text('description')->nullable(); // Descripción del plan
            $table->decimal('price', 10, 2); // Precio mensual
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            
            // Límites del plan
            $table->integer('max_users')->default(10); // Usuarios máximos
            $table->integer('max_suppliers')->default(100); // Proveedores máximos
            $table->integer('max_customers')->default(100); // Clientes máximos
            $table->integer('max_lots_per_month')->default(500); // Lotes por mes
            $table->integer('max_sales_per_month')->default(1000); // Ventas por mes
            $table->integer('storage_limit_gb')->default(5); // Almacenamiento en GB
            
            // Características del plan (JSON)
            $table->json('features')->nullable(); // Características habilitadas
            
            // Estado y configuración
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false); // Plan destacado
            $table->integer('sort_order')->default(0); // Orden de visualización
            
            // Metadatos
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_plans');
    }
};
