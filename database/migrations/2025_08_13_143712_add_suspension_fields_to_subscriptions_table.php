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
        Schema::table('subscriptions', function (Blueprint $table) {
            // Suspension tracking fields
            $table->string('suspension_reason')->nullable()->after('cancelled_by');
            $table->string('suspended_by')->nullable()->after('suspension_reason');
            
            // Reactivation tracking fields  
            $table->timestamp('reactivated_at')->nullable()->after('suspended_by');
            $table->string('reactivation_reason')->nullable()->after('reactivated_at');
            $table->string('reactivated_by')->nullable()->after('reactivation_reason');
            
            // Grace period tracking
            $table->timestamp('grace_period_started_at')->nullable()->after('reactivated_by');
            
            // Data backup tracking
            $table->boolean('data_backed_up_before_suspension')->default(false)->after('grace_period_started_at');
            $table->timestamp('data_backup_created_at')->nullable()->after('data_backed_up_before_suspension');
            $table->string('data_backup_path')->nullable()->after('data_backup_created_at');
            
            // Configurable grace period (in days)
            $table->integer('custom_grace_period_days')->nullable()->after('data_backup_path');
            
            // Account deletion tracking (for permanent deletion after long suspension)
            $table->timestamp('scheduled_for_deletion_at')->nullable()->after('custom_grace_period_days');
            $table->boolean('deletion_warning_sent')->default(false)->after('scheduled_for_deletion_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'suspension_reason',
                'suspended_by', 
                'reactivated_at',
                'reactivation_reason',
                'reactivated_by',
                'grace_period_started_at',
                'data_backed_up_before_suspension',
                'data_backup_created_at',
                'data_backup_path',
                'custom_grace_period_days',
                'scheduled_for_deletion_at',
                'deletion_warning_sent'
            ]);
        });
    }
};
