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
        Schema::create('notification_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            
            // Permission scope
            $table->string('scope'); // global, user, role, department
            $table->unsignedBigInteger('entity_id')->nullable(); // User ID, role ID, etc.
            $table->string('entity_type')->nullable(); // user, role, department
            
            // Notification permissions
            $table->boolean('can_send_notifications')->default(false);
            $table->boolean('can_receive_notifications')->default(true);
            $table->boolean('can_manage_templates')->default(false);
            $table->boolean('can_manage_schedules')->default(false);
            $table->boolean('can_manage_filters')->default(false);
            $table->boolean('can_view_analytics')->default(false);
            $table->boolean('can_manage_channels')->default(false);
            
            // Type-specific permissions
            $table->json('allowed_types')->nullable(); // Which notification types can send/receive
            $table->json('blocked_types')->nullable(); // Which notification types are blocked
            $table->json('allowed_channels')->nullable(); // Which channels can use
            $table->json('blocked_channels')->nullable(); // Which channels are blocked
            
            // Priority permissions
            $table->string('max_priority')->default('normal'); // low, normal, high, critical
            $table->boolean('can_send_critical')->default(false);
            $table->boolean('can_override_filters')->default(false);
            
            // Rate limiting permissions
            $table->integer('max_send_per_hour')->nullable();
            $table->integer('max_send_per_day')->nullable();
            $table->boolean('exempt_from_rate_limits')->default(false);
            
            // Time-based permissions
            $table->json('allowed_send_hours')->nullable(); // Hours when can send
            $table->json('allowed_send_days')->nullable(); // Days when can send
            $table->boolean('can_send_outside_hours')->default(false);
            
            // Advanced permissions
            $table->boolean('can_bulk_send')->default(false);
            $table->boolean('can_schedule_future')->default(false);
            $table->boolean('can_modify_system_notifications')->default(false);
            $table->boolean('can_delete_notifications')->default(false);
            $table->boolean('can_view_all_notifications')->default(false);
            
            // Control and monitoring
            $table->boolean('is_active')->default(true);
            $table->boolean('inherit_from_role')->default(true);
            $table->integer('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            
            // Metadata
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['scope', 'is_active']);
            $table->index(['entity_type', 'entity_id']);
            $table->index('slug');
            $table->index(['max_priority', 'is_active']);
            
            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_permissions');
    }
};
