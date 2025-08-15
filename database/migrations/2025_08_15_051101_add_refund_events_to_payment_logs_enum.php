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
        DB::statement("ALTER TABLE payment_logs MODIFY COLUMN event ENUM(
            'subscription_created','subscription_activated','subscription_cancelled',
            'webhook_unhandled','token_acquired','config_updated','plan_synchronized',
            'payment_completed','payment_failed','payment_refunded','payment_reversed',
            'subscription_suspended','subscription_reactivated','webhook_received',
            'webhook_processing_error'
        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE payment_logs MODIFY COLUMN event ENUM(
            'subscription_created','subscription_activated','subscription_cancelled',
            'webhook_unhandled','token_acquired','config_updated','plan_synchronized'
        ) NOT NULL");
    }
};