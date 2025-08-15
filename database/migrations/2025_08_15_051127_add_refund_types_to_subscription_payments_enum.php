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
        DB::statement("ALTER TABLE subscription_payments MODIFY COLUMN type ENUM(
            'initial','recurring','retry','refund','chargeback'
        ) NOT NULL DEFAULT 'recurring'");

        DB::statement("ALTER TABLE subscription_payments MODIFY COLUMN status ENUM(
            'pending','processing','completed','failed','cancelled','expired','refunded','reversed'
        ) NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE subscription_payments MODIFY COLUMN type ENUM(
            'initial','recurring','retry'
        ) NOT NULL DEFAULT 'recurring'");

        DB::statement("ALTER TABLE subscription_payments MODIFY COLUMN status ENUM(
            'pending','processing','completed','failed','cancelled','expired'
        ) NOT NULL DEFAULT 'pending'");
    }
};