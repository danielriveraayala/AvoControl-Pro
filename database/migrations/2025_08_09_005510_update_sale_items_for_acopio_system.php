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
        Schema::table('sale_items', function (Blueprint $table) {
            // Cambiar la lógica de ventas por lote específico a ventas por acopio/calidad
            $table->dropForeign(['lot_id']);
            $table->dropColumn('lot_id');
            
            // Agregar columna de calidad para ventas por acopio
            $table->string('quality_grade')->after('sale_id')->comment('Calidad vendida del acopio total');
            
            // Renombrar columnas para consistencia (si existen con nombres diferentes)
            if (Schema::hasColumn('sale_items', 'quantity_kilos')) {
                $table->renameColumn('quantity_kilos', 'weight');
            }
            if (Schema::hasColumn('sale_items', 'unit_price')) {
                $table->renameColumn('unit_price', 'price_per_kg');
            }
            
            // Agregar índice para consultas por calidad
            $table->index(['quality_grade', 'sale_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            // Revertir cambios
            $table->dropIndex(['quality_grade', 'sale_id']);
            $table->dropColumn('quality_grade');
            
            // Restaurar relación con lotes
            $table->foreignId('lot_id')->after('sale_id')->constrained()->onDelete('cascade');
            
            // Revertir nombres de columnas si fueron cambiados
            if (Schema::hasColumn('sale_items', 'weight')) {
                $table->renameColumn('weight', 'quantity_kilos');
            }
            if (Schema::hasColumn('sale_items', 'price_per_kg')) {
                $table->renameColumn('price_per_kg', 'unit_price');
            }
        });
    }
};
