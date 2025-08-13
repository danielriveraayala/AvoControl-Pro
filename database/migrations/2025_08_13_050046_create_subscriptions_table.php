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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // User who created subscription
            
            // PayPal subscription details
            $table->string('paypal_subscription_id')->unique()->nullable(); // PayPal subscription ID
            $table->string('paypal_plan_id')->nullable(); // PayPal plan ID
            $table->string('paypal_order_id')->nullable(); // Initial order ID
            
            // Plan information
            $table->enum('plan', ['trial', 'basic', 'premium', 'enterprise', 'corporate'])->default('trial');
            $table->decimal('amount', 10, 2); // Monthly/yearly amount
            $table->enum('currency', ['USD', 'MXN'])->default('USD');
            $table->enum('interval', ['monthly', 'yearly'])->default('monthly');
            
            // Subscription status
            $table->enum('status', [
                'pending',           // Waiting for PayPal approval
                'active',           // Active subscription
                'suspended',        // Temporarily suspended
                'cancelled',        // User cancelled
                'expired',          // Subscription expired
                'failed'            // Payment failed
            ])->default('pending');
            
            // Trial information
            $table->boolean('is_trial')->default(true);
            $table->timestamp('trial_starts_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            
            // Subscription dates
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('next_billing_date')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            
            // Grace period for failed payments
            $table->integer('grace_period_days')->default(3);
            $table->timestamp('grace_period_ends_at')->nullable();
            
            // Metrics
            $table->integer('billing_cycle_count')->default(0);
            $table->integer('failed_payment_count')->default(0);
            
            // Metadata
            $table->json('paypal_metadata')->nullable(); // Store PayPal response data
            $table->json('plan_features')->nullable(); // Store plan limits and features
            $table->text('cancellation_reason')->nullable();
            $table->string('cancelled_by')->nullable(); // 'user', 'admin', 'system'
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['tenant_id', 'status']);
            $table->index(['paypal_subscription_id']);
            $table->index(['next_billing_date']);
            $table->index(['trial_ends_at']);
            $table->index(['status', 'next_billing_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
