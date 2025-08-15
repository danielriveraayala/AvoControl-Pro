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
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->boolean('show_on_landing')->default(true)->after('is_featured')
                ->comment('Whether to show this plan on the public landing page');
            $table->string('button_text')->nullable()->after('show_on_landing')
                ->comment('Custom button text for the plan (e.g., "Comenzar Prueba", "Contratar Ahora")');
            $table->string('popular_badge')->nullable()->after('button_text')
                ->comment('Badge text for popular plans (e.g., "Más Popular", "Recomendado")');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn(['show_on_landing', 'button_text', 'popular_badge']);
        });
    }
};
