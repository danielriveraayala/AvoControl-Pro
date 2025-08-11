<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Lot;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CompleteDataSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Limpiar datos existentes
        Payment::truncate();
        SaleItem::truncate();
        Sale::truncate();
        Lot::truncate();
        Customer::truncate();
        Supplier::truncate();
        User::truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        echo "🧹 Datos anteriores limpiados\n";
        
        // 1. Crear usuarios
        $this->createUsers();
        
        // 2. Crear proveedores
        $suppliers = $this->createSuppliers();
        
        // 3. Crear clientes
        $customers = $this->createCustomers();
        
        // 4. Crear lotes (enero - agosto 2025)
        $lots = $this->createLots($suppliers);
        
        // 5. Crear ventas
        $sales = $this->createSales($customers, $lots);
        
        // 6. Crear pagos
        $this->createPayments($suppliers, $customers, $lots, $sales);
        
        echo "✅ Seeder completo ejecutado exitosamente!\n";
        echo "📊 Datos creados desde enero 2025 a agosto 2025\n";
    }
    
    private function createUsers()
    {
        $users = [
            [
                'name' => 'Administrador General',
                'email' => 'admin@avocontrol.com',
                'password' => bcrypt('password123'),
                'role' => 'admin'
            ],
            [
                'name' => 'Vendedor Principal',
                'email' => 'vendedor@avocontrol.com',
                'password' => bcrypt('password123'),
                'role' => 'vendedor'
            ],
            [
                'name' => 'Contador',
                'email' => 'contador@avocontrol.com',
                'password' => bcrypt('password123'),
                'role' => 'contador'
            ],
            [
                'name' => 'Maria González',
                'email' => 'maria@avocontrol.com',
                'password' => bcrypt('password123'),
                'role' => 'vendedor'
            ]
        ];
        
        foreach ($users as $userData) {
            User::create($userData);
        }
        
        echo "👥 4 usuarios creados\n";
    }
    
    private function createSuppliers()
    {
        $suppliers = [
            [
                'name' => 'Productores Unidos de Michoacán',
                'contact_person' => 'Carlos Mendoza',
                'phone' => '+52 443 123 4567',
                'email' => 'carlos@productores-michoacan.com',
                'address' => 'Carretera Uruapan-Peribán Km 8',
                'city' => 'Uruapan',
                'state' => 'Michoacán',
                'notes' => 'Proveedor confiable con aguacates de primera calidad'
            ],
            [
                'name' => 'Aguacates del Valle',
                'contact_person' => 'Ana Rodriguez',
                'phone' => '+52 443 234 5678',
                'email' => 'ana@aguacates-valle.mx',
                'address' => 'Av. Revolución 245',
                'city' => 'Tancítaro',
                'state' => 'Michoacán',
                'notes' => 'Especialistas en aguacate Hass orgánico'
            ],
            [
                'name' => 'Cooperativa La Esperanza',
                'contact_person' => 'José Ramírez',
                'phone' => '+52 443 345 6789',
                'email' => 'jose@cooperativa-esperanza.com',
                'address' => 'Calle Juárez 123',
                'city' => 'Peribán',
                'state' => 'Michoacán',
                'notes' => 'Cooperativa de pequeños productores'
            ],
            [
                'name' => 'Rancho San Miguel',
                'contact_person' => 'Roberto Silva',
                'phone' => '+52 443 456 7890',
                'email' => 'roberto@rancho-sanmiguel.mx',
                'address' => 'Km 15 Carretera a Los Reyes',
                'city' => 'Los Reyes',
                'state' => 'Michoacán',
                'notes' => 'Productor premium con certificaciones internacionales'
            ],
            [
                'name' => 'Frutos de la Tierra',
                'contact_person' => 'Isabel Morales',
                'phone' => '+52 443 567 8901',
                'email' => 'isabel@frutos-tierra.com',
                'address' => 'Ejido El Aguacate',
                'city' => 'Salvador Escalante',
                'state' => 'Michoacán',
                'notes' => 'Proveedor familiar con tradición de 3 generaciones'
            ]
        ];
        
        $createdSuppliers = collect();
        foreach ($suppliers as $supplierData) {
            $createdSuppliers->push(Supplier::create($supplierData));
        }
        
        echo "🚚 5 proveedores creados\n";
        return $createdSuppliers;
    }
    
    private function createCustomers()
    {
        $customers = [
            [
                'name' => 'Walmart de México',
                'contact_person' => 'Patricia Vega',
                'phone' => '+52 55 1234 5678',
                'email' => 'compras@walmart.com.mx',
                'address' => 'Blvd. Manuel Ávila Camacho 647',
                'city' => 'Ciudad de México',
                'state' => 'CDMX',
                'credit_limit' => 500000.00,
                'customer_type' => 'mayorista',
                'notes' => 'Cliente corporativo de alto volumen'
            ],
            [
                'name' => 'Soriana Mercados',
                'contact_person' => 'Miguel Torres',
                'phone' => '+52 81 2345 6789',
                'email' => 'proveedores@soriana.com',
                'address' => 'Av. Ricardo Margáin 444',
                'city' => 'Monterrey',
                'state' => 'Nuevo León',
                'credit_limit' => 300000.00,
                'customer_type' => 'mayorista',
                'notes' => 'Cadena retail con presencia nacional'
            ],
            [
                'name' => 'Mercado San Juan',
                'contact_person' => 'Carmen López',
                'phone' => '+52 55 3456 7890',
                'email' => 'carmen@mercado-sanjuan.mx',
                'address' => 'Calle Ernesto Pugibet 21',
                'city' => 'Ciudad de México',
                'state' => 'CDMX',
                'credit_limit' => 50000.00,
                'customer_type' => 'minorista',
                'notes' => 'Mercado tradicional de alta rotación'
            ],
            [
                'name' => 'Exportadora Pacific Fresh',
                'contact_person' => 'David Kim',
                'phone' => '+1 213 456 7890',
                'email' => 'david@pacificfresh.com',
                'address' => '1234 Harbor Blvd',
                'city' => 'Los Angeles',
                'state' => 'California',
                'country' => 'Estados Unidos',
                'credit_limit' => 1000000.00,
                'customer_type' => 'distribuidor',
                'notes' => 'Exportación a Estados Unidos y Canadá'
            ],
            [
                'name' => 'Restaurantes Grupo Alsea',
                'contact_person' => 'Fernando Ruiz',
                'phone' => '+52 55 4567 8901',
                'email' => 'compras@alsea.com.mx',
                'address' => 'Santa Fe 505',
                'city' => 'Ciudad de México',
                'state' => 'CDMX',
                'credit_limit' => 150000.00,
                'customer_type' => 'mayorista',
                'notes' => 'Grupo restaurantero con múltiples marcas'
            ],
            [
                'name' => 'Central de Abastos CDMX',
                'contact_person' => 'Luis Hernández',
                'phone' => '+52 55 5678 9012',
                'email' => 'luis@central-abastos.mx',
                'address' => 'Av. Dr. Río de la Loza 195',
                'city' => 'Ciudad de México',
                'state' => 'CDMX',
                'credit_limit' => 200000.00,
                'customer_type' => 'distribuidor',
                'notes' => 'Mayor distribuidor de la central de abastos'
            ]
        ];
        
        $createdCustomers = collect();
        foreach ($customers as $customerData) {
            $createdCustomers->push(Customer::create($customerData));
        }
        
        echo "🏪 6 clientes creados\n";
        return $createdCustomers;
    }
    
    private function createLots($suppliers)
    {
        $lots = collect();
        $lotCounter = 1;
        
        // Crear lotes desde enero 2025 hasta agosto 2025
        for ($month = 1; $month <= 8; $month++) {
            $lotsPerMonth = rand(15, 25); // 15-25 lotes por mes
            
            for ($i = 0; $i < $lotsPerMonth; $i++) {
                $harvestDate = Carbon::create(2025, $month, rand(1, 28));
                $entryDate = $harvestDate->copy()->addDays(rand(1, 3));
                
                $supplier = $suppliers->random();
                $totalWeight = rand(800, 3000); // 800kg - 3000kg
                $pricePerKg = rand(28, 55); // $28 - $55 por kg
                
                $qualities = ['Primera', 'Segunda', 'Tercera', 'Extra'];
                $quality = $qualities[array_rand($qualities)];
                
                // Ajustar precio según calidad
                switch ($quality) {
                    case 'Extra':
                        $pricePerKg *= 1.3;
                        break;
                    case 'Primera':
                        $pricePerKg *= 1.1;
                        break;
                    case 'Segunda':
                        $pricePerKg *= 0.9;
                        break;
                    case 'Tercera':
                        $pricePerKg *= 0.7;
                        break;
                }
                
                $totalCost = $totalWeight * $pricePerKg;
                
                // Status basado en la fecha (lotes más antiguos más probables de estar vendidos)
                $daysSinceHarvest = $harvestDate->diffInDays(now());
                if ($daysSinceHarvest > 150) {
                    $status = collect(['vendido', 'vendido_parcial', 'vendido'])->random();
                } elseif ($daysSinceHarvest > 90) {
                    $status = collect(['disponible', 'vendido_parcial', 'vendido'])->random();
                } else {
                    $status = collect(['disponible', 'vendido_parcial'])->random();
                }
                
                // Calcular pesos vendidos y disponibles
                $weightSold = 0;
                $weightAvailable = $totalWeight;
                
                if ($status === 'vendido') {
                    $weightSold = $totalWeight;
                    $weightAvailable = 0;
                } elseif ($status === 'vendido_parcial') {
                    $weightSold = $totalWeight * (rand(20, 80) / 100);
                    $weightAvailable = $totalWeight - $weightSold;
                }
                
                $varieties = ['Hass', 'Fuerte', 'Criollo', 'Bacon'];
                $regions = [
                    'Uruapan', 'Tancítaro', 'Peribán', 'Los Reyes', 
                    'Salvador Escalante', 'Ario de Rosales'
                ];
                $calibers = ['32-36', '34-32', '30-28', '28-26', '26-24'];
                
                $metadata = [
                    'harvest_region' => $regions[array_rand($regions)],
                    'variety' => $varieties[array_rand($varieties)],
                    'organic' => rand(0, 100) < 20, // 20% orgánico
                    'caliber' => $calibers[array_rand($calibers)]
                ];
                
                $lot = Lot::create([
                    'lot_code' => sprintf('LOT-%04d%02d-%03d', 2025, $month, $lotCounter++),
                    'supplier_id' => $supplier->id,
                    'harvest_date' => $harvestDate,
                    'entry_date' => $entryDate,
                    'total_weight' => $totalWeight,
                    'purchase_price_per_kg' => round($pricePerKg, 2),
                    'total_purchase_cost' => round($totalCost, 2),
                    'quality_grade' => $quality,
                    'status' => $status,
                    'weight_sold' => round($weightSold, 2),
                    'weight_available' => round($weightAvailable, 2),
                    'metadata' => json_encode($metadata),
                    'created_at' => $entryDate,
                    'updated_at' => $entryDate
                ]);
                
                $lots->push($lot);
            }
        }
        
        echo "📦 " . $lots->count() . " lotes creados (enero-agosto 2025)\n";
        return $lots;
    }
    
    private function createSales($customers, $lots)
    {
        $sales = collect();
        $saleCounter = 1;
        
        // Crear ventas para lotes que tienen weight_sold > 0
        $soldLots = $lots->where('weight_sold', '>', 0);
        
        foreach ($soldLots as $lot) {
            $numSales = $lot->status === 'vendido' ? rand(1, 2) : 1;
            $remainingWeight = $lot->weight_sold;
            
            for ($i = 0; $i < $numSales && $remainingWeight > 0; $i++) {
                $customer = $customers->random();
                
                $saleDate = $lot->entry_date->copy()->addDays(rand(1, 30));
                
                // Peso de esta venta
                if ($i === $numSales - 1) {
                    $saleWeight = $remainingWeight; // Última venta lleva el resto
                } else {
                    $saleWeight = $remainingWeight * (rand(30, 70) / 100);
                }
                $remainingWeight -= $saleWeight;
                
                // Precio de venta (margen del 15-40% sobre costo)
                $marginMultiplier = rand(115, 140) / 100;
                $salePrice = $lot->purchase_price_per_kg * $marginMultiplier;
                
                $totalSaleAmount = $saleWeight * $salePrice;
                
                $sale = Sale::create([
                    'sale_code' => sprintf('VTA-%04d%02d-%03d', $saleDate->year, $saleDate->month, $saleCounter++),
                    'customer_id' => $customer->id,
                    'sale_date' => $saleDate,
                    'total_amount' => round($totalSaleAmount, 2),
                    'discount_amount' => 0.00,
                    'final_amount' => round($totalSaleAmount, 2),
                    'status' => collect(['pendiente', 'pagada', 'parcial'])->random(),
                    'notes' => 'Venta generada automáticamente - Datos de prueba',
                    'created_at' => $saleDate,
                    'updated_at' => $saleDate
                ]);
                
                // Crear item de venta
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'lot_id' => $lot->id,
                    'quantity' => round($saleWeight, 2),
                    'unit_price' => round($salePrice, 2),
                    'total_amount' => round($totalSaleAmount, 2),
                    'created_at' => $saleDate,
                    'updated_at' => $saleDate
                ]);
                
                $sales->push($sale);
            }
        }
        
        echo "💰 " . $sales->count() . " ventas creadas\n";
        return $sales;
    }
    
    private function createPayments($suppliers, $customers, $lots, $sales)
    {
        $paymentsCount = 0;
        
        // Pagos a proveedores (por lotes)
        foreach ($lots->random($lots->count() * 0.7) as $lot) { // 70% de lotes tienen pagos
            $numPayments = rand(1, 3);
            $remainingAmount = $lot->total_purchase_cost;
            
            for ($i = 0; $i < $numPayments && $remainingAmount > 10; $i++) {
                $paymentDate = $lot->entry_date->copy()->addDays(rand(5, 45));
                
                if ($i === $numPayments - 1) {
                    $paymentAmount = $remainingAmount;
                } else {
                    $paymentAmount = $remainingAmount * (rand(20, 60) / 100);
                }
                $remainingAmount -= $paymentAmount;
                
                $methods = ['transferencia', 'cheque', 'efectivo', 'deposito'];
                
                Payment::create([
                    'payment_code' => 'PAG-PROV-' . $lot->id . '-' . ($i + 1),
                    'payable_type' => 'App\\Models\\Lot',
                    'payable_id' => $lot->id,
                    'related_entity_type' => 'supplier',
                    'related_entity_id' => $lot->supplier_id,
                    'amount' => round($paymentAmount, 2),
                    'payment_date' => $paymentDate,
                    'payment_method' => $methods[array_rand($methods)],
                    'reference' => 'REF-' . rand(10000, 99999),
                    'status' => 'completed',
                    'notes' => 'Pago a proveedor por lote ' . $lot->lot_code,
                    'created_at' => $paymentDate,
                    'updated_at' => $paymentDate
                ]);
                
                $paymentsCount++;
            }
        }
        
        // Pagos de clientes (por ventas)
        foreach ($sales->random($sales->count() * 0.8) as $sale) { // 80% de ventas tienen pagos
            $numPayments = rand(1, 2);
            $remainingAmount = $sale->final_amount;
            
            for ($i = 0; $i < $numPayments && $remainingAmount > 10; $i++) {
                $paymentDate = $sale->sale_date->copy()->addDays(rand(1, 30));
                
                if ($i === $numPayments - 1) {
                    $paymentAmount = $remainingAmount;
                } else {
                    $paymentAmount = $remainingAmount * (rand(40, 80) / 100);
                }
                $remainingAmount -= $paymentAmount;
                
                $methods = ['transferencia', 'cheque', 'tarjeta', 'deposito'];
                
                Payment::create([
                    'payment_code' => 'PAG-CLI-' . $sale->id . '-' . ($i + 1),
                    'payable_type' => 'App\\Models\\Sale',
                    'payable_id' => $sale->id,
                    'related_entity_type' => 'customer',
                    'related_entity_id' => $sale->customer_id,
                    'amount' => round($paymentAmount, 2),
                    'payment_date' => $paymentDate,
                    'payment_method' => $methods[array_rand($methods)],
                    'reference' => 'REF-' . rand(10000, 99999),
                    'status' => 'completed',
                    'notes' => 'Pago de cliente por venta ' . $sale->sale_code,
                    'created_at' => $paymentDate,
                    'updated_at' => $paymentDate
                ]);
                
                $paymentsCount++;
            }
        }
        
        echo "💳 $paymentsCount pagos creados\n";
    }
}