<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the enum to include 'manager'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin','manager','gerente','contador','vendedor','comprador','operario','visualizador') NOT NULL DEFAULT 'vendedor'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'manager' from enum (only if no users have this role)
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin','gerente','contador','vendedor','comprador','operario','visualizador') NOT NULL DEFAULT 'vendedor'");
    }
};
