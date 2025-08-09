<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use App\Models\Sale;
use App\Models\Lot;
use App\Models\SaleLotAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index()
    {
        // Get dashboard metrics
        $metrics = $this->reportService->getDashboardMetrics();
        
        // Get recent sales
        $recentSales = Sale::with(['customer', 'saleItems'])
            ->latest('sale_date')
            ->take(5)
            ->get();

        // Get acopio (inventory) summary by quality
        $acopioSummary = Lot::with(['qualityGrade'])
            ->select('quality_grade_id')
            ->selectRaw('SUM(weight_available) as peso_disponible')
            ->selectRaw('SUM(weight_sold) as peso_vendido')
            ->selectRaw('SUM(total_weight) as peso_total')
            ->selectRaw('COUNT(*) as total_lotes')
            ->selectRaw('SUM(total_purchase_cost) as inversion_total')
            ->where('status', '!=', 'cancelled')
            ->where('quality_grade_id', '!=', null)
            ->groupBy('quality_grade_id')
            ->orderBy('quality_grade_id')
            ->get();

        // Get recent movements (sales allocations)
        $recentMovements = SaleLotAllocation::with(['saleItem.sale.customer', 'lot'])
            ->whereHas('saleItem.sale', function($query) {
                $query->whereDate('sale_date', '>=', now()->subDays(7));
            })
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Quality distribution for pie chart
        $qualityDistribution = $acopioSummary->map(function($item) {
            return [
                'quality' => $item->qualityGrade ? $item->qualityGrade->name : 'Sin calidad',
                'peso' => $item->peso_disponible,
                'value' => $item->inversion_total
            ];
        });
        
        // Verificar déficit de inventario
        $ventasComprometidas = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', '!=', 'cancelled')
            ->select('sale_items.quality_grade')
            ->selectRaw('SUM(sale_items.weight) as cantidad_vendida')
            ->groupBy('sale_items.quality_grade')
            ->get()
            ->keyBy('quality_grade');
            
        $alertasDeficit = [];
        foreach ($acopioSummary as $item) {
            $qualityName = $item->qualityGrade ? $item->qualityGrade->name : 'Sin calidad';
            $pesoDisponible = $item->peso_disponible ?? 0;
            $pesoComprometido = $ventasComprometidas->get($qualityName)->cantidad_vendida ?? 0;
            
            $balanceReal = $pesoDisponible - $pesoComprometido;
            
            if ($balanceReal < 0) {
                $alertasDeficit[] = [
                    'calidad' => $qualityName,
                    'deficit' => abs($balanceReal),
                    'disponible' => $pesoDisponible,
                    'comprometido' => $pesoComprometido
                ];
            }
        }

        return view('dashboard', compact(
            'metrics', 
            'recentSales', 
            'acopioSummary', 
            'recentMovements', 
            'qualityDistribution',
            'alertasDeficit'
        ));
    }
}