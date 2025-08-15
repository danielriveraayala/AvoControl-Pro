<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add new event types to payment_logs enum
        DB::statement("ALTER TABLE payment_logs MODIFY COLUMN event ENUM(
            'subscription_created','subscription_activated','subscription_cancelled','subscription_suspended','subscription_reactivated',
            'payment_completed','payment_failed','payment_refunded','webhook_received','trial_started','trial_ending_soon',
            'trial_expired','plan_upgraded','plan_downgraded','account_suspended','account_reactivated',
            'grace_period_started','grace_period_ended','user_subscribed','user_cancelled','user_updated_payment',
            'user_accessed_dashboard','admin_suspended_account','admin_reactivated_account','admin_refunded_payment',
            'admin_extended_trial','api_error','webhook_error','payment_processing_error','sync_error',
            'webhook_unhandled','token_acquired','config_updated','plan_synchronized'
        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove new event types from payment_logs enum
        DB::statement("ALTER TABLE payment_logs MODIFY COLUMN event ENUM(
            'subscription_created','subscription_activated','subscription_cancelled','subscription_suspended','subscription_reactivated',
            'payment_completed','payment_failed','payment_refunded','webhook_received','trial_started','trial_ending_soon',
            'trial_expired','plan_upgraded','plan_downgraded','account_suspended','account_reactivated',
            'grace_period_started','grace_period_ended','user_subscribed','user_cancelled','user_updated_payment',
            'user_accessed_dashboard','admin_suspended_account','admin_reactivated_account','admin_refunded_payment',
            'admin_extended_trial','api_error','webhook_error','payment_processing_error','sync_error'
        ) NOT NULL");
    }
};
