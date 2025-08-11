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
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->char('notification_id', 36)->nullable()->index(); // UUID
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->string('subject');
            $table->string('mailable_class');
            $table->enum('priority', ['low', 'normal', 'high', 'critical'])->default('normal');
            $table->enum('status', ['pending', 'sent', 'failed', 'retrying'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('attempts')->default(0);
            $table->json('data')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['status', 'created_at']);
            $table->index(['priority', 'created_at']);
            $table->index('recipient_email');
            $table->index('sent_at');
            $table->index('failed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
