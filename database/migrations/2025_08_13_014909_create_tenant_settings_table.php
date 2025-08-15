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
        Schema::create('tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('key'); // Clave de la configuración
            $table->text('value')->nullable(); // Valor de la configuración
            $table->string('type')->default('string'); // Tipo: string, integer, boolean, json, array
            $table->text('description')->nullable(); // Descripción de la configuración
            $table->string('category')->default('general'); // Categoría: general, email, notifications, etc.
            $table->boolean('is_public')->default(false); // Si es visible para usuarios no admin
            $table->boolean('is_encrypted')->default(false); // Si el valor debe estar encriptado
            $table->timestamps();
            
            // Índices para optimización
            $table->unique(['tenant_id', 'key']); // Una configuración única por tenant
            $table->index(['tenant_id', 'category']);
            $table->index(['tenant_id', 'is_public']);
            $table->index(['key', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_settings');
    }
};
