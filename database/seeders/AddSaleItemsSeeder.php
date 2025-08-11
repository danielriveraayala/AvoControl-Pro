<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\QualityGrade;
use App\Models\Lot;
use Illuminate\Support\Facades\DB;

class AddSaleItemsSeeder extends Seeder
{
    public function run()
    {
        // Desactivar eventos para evitar la lógica de negocio
        SaleItem::unsetEventDispatcher();
        
        echo "📦 Agregando items a las ventas existentes...\n";
        
        $qualityGrades = QualityGrade::all();
        
        if ($qualityGrades->isEmpty()) {
            echo "⚠️  No hay calidades registradas. Ejecuta SimpleProductionSeeder primero.\n";
            return;
        }
        
        // Obtener ventas sin items
        $salesWithoutItems = Sale::doesntHave('saleItems')->get();
        
        if ($salesWithoutItems->isEmpty()) {
            echo "✅ Todas las ventas ya tienen items.\n";
            return;
        }
        
        $itemsCreated = 0;
        
        foreach ($salesWithoutItems as $sale) {
            // Determinar cuántos items crear (1-3 items por venta)
            $numItems = rand(1, min(3, $qualityGrades->count()));
            $usedGrades = [];
            $remainingWeight = $sale->total_weight;
            $remainingAmount = $sale->total_amount;
            
            for ($i = 0; $i < $numItems; $i++) {
                // Seleccionar una calidad que no se haya usado en esta venta
                $availableGrades = $qualityGrades->whereNotIn('id', $usedGrades);
                if ($availableGrades->isEmpty()) {
                    break;
                }
                
                $qualityGrade = $availableGrades->random();
                $usedGrades[] = $qualityGrade->id;
                
                // Calcular peso para este item
                if ($i === $numItems - 1) {
                    // Último item lleva el peso restante
                    $itemWeight = $remainingWeight;
                    $itemAmount = $remainingAmount;
                } else {
                    // Distribuir proporcionalmente
                    $itemWeight = $remainingWeight * (rand(30, 60) / 100);
                    $itemAmount = $remainingAmount * ($itemWeight / $remainingWeight);
                }
                
                // Calcular precio por kg basado en el monto y peso
                $pricePerKg = $itemWeight > 0 ? $itemAmount / $itemWeight : 0;
                
                // Crear el sale_item sin activar eventos
                DB::table('sale_items')->insert([
                    'sale_id' => $sale->id,
                    'quality_grade' => $qualityGrade->name,
                    'weight' => round($itemWeight, 2),
                    'price_per_kg' => round($pricePerKg, 2),
                    'subtotal' => round($itemAmount, 2),
                    'notes' => 'Item generado automáticamente para datos de prueba',
                    'created_at' => $sale->created_at,
                    'updated_at' => $sale->updated_at
                ]);
                
                $remainingWeight -= $itemWeight;
                $remainingAmount -= $itemAmount;
                $itemsCreated++;
            }
        }
        
        echo "✅ {$itemsCreated} items de venta creados para {$salesWithoutItems->count()} ventas\n";
        
        // Ahora crear algunas asignaciones de lotes para que haya datos de costos
        echo "🔗 Creando asignaciones de lotes...\n";
        $this->createSampleAllocations();
    }
    
    private function createSampleAllocations()
    {
        // Obtener items sin asignaciones
        $itemsWithoutAllocations = SaleItem::doesntHave('allocations')->get();
        
        if ($itemsWithoutAllocations->isEmpty()) {
            echo "✅ Todos los items ya tienen asignaciones.\n";
            return;
        }
        
        $allocationsCreated = 0;
        
        foreach ($itemsWithoutAllocations as $item) {
            // Buscar lotes de la misma calidad
            $qualityGrade = QualityGrade::where('name', $item->quality_grade)->first();
            
            if (!$qualityGrade) {
                continue;
            }
            
            // Obtener lotes con inventario de esa calidad
            $availableLots = Lot::where('quality_grade_id', $qualityGrade->id)
                               ->where('weight_sold', '>', 0) // Lotes que ya tienen ventas
                               ->orderBy('entry_date', 'asc')
                               ->limit(3)
                               ->get();
            
            if ($availableLots->isEmpty()) {
                continue;
            }
            
            $remainingWeight = $item->weight;
            
            foreach ($availableLots as $lot) {
                if ($remainingWeight <= 0) break;
                
                // Asignar peso de este lote
                $allocatedWeight = min($remainingWeight, $lot->weight_sold * 0.3); // Usar máximo 30% del peso vendido del lote
                
                if ($allocatedWeight > 0) {
                    // Crear asignación directamente en la BD sin eventos
                    DB::table('sale_lot_allocations')->insert([
                        'sale_item_id' => $item->id,
                        'lot_id' => $lot->id,
                        'allocated_weight' => round($allocatedWeight, 2),
                        'cost_per_kg' => $lot->purchase_price_per_kg,
                        'allocated_cost' => round($allocatedWeight * $lot->purchase_price_per_kg, 2),
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at
                    ]);
                    
                    $remainingWeight -= $allocatedWeight;
                    $allocationsCreated++;
                }
            }
        }
        
        echo "✅ {$allocationsCreated} asignaciones de lotes creadas\n";
    }
}