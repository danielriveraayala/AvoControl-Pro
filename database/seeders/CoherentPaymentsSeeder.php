<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sale;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CoherentPaymentsSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Limpiar pagos existentes de ventas para evitar duplicados
        Payment::where('payable_type', 'App\\Models\\Sale')->delete();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        echo "🧹 Pagos de ventas anteriores limpiados\n";
        
        // Obtener todas las ventas
        $sales = Sale::all();
        
        if ($sales->isEmpty()) {
            echo "⚠️  No hay ventas registradas. Ejecuta SimpleProductionSeeder primero.\n";
            return;
        }
        
        $totalPaymentsCreated = 0;
        $salesWithPayments = 0;
        
        foreach ($sales as $sale) {
            $paymentStatus = $sale->payment_status;
            $totalAmount = $sale->total_amount;
            
            if ($paymentStatus === 'pending') {
                // Ventas pendientes: No crear pagos
                continue;
            } elseif ($paymentStatus === 'partial') {
                // Ventas parciales: Crear 1-3 pagos que sumen 30-70% del total
                $this->createPartialPayments($sale);
                $salesWithPayments++;
            } elseif ($paymentStatus === 'paid') {
                // Ventas pagadas: Crear pagos que sumen el 100%
                $this->createFullPayments($sale);
                $salesWithPayments++;
            }
        }
        
        $totalPaymentsCreated = Payment::where('payable_type', 'App\\Models\\Sale')->count();
        
        echo "✅ {$totalPaymentsCreated} pagos creados para {$salesWithPayments} ventas\n";
        echo "📊 Resumen:\n";
        echo "   - Ventas pendientes (sin pagos): " . $sales->where('payment_status', 'pending')->count() . "\n";
        echo "   - Ventas parciales (con pagos parciales): " . $sales->where('payment_status', 'partial')->count() . "\n";
        echo "   - Ventas pagadas (con pagos completos): " . $sales->where('payment_status', 'paid')->count() . "\n";
    }
    
    private function createPartialPayments(Sale $sale)
    {
        $totalAmount = $sale->total_amount;
        
        // Determinar porcentaje pagado (30-70%)
        $paidPercentage = rand(30, 70);
        $targetPaidAmount = $totalAmount * ($paidPercentage / 100);
        
        // Crear 1-3 pagos
        $numPayments = rand(1, 3);
        $remainingAmount = $targetPaidAmount;
        
        for ($i = 0; $i < $numPayments; $i++) {
            if ($remainingAmount <= 0) break;
            
            // Calcular monto de este pago
            if ($i === $numPayments - 1) {
                // Último pago lleva el resto
                $paymentAmount = $remainingAmount;
            } else {
                // Distribuir aleatoriamente
                $paymentAmount = $remainingAmount * (rand(30, 70) / 100);
            }
            
            // Fecha del pago (días después de la venta)
            $daysAfterSale = ($i * 15) + rand(5, 15); // Espaciar pagos cada 15-30 días
            $paymentDate = $sale->sale_date->copy()->addDays($daysAfterSale);
            
            // Si la fecha es futura, ajustar a máximo hoy
            if ($paymentDate->isFuture()) {
                $paymentDate = now()->subDays(rand(1, 10));
            }
            
            $methods = ['transfer', 'check', 'card', 'cash', 'credit'];
            $banks = ['BBVA', 'Santander', 'Banamex', 'HSBC', 'Banorte', 'Scotiabank'];
            
            Payment::create([
                'type' => 'income',
                'concept' => 'Pago parcial ' . ($i + 1) . ' - Venta ' . $sale->sale_code,
                'payable_type' => 'App\\Models\\Sale',
                'payable_id' => $sale->id,
                'payment_code' => 'PAG-' . strtoupper(substr(md5($sale->id . '-' . $i), 0, 8)),
                'payment_date' => $paymentDate,
                'amount' => round($paymentAmount, 2),
                'payment_method' => $methods[array_rand($methods)],
                'reference' => $this->generateReference($methods[array_rand($methods)], $banks[array_rand($banks)]),
                'status' => 'confirmed',
                'notes' => 'Pago parcial ' . ($i + 1) . ' de ' . $numPayments . ' - Cliente: ' . ($sale->customer->name ?? 'N/A'),
                'created_by' => 1,
                'created_at' => $paymentDate,
                'updated_at' => $paymentDate
            ]);
            
            $remainingAmount -= $paymentAmount;
        }
    }
    
    private function createFullPayments(Sale $sale)
    {
        $totalAmount = $sale->total_amount;
        
        // Determinar si fue pago único o múltiples pagos
        $isSinglePayment = rand(1, 100) <= 60; // 60% probabilidad de pago único
        
        if ($isSinglePayment) {
            // Pago único completo
            $paymentDate = $sale->sale_date->copy()->addDays(rand(1, 20));
            
            // Si la fecha es futura, ajustar
            if ($paymentDate->isFuture()) {
                $paymentDate = now()->subDays(rand(1, 5));
            }
            
            $methods = ['transfer', 'check', 'card', 'cash'];
            $banks = ['BBVA', 'Santander', 'Banamex', 'HSBC', 'Banorte'];
            $method = $methods[array_rand($methods)];
            
            Payment::create([
                'type' => 'income',
                'concept' => 'Pago total - Venta ' . $sale->sale_code,
                'payable_type' => 'App\\Models\\Sale',
                'payable_id' => $sale->id,
                'payment_code' => 'PAG-' . strtoupper(substr(md5($sale->id . '-FULL'), 0, 8)),
                'payment_date' => $paymentDate,
                'amount' => round($totalAmount, 2),
                'payment_method' => $method,
                'reference' => $this->generateReference($method, $banks[array_rand($banks)]),
                'status' => 'confirmed',
                'notes' => 'Pago completo - Cliente: ' . ($sale->customer->name ?? 'N/A'),
                'created_by' => 1,
                'created_at' => $paymentDate,
                'updated_at' => $paymentDate
            ]);
        } else {
            // Múltiples pagos que suman el total
            $numPayments = rand(2, 4);
            $remainingAmount = $totalAmount;
            
            for ($i = 0; $i < $numPayments; $i++) {
                // Calcular monto de este pago
                if ($i === $numPayments - 1) {
                    // Último pago lleva el resto exacto
                    $paymentAmount = $remainingAmount;
                } else {
                    // Distribuir proporcionalmente
                    $paymentAmount = $totalAmount / $numPayments + rand(-1000, 1000);
                    $paymentAmount = min($paymentAmount, $remainingAmount);
                    $paymentAmount = max($paymentAmount, $remainingAmount * 0.1); // Mínimo 10% del restante
                }
                
                // Fecha del pago
                $daysAfterSale = ($i * 10) + rand(1, 10);
                $paymentDate = $sale->sale_date->copy()->addDays($daysAfterSale);
                
                // Si la fecha es futura, ajustar
                if ($paymentDate->isFuture()) {
                    $paymentDate = now()->subDays(rand(5, 20) - $i * 3);
                }
                
                $methods = ['transfer', 'check', 'card', 'cash', 'credit'];
                $banks = ['BBVA', 'Santander', 'Banamex', 'HSBC', 'Banorte', 'Scotiabank', 'Inbursa'];
                $method = $methods[array_rand($methods)];
                
                Payment::create([
                    'type' => 'income',
                    'concept' => 'Pago ' . ($i + 1) . '/' . $numPayments . ' - Venta ' . $sale->sale_code,
                    'payable_type' => 'App\\Models\\Sale',
                    'payable_id' => $sale->id,
                    'payment_code' => 'PAG-' . strtoupper(substr(md5($sale->id . '-' . $i), 0, 8)),
                    'payment_date' => $paymentDate,
                    'amount' => round($paymentAmount, 2),
                    'payment_method' => $method,
                    'reference' => $this->generateReference($method, $banks[array_rand($banks)]),
                    'status' => 'confirmed',
                    'notes' => 'Pago ' . ($i + 1) . ' de ' . $numPayments . ' - Cliente: ' . ($sale->customer->name ?? 'N/A'),
                    'created_by' => 1,
                    'created_at' => $paymentDate,
                    'updated_at' => $paymentDate
                ]);
                
                $remainingAmount -= $paymentAmount;
            }
        }
    }
    
    private function generateReference($method, $bank = null)
    {
        switch ($method) {
            case 'transfer':
                // Referencia de transferencia bancaria
                return $bank . '-' . date('Ymd') . rand(1000, 9999);
                
            case 'check':
                // Número de cheque
                return 'CHQ-' . rand(100000, 999999);
                
            case 'card':
                // Últimos 4 dígitos de tarjeta
                return 'XXXX-XXXX-XXXX-' . rand(1000, 9999);
                
            case 'credit':
                // Ficha de depósito
                return 'CRE-' . $bank . '-' . rand(100000, 999999);
                
            case 'cash':
                // Recibo de caja
                return 'REC-' . date('Ymd') . '-' . rand(100, 999);
                
            default:
                return 'REF-' . rand(100000, 999999);
        }
    }
}