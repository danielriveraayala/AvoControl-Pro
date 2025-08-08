<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // vendedor
            $table->date('sale_date');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax_percentage', 5, 2)->default(16.00); // IVA
            $table->decimal('tax_amount', 12, 2);
            $table->decimal('total_amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0.00);
            $table->decimal('pending_amount', 12, 2);
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'overdue'])->default('pending');
            $table->enum('status', ['draft', 'confirmed', 'shipped', 'delivered', 'cancelled'])->default('draft');
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->json('shipping_info')->nullable();
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
        Schema::dropIfExists('sales');
    }
}
