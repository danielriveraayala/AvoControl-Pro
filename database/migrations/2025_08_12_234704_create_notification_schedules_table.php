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
        Schema::create('notification_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            
            // Schedule configuration
            $table->string('frequency'); // daily, weekly, monthly, custom
            $table->json('schedule_config'); // Detailed schedule configuration
            $table->string('timezone')->default('America/Mexico_City');
            
            // Time restrictions
            $table->time('start_time')->nullable(); // Earliest time to send
            $table->time('end_time')->nullable(); // Latest time to send
            $table->json('allowed_days')->nullable(); // ['monday', 'tuesday', etc.]
            $table->json('excluded_dates')->nullable(); // Specific dates to exclude
            
            // Template and targeting
            $table->unsignedBigInteger('template_id')->nullable();
            $table->json('target_users')->nullable(); // User IDs or roles
            $table->json('conditions')->nullable(); // Additional conditions
            
            // Control and status
            $table->boolean('is_active')->default(true);
            $table->timestamp('next_run_at')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->integer('run_count')->default(0);
            $table->json('last_run_result')->nullable();
            
            // Metadata
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['is_active', 'next_run_at']);
            $table->index(['frequency', 'is_active']);
            $table->index('slug');
            
            // Foreign keys
            $table->foreign('template_id')->references('id')->on('notification_templates')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_schedules');
    }
};
