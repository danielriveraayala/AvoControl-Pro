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
            // Eliminar metadata que contenía precio sugerido
            if (Schema::hasColumn('lots', 'metadata')) {
                $table->dropColumn('metadata');
            }
            
            // Agregar campo de notas simples
            $table->text('notes')->nullable()->after('status')->comment('Notas del lote');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            // Revertir cambios
            $table->dropColumn('notes');
            $table->json('metadata')->nullable()->after('status');
        });
    }
};
