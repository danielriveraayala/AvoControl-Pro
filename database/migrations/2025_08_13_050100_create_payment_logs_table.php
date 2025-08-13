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
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            
            // Related entities (nullable for system-wide logs)
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->onDelete('cascade');
            $table->foreignId('subscription_payment_id')->nullable()->constrained('subscription_payments')->onDelete('cascade');
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Log type and event
            $table->enum('type', [
                'api_request',      // PayPal API request
                'api_response',     // PayPal API response
                'webhook',          // PayPal webhook event
                'system',           // System action
                'user_action',      // User-initiated action
                'cron',            // Scheduled job action
                'error',           // Error event
                'debug'            // Debug information
            ]);
            
            $table->enum('event', [
                // PayPal Events
                'subscription_created',
                'subscription_activated',
                'subscription_cancelled',
                'subscription_suspended',
                'subscription_reactivated',
                'payment_completed',
                'payment_failed',
                'payment_refunded',
                'webhook_received',
                
                // System Events
                'trial_started',
                'trial_ending_soon',
                'trial_expired',
                'plan_upgraded',
                'plan_downgraded',
                'account_suspended',
                'account_reactivated',
                'grace_period_started',
                'grace_period_ended',
                
                // User Events
                'user_subscribed',
                'user_cancelled',
                'user_updated_payment',
                'user_accessed_dashboard',
                
                // Admin Events
                'admin_suspended_account',
                'admin_reactivated_account',
                'admin_refunded_payment',
                'admin_extended_trial',
                
                // Errors
                'api_error',
                'webhook_error',
                'payment_processing_error',
                'sync_error'
            ]);
            
            // Log level
            $table->enum('level', ['debug', 'info', 'warning', 'error', 'critical'])->default('info');
            
            // Message and context
            $table->string('message'); // Short description
            $table->text('description')->nullable(); // Detailed description
            $table->json('context')->nullable(); // Additional context data
            
            // PayPal specific data
            $table->string('paypal_request_id')->nullable(); // PayPal-Request-Id header
            $table->string('paypal_correlation_id')->nullable(); // PayPal correlation ID
            $table->string('paypal_debug_id')->nullable(); // PayPal debug ID
            $table->json('request_data')->nullable(); // Request payload
            $table->json('response_data')->nullable(); // Response payload
            $table->integer('response_code')->nullable(); // HTTP response code
            
            // Timing information
            $table->decimal('execution_time', 8, 3)->nullable(); // Execution time in seconds
            $table->timestamp('occurred_at')->useCurrent(); // When the event occurred
            
            // Request information
            $table->string('ip_address', 45)->nullable(); // IPv4 or IPv6
            $table->string('user_agent')->nullable();
            $table->string('session_id')->nullable();
            
            // Processing status
            $table->boolean('processed')->default(false); // If action was taken based on this log
            $table->timestamp('processed_at')->nullable();
            $table->string('processed_by')->nullable(); // Who/what processed this log
            
            $table->timestamps();
            
            // Indexes for performance and searching
            $table->index(['subscription_id', 'occurred_at']);
            $table->index(['tenant_id', 'type', 'occurred_at']);
            $table->index(['event', 'occurred_at']);
            $table->index(['level', 'occurred_at']);
            $table->index(['paypal_request_id']);
            $table->index(['paypal_correlation_id']);
            $table->index(['type', 'level']);
            $table->index(['processed', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
    }
};
