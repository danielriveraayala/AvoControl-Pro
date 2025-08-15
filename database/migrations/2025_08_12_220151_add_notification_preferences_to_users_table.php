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
        Schema::table('users', function (Blueprint $table) {
            // General notification preferences
            $table->boolean('email_notifications_enabled')->default(true)->after('remember_token');
            $table->boolean('push_notifications_enabled')->default(true)->after('email_notifications_enabled');
            
            // Specific notification type preferences
            $table->boolean('notify_test_daily')->default(true)->after('push_notifications_enabled');
            $table->boolean('notify_inventory_low')->default(true)->after('notify_test_daily');
            $table->boolean('notify_payment_overdue')->default(true)->after('notify_inventory_low');
            $table->boolean('notify_sale_completed')->default(true)->after('notify_payment_overdue');
            $table->boolean('notify_system')->default(true)->after('notify_sale_completed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'email_notifications_enabled',
                'push_notifications_enabled',
                'notify_test_daily',
                'notify_inventory_low',
                'notify_payment_overdue',
                'notify_sale_completed',
                'notify_system'
            ]);
        });
    }
};
