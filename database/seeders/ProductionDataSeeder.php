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

class ProductionDataSeeder extends Seeder
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
        $this->createPayments($lots, $sales);
        
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
                'country' => 'México',
                'postal_code' => '60000',
                'status' => 'active',
                'balance_owed' => 0.00,
                'total_purchased' => 0.00,
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
                'country' => 'México',
                'postal_code' => '60460',
                'status' => 'active',
                'balance_owed' => 0.00,
                'total_purchased' => 0.00,
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
                'country' => 'México',
                'postal_code' => '60440',
                'status' => 'active',
                'balance_owed' => 0.00,
                'total_purchased' => 0.00,
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
                'country' => 'México',
                'postal_code' => '60300',
                'status' => 'active',
                'balance_owed' => 0.00,
                'total_purchased' => 0.00,
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
                'country' => 'México',
                'postal_code' => '60450',
                'status' => 'active',
                'balance_owed' => 0.00,
                'total_purchased' => 0.00,
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
                'country' => 'México',
                'postal_code' => '11520',
                'customer_type' => 'mayorista',
                'credit_limit' => 500000.00,
                'current_balance' => 0.00,
                'status' => 'active',
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
                'country' => 'México',
                'postal_code' => '66220',
                'customer_type' => 'mayorista',
                'credit_limit' => 300000.00,
                'current_balance' => 0.00,
                'status' => 'active',
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
                'country' => 'México',
                'postal_code' => '06050',
                'customer_type' => 'minorista',
                'credit_limit' => 50000.00,
                'current_balance' => 0.00,
                'status' => 'active',
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
                'postal_code' => '90001',
                'customer_type' => 'distribuidor',
                'credit_limit' => 1000000.00,
                'current_balance' => 0.00,
                'status' => 'active',
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
                'country' => 'México',
                'postal_code' => '01219',
                'customer_type' => 'mayorista',
                'credit_limit' => 150000.00,
                'current_balance' => 0.00,
                'status' => 'active',
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
                'country' => 'México',
                'postal_code' => '09040',
                'customer_type' => 'distribuidor',
                'credit_limit' => 200000.00,
                'current_balance' => 0.00,
                'status' => 'active',
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
                    $status = collect(['sold', 'partial', 'sold'])->random();
                } elseif ($daysSinceHarvest > 90) {
                    $status = collect(['active', 'partial', 'sold'])->random();
                } else {
                    $status = collect(['active', 'partial'])->random();
                }
                
                // Calcular pesos vendidos y disponibles
                $weightSold = 0;
                $weightAvailable = $totalWeight;
                
                if ($status === 'sold') {
                    $weightSold = $totalWeight;
                    $weightAvailable = 0;
                } elseif ($status === 'partial') {
                    $weightSold = $totalWeight * (rand(20, 80) / 100);
                    $weightAvailable = $totalWeight - $weightSold;
                }
                
                // Calcular pagos aleatorios
                $amountPaid = 0;
                $amountOwed = $totalCost;
                $paymentStatus = 'pending';
                
                // 60% de lotes tienen algún pago
                if (rand(1, 100) <= 60) {
                    $paidPercentage = rand(20, 100);
                    $amountPaid = $totalCost * ($paidPercentage / 100);
                    $amountOwed = $totalCost - $amountPaid;
                    
                    if ($paidPercentage >= 100) {
                        $paymentStatus = 'paid';
                        $amountOwed = 0;
                    } else {
                        $paymentStatus = 'partial';
                    }
                }
                
                $lot = Lot::create([
                    'lot_code' => sprintf('LOT-%04d%02d-%03d', 2025, $month, $lotCounter++),
                    'supplier_id' => $supplier->id,
                    'harvest_date' => $harvestDate,
                    'entry_date' => $entryDate,
                    'total_weight' => $totalWeight,
                    'purchase_price_per_kg' => round($pricePerKg, 2),
                    'total_purchase_cost' => round($totalCost, 2),
                    'amount_paid' => round($amountPaid, 2),
                    'amount_owed' => round($amountOwed, 2),
                    'payment_status' => $paymentStatus,
                    'quality_grade' => $quality,
                    'quality_grade_id' => null, // No tenemos tabla quality_grades
                    'status' => $status,
                    'notes' => 'Lote de ' . $quality . ' calidad',
                    'weight_sold' => round($weightSold, 2),
                    'weight_available' => round($weightAvailable, 2),
                    'created_at' => $entryDate,
                    'updated_at' => $entryDate
                ]);
                
                // Actualizar totales del proveedor
                $supplier->total_purchased += $totalCost;
                $supplier->balance_owed += $amountOwed;
                $supplier->save();
                
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
            $numSales = $lot->status === 'sold' ? rand(1, 2) : 1;
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
                
                $saleCode = sprintf('VTA-%04d%02d-%03d', $saleDate->year, $saleDate->month, $saleCounter);
                $invoiceNumber = sprintf('F-%04d%02d-%03d', $saleDate->year, $saleDate->month, $saleCounter);
                $saleCounter++;
                
                $sale = Sale::create([
                    'sale_code' => $saleCode,
                    'invoice_number' => $invoiceNumber,
                    'customer_id' => $customer->id,
                    'created_by' => 1, // Admin user
                    'sale_date' => $saleDate,
                    'delivery_date' => $saleDate->copy()->addDays(rand(1, 3)),
                    'total_weight' => round($saleWeight, 2),
                    'total_amount' => round($totalSaleAmount, 2),
                    'payment_status' => collect(['pending', 'partial', 'paid'])->random(),
                    'status' => collect(['confirmed', 'delivered'])->random(),
                    'due_date' => $saleDate->copy()->addDays(30),
                    'notes' => 'Venta generada automáticamente - Datos de prueba',
                    'created_at' => $saleDate,
                    'updated_at' => $saleDate
                ]);
                
                // Crear item de venta con estructura correcta de sale_items
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'quality_grade' => $lot->quality_grade,
                    'weight' => round($saleWeight, 2),
                    'price_per_kg' => round($salePrice, 2),
                    'subtotal' => round($totalSaleAmount, 2),
                    'notes' => 'Item del lote ' . $lot->lot_code,
                    'created_at' => $saleDate,
                    'updated_at' => $saleDate
                ]);
                
                // Actualizar balance del cliente
                if ($sale->payment_status === 'pending') {
                    $customer->current_balance += $totalSaleAmount;
                    $customer->save();
                } elseif ($sale->payment_status === 'partial') {
                    $customer->current_balance += ($totalSaleAmount * 0.5);
                    $customer->save();
                }
                
                $sales->push($sale);
            }
        }
        
        echo "💰 " . $sales->count() . " ventas creadas\n";
        return $sales;
    }
    
    private function createPayments($lots, $sales)
    {
        $paymentsCount = 0;
        
        // Pagos a proveedores (por lotes)
        foreach ($lots->random($lots->count() * 0.7) as $lot) { // 70% de lotes tienen pagos
            if ($lot->payment_status !== 'pending') {
                $paymentDate = $lot->entry_date->copy()->addDays(rand(5, 45));
                
                $methods = ['transfer', 'cheque', 'cash'];
                
                Payment::create([
                    'type' => 'supplier',
                    'concept' => 'Pago por lote ' . $lot->lot_code,
                    'payable_type' => 'App\\Models\\Lot',
                    'payable_id' => $lot->id,
                    'payment_code' => 'PAG-PROV-' . $lot->id,
                    'payment_date' => $paymentDate,
                    'amount' => $lot->amount_paid,
                    'payment_method' => $methods[array_rand($methods)],
                    'reference' => 'REF-' . rand(10000, 99999),
                    'status' => 'completed',
                    'notes' => 'Pago a proveedor por lote ' . $lot->lot_code,
                    'created_by' => 1,
                    'created_at' => $paymentDate,
                    'updated_at' => $paymentDate
                ]);
                
                $paymentsCount++;
            }
        }
        
        // Pagos de clientes (por ventas)
        foreach ($sales->random($sales->count() * 0.8) as $sale) { // 80% de ventas tienen pagos
            if ($sale->payment_status !== 'pending') {
                $paymentDate = $sale->sale_date->copy()->addDays(rand(1, 30));
                
                $paymentAmount = $sale->payment_status === 'paid' 
                    ? $sale->total_amount 
                    : $sale->total_amount * 0.5;
                
                $methods = ['transfer', 'cheque', 'card', 'deposit'];
                
                Payment::create([
                    'type' => 'customer',
                    'concept' => 'Pago por venta ' . $sale->sale_code,
                    'payable_type' => 'App\\Models\\Sale',
                    'payable_id' => $sale->id,
                    'payment_code' => 'PAG-CLI-' . $sale->id,
                    'payment_date' => $paymentDate,
                    'amount' => round($paymentAmount, 2),
                    'payment_method' => $methods[array_rand($methods)],
                    'reference' => 'REF-' . rand(10000, 99999),
                    'status' => 'completed',
                    'notes' => 'Pago de cliente por venta ' . $sale->sale_code,
                    'created_by' => 1,
                    'created_at' => $paymentDate,
                    'updated_at' => $paymentDate
                ]);
                
                $paymentsCount++;
            }
        }
        
        echo "💳 $paymentsCount pagos creados\n";
    }
}