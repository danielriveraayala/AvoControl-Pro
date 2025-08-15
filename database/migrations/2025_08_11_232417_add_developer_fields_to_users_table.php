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
            $table->timestamp('suspended_at')->nullable()->after('email_verified_at');
            $table->text('suspension_reason')->nullable()->after('suspended_at');
            $table->timestamp('password_changed_at')->nullable()->after('suspension_reason');
            $table->unsignedBigInteger('created_by')->nullable()->after('password_changed_at');
            
            $table->index('suspended_at');
            $table->index('created_by');
            
            // Add foreign key constraint for created_by
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropIndex(['suspended_at']);
            $table->dropIndex(['created_by']);
            $table->dropColumn([
                'suspended_at',
                'suspension_reason', 
                'password_changed_at',
                'created_by'
            ]);
        });
    }
};
