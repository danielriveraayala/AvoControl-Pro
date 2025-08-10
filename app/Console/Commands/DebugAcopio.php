<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lot;
use App\Models\QualityGrade;
use Illuminate\Support\Facades\DB;

class DebugAcopio extends Command
{
    protected $signature = 'debug:acopio';
    protected $description = 'Debug acopio inventory discrepancies';

    public function handle()
    {
        $this->info("=== ACOPIO INVENTORY DEBUG ===");
        
        // 1. Verificar calidades
        $this->info("\n=== QUALITY GRADES ===");
        $qualities = QualityGrade::orderBy('id')->get();
        foreach ($qualities as $quality) {
            $this->line("ID: {$quality->id}, Name: {$quality->name}");
        }
        
        // 2. Verificar lotes por calidad
        $this->info("\n=== LOTS BY QUALITY ===");
        $lots = Lot::with('qualityGrade')->where('status', '!=', 'cancelled')->get();
        $lotsByQuality = $lots->groupBy('quality_grade');
        
        foreach ($lotsByQuality as $qualityName => $lotsGroup) {
            $this->warn("Quality: {$qualityName}");
            $totalWeight = $lotsGroup->sum('total_weight');
            $soldWeight = $lotsGroup->sum('weight_sold'); 
            $availableWeight = $lotsGroup->sum('weight_available');
            $this->line("  Lotes: " . $lotsGroup->count());
            $this->line("  Peso Total: {$totalWeight}kg");
            $this->line("  Peso Vendido (lots.weight_sold): {$soldWeight}kg");
            $this->line("  Peso Disponible (lots.weight_available): {$availableWeight}kg");
            
            // Verificar los IDs también
            $qualityId = $lotsGroup->first()->quality_grade_id;
            $this->line("  Quality Grade ID: {$qualityId}");
        }
        
        // 3. Verificar ventas comprometidas
        $this->info("\n=== COMMITTED SALES (sale_items) ===");
        $salesByQuality = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', '!=', 'cancelled')
            ->select('sale_items.quality_grade')
            ->selectRaw('SUM(sale_items.weight) as total_weight')
            ->selectRaw('COUNT(*) as items_count')
            ->groupBy('sale_items.quality_grade')
            ->get();
            
        foreach ($salesByQuality as $sale) {
            $this->warn("Quality: {$sale->quality_grade}");
            $this->line("  Items: {$sale->items_count}");
            $this->line("  Peso Comprometido: {$sale->total_weight}kg");
        }
        
        // 4. Verificar sales y sale_items detalladamente
        $this->info("\n=== DETAILED SALES ===");
        $sales = DB::table('sales')
            ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', '!=', 'cancelled')
            ->select('sales.id', 'sales.sale_code', 'sale_items.quality_grade', 'sale_items.weight')
            ->get();
            
        foreach ($sales as $sale) {
            $this->line("Sale ID: {$sale->id}, Code: {$sale->sale_code}, Quality: {$sale->quality_grade}, Weight: {$sale->weight}kg");
        }
        
        // 5. Investigar lote problemático de "Cuarta"
        $this->info("\n=== CUARTA QUALITY LOT INVESTIGATION ===");
        $cuartaLot = Lot::where('quality_grade', 'Cuarta')->first();
        if ($cuartaLot) {
            $this->warn("Lot ID: {$cuartaLot->id}, Code: {$cuartaLot->lot_code}");
            $this->line("  Weight Sold: {$cuartaLot->weight_sold}kg");
            $this->line("  Weight Available: {$cuartaLot->weight_available}kg"); 
            $this->line("  Total Weight: {$cuartaLot->total_weight}kg");
            
            // Buscar allocations de este lote
            $allocations = DB::table('sale_lot_allocations')
                ->where('lot_id', $cuartaLot->id)
                ->get();
            $this->line("  Sale Allocations: " . $allocations->count());
            foreach ($allocations as $alloc) {
                $this->line("    Allocation ID: {$alloc->id}, Weight: {$alloc->allocated_weight}kg");
            }
        }
        
        return 0;
    }
}