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
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('type'); // notification type: test_daily, inventory_low, etc.
            $table->string('channel')->default('all'); // email, push, database, all
            $table->string('priority')->default('normal'); // low, normal, high, critical
            
            // Template content
            $table->string('title_template');
            $table->text('message_template');
            $table->text('email_subject_template')->nullable();
            $table->text('email_body_template')->nullable();
            $table->text('push_title_template')->nullable();
            $table->text('push_body_template')->nullable();
            
            // Configuration
            $table->json('variables')->nullable(); // Available template variables
            $table->json('conditions')->nullable(); // When to use this template
            $table->json('settings')->nullable(); // Additional settings
            
            // Control
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false); // System templates can't be deleted
            $table->integer('usage_count')->default(0);
            
            // Metadata
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['type', 'is_active']);
            $table->index(['channel', 'is_active']);
            $table->index('slug');
            
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
        Schema::dropIfExists('notification_templates');
    }
};
