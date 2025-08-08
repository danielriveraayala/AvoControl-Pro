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
            // Drop old columns if they exist
            if (Schema::hasColumn('lots', 'lot_number')) {
                $table->dropColumn('lot_number');
            }
            if (Schema::hasColumn('lots', 'total_kilos')) {
                $table->dropColumn(['total_kilos', 'price_per_kilo', 'total_cost', 'purchase_date', 'remaining_kilos']);
            }
            if (Schema::hasColumn('lots', 'quality_metrics')) {
                $table->dropColumn(['quality_metrics', 'notes']);
            }
            
            // Add new columns if they don't exist
            if (!Schema::hasColumn('lots', 'lot_code')) {
                $table->string('lot_code')->unique()->after('id');
            }
            if (!Schema::hasColumn('lots', 'harvest_date')) {
                $table->date('harvest_date')->after('supplier_id');
            }
            if (!Schema::hasColumn('lots', 'entry_date')) {
                $table->dateTime('entry_date')->after('harvest_date');
            }
            if (!Schema::hasColumn('lots', 'total_weight')) {
                $table->decimal('total_weight', 10, 2)->after('entry_date');
            }
            if (!Schema::hasColumn('lots', 'purchase_price_per_kg')) {
                $table->decimal('purchase_price_per_kg', 8, 2)->after('total_weight');
            }
            if (!Schema::hasColumn('lots', 'total_purchase_cost')) {
                $table->decimal('total_purchase_cost', 12, 2)->after('purchase_price_per_kg');
            }
            if (!Schema::hasColumn('lots', 'weight_sold')) {
                $table->decimal('weight_sold', 10, 2)->default(0)->after('status');
            }
            if (!Schema::hasColumn('lots', 'weight_available')) {
                $table->decimal('weight_available', 10, 2)->after('weight_sold');
            }
            if (!Schema::hasColumn('lots', 'metadata')) {
                $table->json('metadata')->nullable()->after('weight_available');
            }
            
            // Update quality_grade enum values
            $table->enum('quality_grade', ['Primera', 'Segunda', 'Tercera'])->default('Primera')->change();
            
            // Update status enum values
            $table->enum('status', ['active', 'partial', 'sold'])->default('active')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            // This migration is not easily reversible due to data structure changes
            // In production, you would want to backup data before running this
        });
    }
};