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
        Schema::create('paypal_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('webhook_id')->nullable(); // PayPal webhook ID
            $table->string('event_type'); // BILLING.SUBSCRIPTION.ACTIVATED, etc.
            $table->string('event_version')->nullable();
            $table->string('resource_type')->nullable(); // subscription, payment, etc.
            $table->string('resource_id')->nullable(); // PayPal resource ID
            $table->unsignedBigInteger('subscription_id')->nullable(); // Local subscription ID
            $table->unsignedBigInteger('tenant_id')->nullable(); // Related tenant
            $table->json('payload'); // Complete webhook payload
            $table->enum('status', ['received', 'processed', 'failed', 'ignored'])->default('received');
            $table->text('processing_notes')->nullable(); // Success/error messages
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('received_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['event_type', 'created_at']);
            $table->index(['subscription_id', 'created_at']);
            $table->index(['tenant_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index('webhook_id');
            $table->index('resource_id');

            // Foreign keys
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('set null');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paypal_webhook_logs');
    }
};
