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
        // Add 'prueba' to the plan enum in subscriptions table
        DB::statement("ALTER TABLE subscriptions MODIFY COLUMN plan ENUM('trial','basic','premium','enterprise','corporate','prueba') NOT NULL DEFAULT 'trial'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'prueba' from the plan enum
        DB::statement("ALTER TABLE subscriptions MODIFY COLUMN plan ENUM('trial','basic','premium','enterprise','corporate') NOT NULL DEFAULT 'trial'");
    }
};