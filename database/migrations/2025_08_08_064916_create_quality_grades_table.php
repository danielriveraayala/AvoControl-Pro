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
        Schema::create('quality_grades', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Primera, Segunda, etc.
            $table->integer('caliber_min')->nullable(); // Calibre mínimo
            $table->integer('caliber_max')->nullable(); // Calibre máximo
            $table->integer('weight_min')->nullable(); // Peso mínimo en gramos
            $table->integer('weight_max')->nullable(); // Peso máximo en gramos
            $table->text('description')->nullable(); // Descripción adicional
            $table->boolean('active')->default(true); // Activo/Inactivo
            $table->integer('sort_order')->default(0); // Orden de presentación
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_grades');
    }
};
