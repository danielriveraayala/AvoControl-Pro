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
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('subscription_id')->constrained('subscriptions')->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            
            // PayPal payment details
            $table->string('paypal_payment_id')->unique()->nullable(); // PayPal payment ID
            $table->string('paypal_capture_id')->nullable(); // PayPal capture ID
            $table->string('paypal_order_id')->nullable(); // PayPal order ID
            $table->string('paypal_payer_id')->nullable(); // PayPal payer ID
            
            // Payment information
            $table->decimal('amount', 10, 2); // Payment amount
            $table->enum('currency', ['USD', 'MXN'])->default('USD');
            $table->enum('type', [
                'initial',          // First payment (setup)
                'recurring',        // Regular monthly/yearly payment
                'retry',           // Retry failed payment
                'refund',          // Refund payment
                'partial_refund'   // Partial refund
            ])->default('recurring');
            
            // Payment status
            $table->enum('status', [
                'pending',          // Payment pending
                'completed',        // Payment successful
                'failed',          // Payment failed
                'cancelled',       // Payment cancelled
                'refunded',        // Payment refunded
                'partially_refunded' // Payment partially refunded
            ])->default('pending');
            
            // Billing information
            $table->integer('billing_cycle')->default(1); // Which billing cycle this payment is for
            $table->date('billing_period_start')->nullable();
            $table->date('billing_period_end')->nullable();
            
            // Payment dates
            $table->timestamp('payment_date')->nullable(); // When payment was attempted
            $table->timestamp('completed_at')->nullable(); // When payment completed
            $table->timestamp('failed_at')->nullable(); // When payment failed
            $table->timestamp('refunded_at')->nullable(); // When payment was refunded
            
            // Failure information
            $table->string('failure_reason')->nullable(); // PayPal failure reason
            $table->text('failure_details')->nullable(); // Detailed failure information
            $table->integer('retry_count')->default(0); // How many times payment was retried
            $table->timestamp('next_retry_at')->nullable(); // When to retry failed payment
            
            // Refund information
            $table->decimal('refunded_amount', 10, 2)->default(0); // Total refunded amount
            $table->string('refund_reason')->nullable(); // Reason for refund
            $table->string('refunded_by')->nullable(); // Who initiated refund ('user', 'admin', 'system')
            
            // Metadata
            $table->json('paypal_response')->nullable(); // Store full PayPal response
            $table->json('webhook_data')->nullable(); // Store webhook event data
            $table->text('notes')->nullable(); // Admin notes
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['subscription_id', 'status']);
            $table->index(['tenant_id', 'payment_date']);
            $table->index(['paypal_payment_id']);
            $table->index(['billing_cycle']);
            $table->index(['status', 'payment_date']);
            $table->index(['next_retry_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};
