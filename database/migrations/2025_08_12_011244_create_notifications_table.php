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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->json('data');
            $table->timestamp('read_at')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'critical'])->default('normal');
            $table->enum('channel', ['database', 'email', 'push', 'all'])->default('database');
            $table->string('category')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('sent_email')->default(false);
            $table->boolean('sent_push')->default(false);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['notifiable_type', 'notifiable_id']);
            $table->index('read_at');
            $table->index('priority');
            $table->index('channel');
            $table->index('category');
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
