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
        Schema::create('notification_filters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            
            // Filter configuration
            $table->string('filter_type'); // user_based, content_based, time_based, frequency_based
            $table->json('conditions'); // Filter conditions and rules
            $table->string('action'); // allow, block, modify, delay
            $table->integer('priority')->default(1); // Execution order
            
            // Scope and targeting
            $table->json('applies_to_types')->nullable(); // Notification types this filter applies to
            $table->json('applies_to_channels')->nullable(); // Channels this filter applies to
            $table->json('applies_to_users')->nullable(); // User IDs or roles
            $table->json('exclude_users')->nullable(); // Users to exclude from this filter
            
            // Filter actions and modifications
            $table->json('action_config')->nullable(); // Configuration for the action
            $table->integer('delay_minutes')->nullable(); // For delay action
            $table->string('redirect_channel')->nullable(); // For channel redirection
            $table->json('content_modifications')->nullable(); // Content changes
            
            // Frequency and rate limiting
            $table->integer('max_per_user_per_hour')->nullable();
            $table->integer('max_per_user_per_day')->nullable();
            $table->integer('max_total_per_hour')->nullable();
            $table->integer('max_total_per_day')->nullable();
            
            // Time-based restrictions
            $table->json('allowed_hours')->nullable(); // Hours when filter is active
            $table->json('allowed_days')->nullable(); // Days when filter is active
            $table->json('excluded_dates')->nullable(); // Specific dates to exclude
            
            // Control and monitoring
            $table->boolean('is_active')->default(true);
            $table->boolean('log_actions')->default(true);
            $table->integer('matches_count')->default(0);
            $table->integer('actions_count')->default(0);
            $table->timestamp('last_matched_at')->nullable();
            
            // Metadata
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['is_active', 'priority']);
            $table->index(['filter_type', 'is_active']);
            $table->index('slug');
            $table->index('matches_count');
            
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
        Schema::dropIfExists('notification_filters');
    }
};
