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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // trial, basic, premium, enterprise, custom
            $table->string('name'); // Nombre del plan
            $table->text('description')->nullable(); // Descripción del plan
            $table->decimal('price', 8, 2); // Precio mensual
            $table->string('currency', 3)->default('USD'); // Moneda
            $table->string('billing_cycle')->default('monthly'); // monthly, yearly
            $table->integer('trial_days')->default(0); // Días de prueba gratis
            $table->boolean('is_active')->default(true); // Plan activo/inactivo
            $table->boolean('is_featured')->default(false); // Plan destacado
            $table->boolean('is_custom')->default(false); // Plan personalizado
            $table->string('paypal_plan_id')->nullable(); // ID del plan en PayPal
            $table->string('stripe_plan_id')->nullable(); // ID del plan en Stripe (futuro)
            $table->integer('sort_order')->default(0); // Orden de aparición
            
            // Límites del plan
            $table->integer('max_users')->nullable(); // Máximo usuarios (-1 = ilimitado)
            $table->integer('max_lots_per_month')->nullable(); // Máximo lotes por mes (-1 = ilimitado)
            $table->integer('max_storage_gb')->nullable(); // Máximo almacenamiento en GB (-1 = ilimitado)
            $table->integer('max_locations')->default(1); // Máximo ubicaciones/sucursales
            
            // Funcionalidades del plan (features)
            $table->json('features'); // Array de funcionalidades disponibles
            
            // Metadatos
            $table->json('metadata')->nullable(); // Información adicional
            $table->string('color')->default('#3B82F6'); // Color del plan en UI
            $table->string('icon')->default('fas fa-box'); // Icono del plan
            
            $table->timestamps();
            
            // Índices
            $table->index(['is_active', 'sort_order']);
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
