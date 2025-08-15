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
        Schema::create('tenant_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('role_within_tenant')->default('member'); // Rol específico dentro del tenant
            $table->json('permissions')->nullable(); // Permisos específicos del tenant
            $table->enum('status', ['active', 'inactive', 'invited', 'suspended'])->default('active');
            $table->timestamp('invited_at')->nullable(); // Fecha de invitación
            $table->timestamp('joined_at')->nullable(); // Fecha en que aceptó la invitación
            $table->timestamp('last_access_at')->nullable(); // Último acceso al tenant
            $table->json('settings')->nullable(); // Configuraciones específicas del usuario en el tenant
            $table->timestamps();
            
            // Índices para optimización y constraints
            $table->unique(['tenant_id', 'user_id']); // Un usuario no puede estar duplicado en el mismo tenant
            $table->index(['tenant_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('role_within_tenant');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_users');
    }
};
