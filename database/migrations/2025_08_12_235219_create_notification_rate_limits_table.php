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
        Schema::create('notification_rate_limits', function (Blueprint $table) {
            $table->id();
            $table->string('identifier'); // user:123, channel:email, type:test_daily, global
            $table->string('limit_type'); // user, channel, type, global, ip
            $table->string('time_window'); // minute, hour, day, week, month
            
            // Rate limit configuration
            $table->integer('max_attempts');
            $table->integer('current_count')->default(0);
            $table->timestamp('window_start');
            $table->timestamp('window_end');
            
            // Throttling information
            $table->timestamp('blocked_until')->nullable();
            $table->integer('violations_count')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('last_violation_at')->nullable();
            
            // Configuration
            $table->boolean('is_active')->default(true);
            $table->integer('backoff_multiplier')->default(2); // Exponential backoff
            $table->integer('max_block_duration_minutes')->default(60);
            $table->json('exempt_users')->nullable(); // Users exempt from this limit
            $table->json('exempt_types')->nullable(); // Types exempt from this limit
            
            // Metadata and logging
            $table->json('metadata')->nullable(); // Additional context
            $table->text('last_violation_reason')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['identifier', 'limit_type', 'time_window']);
            $table->index(['window_start', 'window_end']);
            $table->index(['blocked_until', 'is_active']);
            $table->index('violations_count');
            
            // Unique constraint to prevent duplicates
            $table->unique(['identifier', 'limit_type', 'time_window'], 'rate_limit_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_rate_limits');
    }
};
