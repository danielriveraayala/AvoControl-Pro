<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use App\Models\QualityGrade;
use App\Models\SaleLotAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AcopioReportExport;
use App\Services\ChartImageService;

class AcopioController extends Controller
{
    public function index(Request $request)
    {
        // Obtener inventario agrupado por calidad
        $acopio = Lot::with(['qualityGrade'])
            ->select('quality_grade_id')
            ->selectRaw('COUNT(*) as total_lotes')
            ->selectRaw('SUM(total_weight) as peso_total')
            ->selectRaw('SUM(weight_sold) as peso_vendido') 
            ->selectRaw('SUM(weight_available) as peso_disponible')
            ->selectRaw('SUM(total_purchase_cost) as costo_total')
            ->selectRaw('AVG(purchase_price_per_kg) as precio_promedio')
            ->selectRaw('SUM(amount_paid) as total_pagado')
            ->selectRaw('SUM(amount_owed) as total_adeudado')
            ->where('status', '!=', 'cancelled')
            ->where('quality_grade_id', '!=', null)
            ->groupBy('quality_grade_id')
            ->orderBy('quality_grade_id')
            ->get();
            
        // Calcular ventas comprometidas por calidad
        $ventasComprometidas = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', '!=', 'cancelled')
            ->select('sale_items.quality_grade')
            ->selectRaw('SUM(sale_items.weight) as cantidad_vendida')
            ->groupBy('sale_items.quality_grade')
            ->get()
            ->keyBy('quality_grade');
            
        // Verificar déficit de inventario y alertas de poco stock
        $alertas = [];
        $alertasPocaExistencia = [];
        
        foreach ($acopio as $item) {
            $qualityName = $item->qualityGrade ? $item->qualityGrade->name : 'Sin calidad';
            $pesoDisponible = $item->peso_disponible ?? 0;
            $pesoComprometido = $ventasComprometidas->get($qualityName)->cantidad_vendida ?? 0;
            $pesoTotal = $item->peso_total ?? 0;
            
            // El balance real ES el peso disponible (ya descontadas las ventas)
            $balanceReal = $pesoDisponible;
            
            $item->peso_comprometido = $pesoComprometido;
            $item->balance_real = $balanceReal;
            
            // DÉFICIT REAL: Solo cuando las ventas superan el inventario total (< 0%)
            // Si pesoDisponible = 0 pero pesoComprometido <= pesoTotal, NO es déficit
            $deficit = $pesoComprometido - $pesoTotal; // Cambio clave: comparar con peso total, no disponible
            $item->tiene_deficit = $deficit > 0;
            
            if ($deficit > 0) {
                $alertas[] = [
                    'tipo' => 'deficit',
                    'calidad' => $qualityName,
                    'deficit' => $deficit,
                    'disponible' => $pesoDisponible,
                    'comprometido' => $pesoComprometido,
                    'total' => $pesoTotal,
                    'porcentaje_disponible' => 0
                ];
            }
            // ALERTA DE POCO INVENTARIO: Entre 0% y 20% del inventario total disponible
            // Incluir 0% porque si vendes todo YA NO TIENES NADA QUE VENDER
            elseif ($pesoTotal > 0) {
                $porcentajeDisponible = ($pesoDisponible / $pesoTotal) * 100;
                
                // Marcar alerta si está entre 0% y 20% del inventario total
                // 0% también genera alerta (no tienes nada que vender)
                if ($porcentajeDisponible >= 0 && $porcentajeDisponible <= 20) {
                    $alertasPocaExistencia[] = [
                        'tipo' => 'poco_stock',
                        'calidad' => $qualityName,
                        'disponible' => $pesoDisponible,
                        'total' => $pesoTotal,
                        'porcentaje_disponible' => round($porcentajeDisponible, 1),
                        'comprometido' => $pesoComprometido
                    ];
                }
            }
        }

        // Estadísticas generales
        $stats = [
            'total_lotes' => Lot::where('status', '!=', 'cancelled')->count(),
            'peso_total' => Lot::where('status', '!=', 'cancelled')->sum('total_weight'),
            'valor_total' => Lot::where('status', '!=', 'cancelled')->sum('total_purchase_cost'),
            'peso_disponible' => Lot::where('status', '!=', 'cancelled')->sum('weight_available'),
            'total_adeudado' => Lot::where('status', '!=', 'cancelled')->sum('amount_owed')
        ];

        // Obtener lotes recientes por calidad
        $lotesRecientes = Lot::with(['supplier', 'qualityGrade'])
            ->where('status', '!=', 'cancelled')
            ->where('quality_grade_id', '!=', null)
            ->orderBy('entry_date', 'desc')
            ->limit(10)
            ->get()
            ->groupBy('quality_grade_id');

        if ($request->ajax()) {
            return response()->json([
                'acopio' => $acopio,
                'stats' => $stats,
                'alertas' => $alertas,
                'alertasPocaExistencia' => $alertasPocaExistencia
            ]);
        }

        return view('acopio.index', compact('acopio', 'stats', 'lotesRecientes', 'alertas', 'alertasPocaExistencia'));
    }

    public function show(Request $request, $quality)
    {
        // Mostrar detalles de una calidad específica (buscar por nombre de calidad)
        $qualityGradeRecord = \App\Models\QualityGrade::where('name', $quality)->first();
        if (!$qualityGradeRecord) {
            abort(404, 'Calidad no encontrada');
        }
        
        $lotes = Lot::with(['supplier', 'saleAllocations.saleItem.sale', 'qualityGrade'])
            ->where('quality_grade_id', $qualityGradeRecord->id)
            ->where('status', '!=', 'cancelled')
            ->orderBy('entry_date', 'desc')
            ->paginate(20);

        $stats = Lot::with(['qualityGrade'])
            ->where('quality_grade_id', $qualityGradeRecord->id)
            ->where('status', '!=', 'cancelled')
            ->selectRaw('COUNT(*) as total_lotes')
            ->selectRaw('SUM(total_weight) as peso_total')
            ->selectRaw('SUM(weight_sold) as peso_vendido')
            ->selectRaw('SUM(weight_available) as peso_disponible')
            ->selectRaw('SUM(total_purchase_cost) as costo_total')
            ->selectRaw('AVG(purchase_price_per_kg) as precio_promedio')
            ->first();

        if ($request->ajax()) {
            return response()->json([
                'lotes' => $lotes,
                'stats' => $stats
            ]);
        }

        return view('acopio.show', compact('lotes', 'stats', 'quality'));
    }


    public function reporte(Request $request)
    {
        // Generar reporte de acopio - usar un rango más amplio por defecto
        $fechaInicio = $request->get('fecha_inicio', now()->subMonths(3)->startOfMonth());
        $fechaFin = $request->get('fecha_fin', now()->endOfMonth());

        // Debug: verificar fechas
        \Log::info('Generando reporte con fechas:', [
            'inicio' => $fechaInicio,
            'fin' => $fechaFin
        ]);

        // Ingresos (lotes por calidad)
        $resumen = Lot::with(['qualityGrade'])
            ->whereBetween('entry_date', [$fechaInicio, $fechaFin])
            ->where('quality_grade_id', '!=', null)
            ->selectRaw('quality_grade_id')
            ->selectRaw('COUNT(*) as lotes_ingresados')
            ->selectRaw('SUM(total_weight) as peso_ingresado')
            ->selectRaw('SUM(total_purchase_cost) as inversion_total')
            ->groupBy('quality_grade_id')
            ->get();

        \Log::info('Lotes encontrados:', ['count' => $resumen->count()]);

        // Ventas agrupadas por calidad
        $ventasAllocations = SaleLotAllocation::whereHas('saleItem.sale', function($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('sale_date', [$fechaInicio, $fechaFin]);
            })
            ->with(['lot.qualityGrade', 'saleItem'])
            ->get();

        \Log::info('Allocations encontradas:', ['count' => $ventasAllocations->count()]);

        // Agrupar ventas por calidad y calcular totales
        $ventasPorCalidad = [];
        foreach ($ventasAllocations as $allocation) {
            $qualityName = $allocation->lot->qualityGrade ? $allocation->lot->qualityGrade->name : 'Sin calidad';
            
            if (!isset($ventasPorCalidad[$qualityName])) {
                $ventasPorCalidad[$qualityName] = collect();
            }
            
            $ventasPorCalidad[$qualityName]->push($allocation);
        }

        // Obtener ventas mensuales por calidad (desde enero 2025 hasta mes actual)
        $ventasMensuales = SaleLotAllocation::with(['lot.qualityGrade', 'saleItem.sale'])
            ->whereHas('saleItem.sale', function($query) {
                $query->where('sale_date', '>=', '2025-01-01')
                      ->where('sale_date', '<=', now()->endOfMonth())
                      ->where('status', '!=', 'cancelled');
            })
            ->whereHas('lot', function($query) {
                $query->where('quality_grade_id', '!=', null);
            })
            ->get()
            ->groupBy(function($allocation) {
                return $allocation->saleItem->sale->sale_date->format('Y-m');
            })
            ->map(function($monthAllocations) {
                return $monthAllocations->groupBy(function($allocation) {
                    return $allocation->lot->qualityGrade ? $allocation->lot->qualityGrade->name : 'Sin calidad';
                })->map(function($qualityAllocations) {
                    return $qualityAllocations->sum(function($allocation) {
                        return $allocation->allocated_weight * $allocation->saleItem->price_per_kg;
                    });
                });
            });

        $reporte = [
            'periodo' => [
                'inicio' => $fechaInicio,
                'fin' => $fechaFin
            ],
            'resumen' => $resumen,
            'ventas' => $ventasPorCalidad,
            'ventas_mensuales' => $ventasMensuales
        ];

        // Obtener colores de calidades para la vista
        $qualityColors = \App\Models\QualityGrade::where('active', true)
            ->get()
            ->keyBy('name')
            ->map(function($quality) {
                return $quality->color ?: '#6c757d';
            })
            ->toArray();

        // Manejar exportaciones
        if ($request->has('export')) {
            $format = $request->get('export');
            
            if ($format === 'pdf') {
                // Generate charts for PDF
                $chartService = new ChartImageService();
                $charts = [];
                
                // Pie chart for ingresos
                if ($reporte['resumen']->count() > 0) {
                    $ingresosData = $reporte['resumen']->pluck('inversion_total')->map(function($value) {
                        return is_numeric($value) ? floatval($value) : 0;
                    })->toArray();
                    
                    $ingresosLabels = $reporte['resumen']->map(function($item) {
                        return $item->qualityGrade ? $item->qualityGrade->name : 'Sin calidad';
                    })->toArray();
                    
                    $ingresosColors = $reporte['resumen']->map(function($item) {
                        return $item->qualityGrade ? $item->qualityGrade->color : '#6c757d';
                    })->toArray();
                    
                    // Only generate chart if we have valid data
                    if (array_sum($ingresosData) > 0) {
                        $charts['ingresos'] = $chartService->generatePieChart(
                            $ingresosData, 
                            $ingresosLabels, 
                            $ingresosColors, 
                            'Distribución de Inversiones por Calidad'
                        );
                    }
                }
                
                // Pie chart for ventas
                if (is_array($reporte['ventas']) && count($reporte['ventas']) > 0) {
                    $ventasData = [];
                    $ventasLabels = [];
                    $ventasColors = [];
                    
                    foreach ($reporte['ventas'] as $calidad => $ventas) {
                        $ingresos = $ventas->sum(function($item) {
                            return $item->allocated_weight * $item->saleItem->price_per_kg;
                        });
                        
                        // Ensure numeric conversion and positive values
                        $ingresosNumeric = is_numeric($ingresos) ? floatval($ingresos) : 0;
                        
                        if ($ingresosNumeric > 0) {
                            $ventasData[] = $ingresosNumeric;
                            $ventasLabels[] = strval($calidad);
                            $ventasColors[] = $qualityColors[$calidad] ?? '#6c757d';
                        }
                    }
                    
                    if (!empty($ventasData) && array_sum($ventasData) > 0) {
                        $charts['ventas'] = $chartService->generatePieChart(
                            $ventasData, 
                            $ventasLabels, 
                            $ventasColors, 
                            'Distribución de Ventas por Calidad'
                        );
                    }
                }
                
                // Pie chart for ventas mensuales (using pie chart since bar chart needs fixing)
                if (isset($reporte['ventas_mensuales']) && count($reporte['ventas_mensuales']) > 0) {
                    // Calculate total sales per quality for the year
                    $calidades = \App\Models\QualityGrade::where('active', true)->orderBy('name')->get();
                    $ventasMensualesData = [];
                    $ventasMensualesLabels = [];
                    $ventasMensualesColors = [];
                    
                    foreach ($calidades as $calidad) {
                        $totalVentasCalidad = 0;
                        
                        // Sum all months for this quality
                        foreach ($reporte['ventas_mensuales'] as $mes => $ventasMes) {
                            $ventaMes = $ventasMes[$calidad->name] ?? 0;
                            $totalVentasCalidad += is_numeric($ventaMes) ? floatval($ventaMes) : 0;
                        }
                        
                        if ($totalVentasCalidad > 0) {
                            $ventasMensualesData[] = $totalVentasCalidad;
                            $ventasMensualesLabels[] = $calidad->name;
                            $ventasMensualesColors[] = $calidad->color ?: '#6c757d';
                        }
                    }
                    
                    if (!empty($ventasMensualesData) && array_sum($ventasMensualesData) > 0) {
                        $charts['mensuales'] = $chartService->generatePieChart(
                            $ventasMensualesData, 
                            $ventasMensualesLabels, 
                            $ventasMensualesColors, 
                            'Ventas por Calidad - ' . date('Y')
                        );
                    }
                }
                
                // Convert absolute paths to data URLs for PDF embedding
                $chartsForPdf = [];
                foreach ($charts as $key => $chartPath) {
                    if ($chartPath && file_exists($chartPath)) {
                        $imageData = base64_encode(file_get_contents($chartPath));
                        $chartsForPdf[$key] = 'data:image/png;base64,' . $imageData;
                        
                        // Clean up temp file after encoding
                        unlink($chartPath);
                    }
                }
                
                $pdf = PDF::loadView('acopio.pdf.reporte', compact('reporte', 'qualityColors', 'charts', 'chartsForPdf'));
                $pdf->setOptions(['isPhpEnabled' => true, 'isRemoteEnabled' => true]);
                return $pdf->download('reporte-acopio-' . \Carbon\Carbon::parse($fechaInicio)->format('Y-m-d') . '-' . \Carbon\Carbon::parse($fechaFin)->format('Y-m-d') . '.pdf');
            }
            
            if ($format === 'excel') {
                return Excel::download(
                    new AcopioReportExport($reporte, $fechaInicio, $fechaFin),
                    'reporte-acopio-' . \Carbon\Carbon::parse($fechaInicio)->format('Y-m-d') . '-' . \Carbon\Carbon::parse($fechaFin)->format('Y-m-d') . '.xlsx'
                );
            }
        }

        if ($request->wantsJson()) {
            return response()->json($reporte);
        }

        return view('acopio.reporte', compact('reporte', 'qualityColors'));
    }
}