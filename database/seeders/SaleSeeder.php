<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\Lot;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $customers = Customer::all();
        $lots = Lot::where('weight_available', '>', 0)->get();
        
        if ($customers->isEmpty() || $lots->isEmpty()) {
            return;
        }

        $saleCount = 1;

        // Create 5-8 sales
        for ($i = 0; $i < rand(5, 8); $i++) {
            $customer = $customers->random();
            $saleDate = Carbon::now()->subDays(rand(1, 30));
            
            // Generate sale code
            $saleCode = 'VTA-' . $saleDate->format('Ymd') . '-' . str_pad($saleCount, 3, '0', STR_PAD_LEFT);
            
            $totalWeight = 0;
            $totalAmount = 0;
            
            // Create sale first
            $sale = Sale::create([
                'sale_code' => $saleCode,
                'customer_id' => $customer->id,
                'sale_date' => $saleDate,
                'delivery_date' => rand(0, 1) ? $saleDate->copy()->addDays(rand(1, 7)) : null,
                'invoice_number' => 'INV-' . $saleCode,
                'total_weight' => 0, // Will be updated after creating items
                'total_amount' => 0, // Will be updated after creating items
                'status' => ['draft', 'delivered'][rand(0, 1)],
                'payment_status' => 'pending'
            ]);

            // Create 1-3 sale items per sale
            $itemsToCreate = rand(1, 3);
            $availableLots = $lots->where('weight_available', '>', 0);
            
            for ($j = 0; $j < $itemsToCreate && $availableLots->isNotEmpty(); $j++) {
                $lot = $availableLots->random();
                $maxWeight = min($lot->weight_available, rand(100, 1000));
                
                if ($maxWeight <= 0) continue;
                
                $weight = rand(50, $maxWeight);
                $pricePerKg = $lot->purchase_price_per_kg * (1 + rand(20, 50) / 100); // 20-50% markup
                $subtotal = $weight * $pricePerKg;
                
                // Create sale item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'lot_id' => $lot->id,
                    'weight' => $weight,
                    'price_per_kg' => $pricePerKg,
                    'subtotal' => $subtotal
                ]);

                // Update lot
                $lot->weight_sold += $weight;
                $lot->weight_available -= $weight;
                $lot->status = $lot->weight_available == 0 ? 'sold' : 'partial';
                $lot->save();

                $totalWeight += $weight;
                $totalAmount += $subtotal;
                
                // Remove lot from available if sold out
                if ($lot->weight_available <= 0) {
                    $availableLots = $availableLots->reject(function($l) use ($lot) {
                        return $l->id === $lot->id;
                    });
                }
            }

            // Update sale totals
            $sale->total_weight = $totalWeight;
            $sale->total_amount = $totalAmount;
            $sale->save();

            // Update customer balance
            $customer->current_balance += $totalAmount;
            $customer->save();

            // Create payments for some sales (60% chance)
            if (rand(1, 100) <= 60) {
                $paymentAmount = rand(0, 1) ? $totalAmount : $totalAmount * (rand(30, 80) / 100); // Full or partial payment
                
                $payment = Payment::create([
                    'payment_code' => 'PAY-' . $saleDate->format('Ymd') . '-' . str_pad($saleCount, 3, '0', STR_PAD_LEFT),
                    'type' => 'income',
                    'concept' => 'sale_payment',
                    'payable_type' => Sale::class,
                    'payable_id' => $sale->id,
                    'amount' => $paymentAmount,
                    'payment_date' => $saleDate->copy()->addDays(rand(0, 15)),
                    'payment_method' => ['cash', 'transfer', 'check'][rand(0, 2)],
                    'reference' => 'REF-' . $saleCount . '-' . rand(1000, 9999),
                    'created_by' => 1 // Admin user
                ]);

                // Update customer balance and sale payment status
                $customer->current_balance -= $paymentAmount;
                $customer->save();
                
                if ($paymentAmount >= $totalAmount) {
                    $sale->payment_status = 'paid';
                } else {
                    $sale->payment_status = 'partial';
                }
                $sale->save();
            }

            $saleCount++;
        }
    }
}