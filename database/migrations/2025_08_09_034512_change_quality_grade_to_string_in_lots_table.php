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
        Schema::table('lots', function (Blueprint $table) {
            // Change quality_grade from enum to string to support dynamic quality grades
            $table->string('quality_grade')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            // Revert back to enum - but this may cause data loss if there are custom quality grades
            $table->enum('quality_grade', ['Primera', 'Segunda', 'Tercera'])->change();
        });
    }
};
