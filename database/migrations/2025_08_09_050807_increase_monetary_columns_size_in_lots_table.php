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
        Schema::table('lots', function (Blueprint $table) {
            // Increase size of monetary columns to handle larger values
            $table->decimal('total_purchase_cost', 15, 2)->change();
            $table->decimal('amount_paid', 15, 2)->change();
            $table->decimal('amount_owed', 15, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            // Revert to original sizes
            $table->decimal('total_purchase_cost', 10, 2)->change();
            $table->decimal('amount_paid', 10, 2)->change();
            $table->decimal('amount_owed', 10, 2)->change();
        });
    }
};
