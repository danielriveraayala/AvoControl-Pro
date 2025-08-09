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
        Schema::create('sale_lot_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('lot_id')->constrained()->onDelete('cascade');
            $table->decimal('allocated_weight', 10, 2)->comment('Peso asignado de este lote a la venta');
            $table->decimal('cost_per_kg', 8, 2)->comment('Costo de compra por kg de este lote');
            $table->decimal('allocated_cost', 10, 2)->comment('Costo total asignado (peso * costo_kg)');
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['sale_item_id', 'lot_id']);
            $table->index(['lot_id', 'allocated_weight']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_lot_allocations');
    }
};
