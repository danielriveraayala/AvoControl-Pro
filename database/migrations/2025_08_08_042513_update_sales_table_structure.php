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
        Schema::table('sales', function (Blueprint $table) {
            // Add missing columns that the ReportService and views expect
            if (!Schema::hasColumn('sales', 'sale_code')) {
                $table->string('sale_code')->unique()->after('id');
            }
            if (!Schema::hasColumn('sales', 'total_weight')) {
                $table->decimal('total_weight', 10, 2)->default(0)->after('sale_date');
            }
            if (!Schema::hasColumn('sales', 'delivery_date')) {
                $table->date('delivery_date')->nullable()->after('sale_date');
            }
            
            // Rename user_id to created_by for consistency
            if (Schema::hasColumn('sales', 'user_id')) {
                $table->renameColumn('user_id', 'created_by');
            }
            
            // Remove tax-related columns that aren't being used
            if (Schema::hasColumn('sales', 'subtotal')) {
                $table->dropColumn(['subtotal', 'tax_percentage', 'tax_amount', 'paid_amount', 'pending_amount']);
            }
            
            // Remove shipping_info column
            if (Schema::hasColumn('sales', 'shipping_info')) {
                $table->dropColumn('shipping_info');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            //
        });
    }
};
