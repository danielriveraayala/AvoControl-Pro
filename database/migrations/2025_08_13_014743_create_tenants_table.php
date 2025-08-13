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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // UUID para identificación única
            $table->string('name'); // Nombre de la empresa/tenant
            $table->string('slug')->unique(); // Subdominio/slug único
            $table->string('domain')->nullable(); // Dominio personalizado (opcional)
            $table->string('email')->unique(); // Email principal del tenant
            $table->string('phone')->nullable(); // Teléfono de contacto
            $table->text('address')->nullable(); // Dirección física
            $table->string('logo_path')->nullable(); // Ruta del logo
            $table->string('plan')->default('basic'); // Plan de suscripción
            $table->enum('status', ['active', 'inactive', 'suspended', 'pending'])->default('pending');
            $table->json('settings')->nullable(); // Configuraciones específicas del tenant
            $table->json('features')->nullable(); // Características habilitadas por plan
            $table->integer('max_users')->default(10); // Límite de usuarios según plan
            $table->integer('max_storage_mb')->default(1000); // Límite de almacenamiento
            $table->datetime('trial_ends_at')->nullable(); // Fin del período de prueba
            $table->datetime('subscription_expires_at')->nullable(); // Expiración de suscripción
            $table->timestamp('last_activity_at')->nullable(); // Última actividad
            $table->timestamps();
            $table->softDeletes(); // Soft delete para mantener historial
            
            // Índices para optimización
            $table->index(['status', 'plan']);
            $table->index(['slug', 'status']);
            $table->index('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
