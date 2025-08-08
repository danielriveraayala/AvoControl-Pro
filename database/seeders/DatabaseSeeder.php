<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UserSeeder::class,
            SupplierSeeder::class,
            CustomerSeeder::class,
            LotSeeder::class,
            // SaleSeeder::class, // Temporarily disabled
        ]);
    }
}
