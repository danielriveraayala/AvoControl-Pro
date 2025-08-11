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
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('endpoint', 500); // URL del endpoint del navegador
            $table->text('p256dh_key'); // Clave pública para encriptar
            $table->text('auth_token'); // Token de autenticación
            $table->string('browser')->nullable(); // Chrome, Firefox, Edge, etc.
            $table->string('device')->nullable(); // Desktop, Mobile, Tablet
            $table->string('user_agent')->nullable(); // User agent completo
            $table->ipAddress('ip_address')->nullable(); // IP desde donde se suscribió
            $table->boolean('active')->default(true); // Subscripción activa
            $table->timestamp('last_used_at')->nullable(); // Última vez que se usó
            $table->json('preferences')->nullable(); // Preferencias de notificación
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['user_id', 'active']);
            $table->index(['endpoint'], 'push_subscriptions_endpoint_index');
            $table->unique(['user_id', 'endpoint'], 'user_endpoint_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
