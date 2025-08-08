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
            $table->decimal('amount_paid', 10, 2)->default(0)->after('total_purchase_cost')->comment('Monto total pagado al proveedor');
            $table->decimal('amount_owed', 10, 2)->default(0)->after('amount_paid')->comment('Monto pendiente de pago');
            $table->enum('payment_status', ['pending', 'partial', 'paid'])->default('pending')->after('amount_owed')->comment('Estado del pago');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            $table->dropColumn(['amount_paid', 'amount_owed', 'payment_status']);
        });
    }
};
