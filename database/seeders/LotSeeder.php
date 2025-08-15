<?php

namespace Database\Seeders;

use App\Models\Lot;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $suppliers = Supplier::all();
        $lotCount = 1;

        foreach ($suppliers as $supplier) {
            // Create 2-3 lots per supplier
            for ($i = 0; $i < rand(2, 3); $i++) {
                $totalWeight = rand(1000, 5000);
                $pricePerKg = rand(25, 60) + (rand(0, 99) / 100); // Random price between 25.00 and 60.99
                $harvestDate = Carbon::now()->subDays(rand(1, 45));
                $entryDate = $harvestDate->copy()->addDays(rand(1, 5));
                
                // Generate lot code
                $lotCode = 'LOT-' . $entryDate->format('Ymd') . '-' . str_pad($lotCount, 3, '0', STR_PAD_LEFT);
                
                // Random quality distribution
                $qualities = ['Primera', 'Segunda', 'Tercera'];
                $quality = $qualities[array_rand($qualities)];
                
                // Random weight sold (0 to 50% of total)
                $weightSold = $totalWeight * (rand(0, 50) / 100);
                $weightAvailable = $totalWeight - $weightSold;
                
                // Determine status
                $status = 'active';
                if ($weightSold == 0) {
                    $status = 'active';
                } elseif ($weightAvailable == 0) {
                    $status = 'sold';
                } else {
                    $status = 'partial';
                }

                Lot::create([
                    'lot_code' => $lotCode,
                    'supplier_id' => $supplier->id,
                    'harvest_date' => $harvestDate,
                    'entry_date' => $entryDate,
                    'total_weight' => $totalWeight,
                    'purchase_price_per_kg' => $pricePerKg,
                    'total_purchase_cost' => $totalWeight * $pricePerKg,
                    'quality_grade' => $quality,
                    'status' => $status,
                    'weight_sold' => $weightSold,
                    'weight_available' => $weightAvailable,
                    'metadata' => json_encode([
                        'harvest_region' => $supplier->name,
                        'variety' => 'Hass',
                        'organic' => rand(0, 1) ? true : false,
                        'caliber' => rand(28, 48) . '-' . rand(32, 52)
                    ])
                ]);

                // Note: Supplier totals will be calculated dynamically from relationships

                $lotCount++;
            }
        }
    }
}