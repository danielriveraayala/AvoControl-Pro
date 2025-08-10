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
        Schema::table('quality_grades', function (Blueprint $table) {
            $table->string('color', 7)->default('#6c757d')->after('description')->comment('Hex color code for UI display');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quality_grades', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
