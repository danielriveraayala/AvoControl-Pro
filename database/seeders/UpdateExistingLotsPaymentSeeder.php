<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UpdateExistingLotsPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Updating existing lots with payment information...');
        
        \App\Models\Lot::all()->each(function($lot) {
            $lot->amount_owed = $lot->total_purchase_cost;
            $lot->payment_status = 'pending';
            $lot->save();
            
            $this->command->info("Updated lot: {$lot->lot_code} - Owed: \${$lot->amount_owed}");
        });
        
        $this->command->info('All lots updated successfully!');
    }
}
