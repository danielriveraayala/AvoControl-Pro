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
        // Add missing current_balance column to customers table
        if (!Schema::hasColumn('customers', 'current_balance')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->decimal('current_balance', 12, 2)->default(0)->after('credit_limit');
            });
        }
        
        // Ensure sale_items table exists for relationships
        if (!Schema::hasTable('sale_items')) {
            Schema::create('sale_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sale_id')->constrained()->onDelete('cascade');
                $table->foreignId('lot_id')->constrained()->onDelete('cascade');
                $table->decimal('weight', 10, 2);
                $table->decimal('price_per_kg', 8, 2);
                $table->decimal('subtotal', 12, 2);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            //
        });
    }
};
