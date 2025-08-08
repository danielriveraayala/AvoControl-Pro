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
        Schema::table('payments', function (Blueprint $table) {
            // Drop sale_id constraint and column if it exists (we'll make it more flexible)
            if (Schema::hasColumn('payments', 'sale_id')) {
                $table->dropForeign(['sale_id']);
                $table->dropColumn('sale_id');
            }
            
            // Update payment_number to payment_code
            if (Schema::hasColumn('payments', 'payment_number')) {
                $table->renameColumn('payment_number', 'payment_code');
            }
            
            // Rename user_id to created_by
            if (Schema::hasColumn('payments', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->renameColumn('user_id', 'created_by');
            }
            
            // Add missing columns
            if (!Schema::hasColumn('payments', 'type')) {
                $table->enum('type', ['income', 'expense'])->after('id');
            }
            if (!Schema::hasColumn('payments', 'concept')) {
                $table->string('concept')->after('type');
            }
            if (!Schema::hasColumn('payments', 'payable_type')) {
                $table->string('payable_type')->nullable()->after('concept');
            }
            if (!Schema::hasColumn('payments', 'payable_id')) {
                $table->unsignedBigInteger('payable_id')->nullable()->after('payable_type');
            }
            
            // Update reference_number to reference
            if (Schema::hasColumn('payments', 'reference_number')) {
                $table->renameColumn('reference_number', 'reference');
            }
            
            // Remove payment_details column if exists (replace with notes)
            if (Schema::hasColumn('payments', 'payment_details')) {
                $table->dropColumn('payment_details');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            //
        });
    }
};
