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
        Schema::create('lot_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lot_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2)->comment('Monto del pago');
            $table->date('payment_date')->comment('Fecha del pago');
            $table->enum('payment_type', ['efectivo', 'transferencia', 'cheque', 'deposito', 'otro'])->default('efectivo')->comment('Tipo de pago');
            $table->foreignId('paid_by_user_id')->nullable()->constrained('users')->onDelete('set null')->comment('Usuario que registró el pago');
            $table->text('notes')->nullable()->comment('Notas adicionales del pago');
            $table->timestamps();
            
            $table->index(['lot_id', 'payment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lot_payments');
    }
};
