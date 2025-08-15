<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lots', function (Blueprint $table) {
            $table->id();
            $table->string('lot_number')->unique();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->decimal('total_kilos', 10, 2);
            $table->decimal('price_per_kilo', 8, 2);
            $table->decimal('total_cost', 12, 2);
            $table->date('purchase_date');
            $table->date('harvest_date')->nullable();
            $table->enum('quality_grade', ['premium', 'export', 'nacional', 'industrial'])->default('export');
            $table->decimal('remaining_kilos', 10, 2);
            $table->enum('status', ['available', 'sold', 'reserved', 'damaged'])->default('available');
            $table->text('notes')->nullable();
            $table->json('quality_metrics')->nullable(); // pH, materia seca, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lots');
    }
}
