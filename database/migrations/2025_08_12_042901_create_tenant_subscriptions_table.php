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
        Schema::create('tenant_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_name', 150); // Nombre de la empresa/tenant
            $table->string('tenant_domain', 100)->unique(); // Subdominio único
            $table->string('contact_name', 150); // Nombre del contacto principal
            $table->string('contact_email', 150)->unique(); // Email del contacto
            $table->string('contact_phone', 20)->nullable(); // Teléfono del contacto
            
            // Plan y suscripción
            $table->foreignId('tenant_plan_id')->constrained()->onDelete('restrict');
            $table->enum('status', ['trial', 'active', 'suspended', 'cancelled', 'expired'])->default('trial');
            $table->datetime('trial_ends_at')->nullable(); // Fin del período de prueba
            $table->datetime('current_period_start')->nullable(); // Inicio período actual
            $table->datetime('current_period_end')->nullable(); // Fin período actual
            $table->datetime('suspended_at')->nullable(); // Fecha de suspensión
            $table->text('suspension_reason')->nullable(); // Razón de suspensión
            
            // Uso actual (para control de límites)
            $table->integer('current_users')->default(0);
            $table->integer('current_suppliers')->default(0);
            $table->integer('current_customers')->default(0);
            $table->integer('lots_this_month')->default(0);
            $table->integer('sales_this_month')->default(0);
            $table->decimal('storage_used_gb', 8, 3)->default(0); // GB usados
            
            // Datos de facturación
            $table->string('billing_name', 150)->nullable();
            $table->text('billing_address')->nullable();
            $table->string('tax_id', 50)->nullable(); // RFC/Tax ID
            
            // Configuración específica del tenant
            $table->json('tenant_config')->nullable(); // Configuraciones personalizadas
            $table->json('feature_flags')->nullable(); // Características habilitadas/deshabilitadas
            
            // Metadatos de gestión
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->datetime('last_usage_update')->nullable(); // Última actualización de uso
            $table->text('notes')->nullable(); // Notas administrativas
            
            $table->timestamps();
            
            // Índices
            $table->index(['status', 'current_period_end']);
            $table->index(['tenant_domain']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_subscriptions');
    }
};
