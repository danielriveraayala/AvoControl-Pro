<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Payment;

class UpdateCustomerBalancesSeeder extends Seeder
{
    public function run()
    {
        echo "💰 Actualizando balances de clientes...\n";
        
        $customers = Customer::all();
        $updatedCount = 0;
        
        foreach ($customers as $customer) {
            // Calcular total de ventas del cliente
            $totalVentas = $customer->sales()->sum('total_amount');
            
            // Calcular total de pagos del cliente
            $totalPagos = Payment::where('payable_type', 'App\\Models\\Sale')
                                ->whereIn('payable_id', $customer->sales->pluck('id'))
                                ->where('status', 'confirmed')
                                ->sum('amount');
            
            // El balance pendiente es: ventas - pagos
            $balancePendiente = $totalVentas - $totalPagos;
            
            // Actualizar el current_balance del cliente
            $customer->current_balance = max(0, $balancePendiente); // No permitir balances negativos
            $customer->save();
            
            if ($balancePendiente > 0) {
                echo "   ✅ {$customer->name}: $" . number_format($balancePendiente, 2) . " pendiente\n";
                $updatedCount++;
            } else {
                echo "   💚 {$customer->name}: Sin saldo pendiente\n";
            }
        }
        
        echo "\n✅ Balances actualizados para todos los clientes\n";
        echo "📊 {$updatedCount} clientes tienen saldo pendiente\n";
        
        // Mostrar resumen de cuentas por cobrar
        $totalPorCobrar = Customer::sum('current_balance');
        echo "💵 Total general por cobrar: $" . number_format($totalPorCobrar, 2) . "\n";
        
        // Eliminar archivo temporal
        if (file_exists('check_balances.php')) {
            unlink('check_balances.php');
            echo "🧹 Archivo temporal eliminado\n";
        }
    }
}