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
        Schema::create('notification_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type'); // email, push, database, sms, webhook
            $table->text('description')->nullable();
            
            // Channel configuration
            $table->json('config'); // Provider-specific configuration
            $table->json('default_settings')->nullable(); // Default settings for notifications
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            
            // Rate limiting
            $table->integer('rate_limit_per_minute')->nullable();
            $table->integer('rate_limit_per_hour')->nullable();
            $table->integer('rate_limit_per_day')->nullable();
            
            // Retry and failure handling
            $table->integer('max_retries')->default(3);
            $table->integer('retry_delay_seconds')->default(60);
            $table->boolean('fail_silently')->default(false);
            
            // Priority and scheduling
            $table->json('priority_mapping')->nullable(); // How to handle different priority levels
            $table->json('schedule_restrictions')->nullable(); // When this channel can be used
            
            // Statistics and monitoring
            $table->integer('sent_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->decimal('success_rate', 5, 2)->default(0.00);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('last_success_at')->nullable();
            $table->timestamp('last_failure_at')->nullable();
            
            // Metadata
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['type', 'is_active']);
            $table->index(['is_active', 'is_default']);
            $table->index('slug');
            $table->index('success_rate');
            
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
        Schema::dropIfExists('notification_channels');
    }
};
