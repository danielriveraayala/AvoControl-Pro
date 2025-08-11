<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Lot;
use App\Models\Sale;
use App\Models\QualityGrade;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SimpleProductionSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Limpiar solo las tablas necesarias (no sale_items ni payments por ahora)
        Lot::truncate();
        Customer::truncate();
        Supplier::truncate();
        User::truncate();
        QualityGrade::truncate();
        Sale::truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        echo "🧹 Datos anteriores limpiados\n";
        
        // 1. Crear quality grades primero
        $this->createQualityGrades();
        
        // 2. Crear usuarios
        $this->createUsers();
        
        // 3. Crear proveedores
        $suppliers = $this->createSuppliers();
        
        // 4. Crear clientes  
        $customers = $this->createCustomers();
        
        // 5. Crear lotes (enero - agosto 2025)
        $lots = $this->createLots($suppliers);
        
        // 6. Crear ventas simples (sin items por ahora)
        $this->createSimpleSales($customers);
        
        echo "✅ Seeder simplificado ejecutado exitosamente!\n";
        echo "📊 Datos básicos creados para enero-agosto 2025\n";
        echo "⚠️  Nota: Las ventas no tienen items para evitar conflictos con la lógica de negocio\n";
    }
    
    private function createQualityGrades()
    {
        $grades = [
            [
                'name' => 'Extra',
                'caliber_min' => 14,
                'caliber_max' => 18,
                'weight_min' => 266,
                'weight_max' => 365,
                'description' => 'Calidad premium para exportación',
                'active' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'Primera',
                'caliber_min' => 20,
                'caliber_max' => 24,
                'weight_min' => 203,
                'weight_max' => 243,
                'description' => 'Primera calidad',
                'active' => true,
                'sort_order' => 2
            ],
            [
                'name' => 'Segunda',
                'caliber_min' => 26,
                'caliber_max' => 32,
                'weight_min' => 156,
                'weight_max' => 190,
                'description' => 'Segunda calidad',
                'active' => true,
                'sort_order' => 3
            ],
            [
                'name' => 'Tercera',
                'caliber_min' => 36,
                'caliber_max' => 40,
                'weight_min' => 123,
                'weight_max' => 148,
                'description' => 'Tercera calidad',
                'active' => true,
                'sort_order' => 4
            ]
        ];
        
        foreach ($grades as $grade) {
            QualityGrade::create($grade);
        }
        
        echo "📏 4 calidades creadas\n";
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
            ]
        ];
        
        foreach ($users as $userData) {
            User::create($userData);
        }
        
        echo "👥 3 usuarios creados\n";
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
                'notes' => 'Proveedor confiable'
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
                'notes' => 'Especialistas en Hass'
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
                'notes' => 'Cooperativa local'
            ]
        ];
        
        $createdSuppliers = collect();
        foreach ($suppliers as $supplierData) {
            $createdSuppliers->push(Supplier::create($supplierData));
        }
        
        echo "🚚 3 proveedores creados\n";
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
                'notes' => 'Cliente corporativo'
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
                'notes' => 'Cadena retail'
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
                'notes' => 'Mercado local'
            ]
        ];
        
        $createdCustomers = collect();
        foreach ($customers as $customerData) {
            $createdCustomers->push(Customer::create($customerData));
        }
        
        echo "🏪 3 clientes creados\n";
        return $createdCustomers;
    }
    
    private function createLots($suppliers)
    {
        $lots = collect();
        $lotCounter = 1;
        $qualityGrades = QualityGrade::all();
        
        // Crear lotes desde enero 2025 hasta agosto 2025
        for ($month = 1; $month <= 8; $month++) {
            $lotsPerMonth = rand(10, 15); // 10-15 lotes por mes
            
            for ($i = 0; $i < $lotsPerMonth; $i++) {
                $harvestDate = Carbon::create(2025, $month, rand(1, 28));
                $entryDate = $harvestDate->copy()->addDays(rand(1, 3));
                
                $supplier = $suppliers->random();
                $qualityGrade = $qualityGrades->random();
                $totalWeight = rand(800, 3000); // 800kg - 3000kg
                $pricePerKg = rand(28, 55); // $28 - $55 por kg
                
                // Ajustar precio según calidad
                switch ($qualityGrade->name) {
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
                
                // Status basado en la fecha
                $daysSinceHarvest = $harvestDate->diffInDays(now());
                if ($daysSinceHarvest > 150) {
                    $status = 'sold';
                    $weightSold = $totalWeight;
                    $weightAvailable = 0;
                } elseif ($daysSinceHarvest > 90) {
                    $status = 'partial';
                    $weightSold = $totalWeight * 0.5;
                    $weightAvailable = $totalWeight - $weightSold;
                } else {
                    $status = 'active';
                    $weightSold = 0;
                    $weightAvailable = $totalWeight;
                }
                
                // Pagos aleatorios
                $amountPaid = $totalCost * (rand(0, 100) / 100);
                $amountOwed = $totalCost - $amountPaid;
                $paymentStatus = $amountPaid == 0 ? 'pending' : ($amountPaid >= $totalCost ? 'paid' : 'partial');
                
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
                    'quality_grade' => $qualityGrade->name,
                    'quality_grade_id' => $qualityGrade->id,
                    'status' => $status,
                    'notes' => 'Lote de ' . $qualityGrade->name . ' calidad',
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
    
    private function createSimpleSales($customers)
    {
        $saleCounter = 1;
        
        // Crear algunas ventas simples sin items
        for ($month = 1; $month <= 8; $month++) {
            $salesPerMonth = rand(5, 10);
            
            for ($i = 0; $i < $salesPerMonth; $i++) {
                $customer = $customers->random();
                $saleDate = Carbon::create(2025, $month, rand(1, 28));
                
                $totalWeight = rand(100, 500); // 100-500 kg
                $totalAmount = $totalWeight * rand(40, 70); // Precio promedio
                
                $saleCode = sprintf('VTA-%04d%02d-%03d', $saleDate->year, $saleDate->month, $saleCounter);
                $invoiceNumber = sprintf('F-%04d%02d-%03d', $saleDate->year, $saleDate->month, $saleCounter);
                $saleCounter++;
                
                Sale::create([
                    'sale_code' => $saleCode,
                    'invoice_number' => $invoiceNumber,
                    'customer_id' => $customer->id,
                    'created_by' => 1, // Admin user
                    'sale_date' => $saleDate,
                    'delivery_date' => $saleDate->copy()->addDays(rand(1, 3)),
                    'total_weight' => round($totalWeight, 2),
                    'total_amount' => round($totalAmount, 2),
                    'payment_status' => collect(['pending', 'partial', 'paid'])->random(),
                    'status' => collect(['confirmed', 'delivered'])->random(),
                    'due_date' => $saleDate->copy()->addDays(30),
                    'notes' => 'Venta de prueba',
                    'created_at' => $saleDate,
                    'updated_at' => $saleDate
                ]);
            }
        }
        
        echo "💰 " . ($saleCounter - 1) . " ventas simples creadas\n";
    }
}