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
        Schema::table('subscription_plans', function (Blueprint $table) {
            // Add annual pricing fields
            $table->decimal('annual_price', 10, 2)->nullable()->after('price');
            $table->integer('annual_discount_percentage')->nullable()->after('annual_price');
            $table->string('paypal_annual_plan_id')->nullable()->after('paypal_plan_id');
            $table->string('stripe_annual_plan_id')->nullable()->after('stripe_plan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn([
                'annual_price',
                'annual_discount_percentage', 
                'paypal_annual_plan_id',
                'stripe_annual_plan_id'
            ]);
        });
    }
};
