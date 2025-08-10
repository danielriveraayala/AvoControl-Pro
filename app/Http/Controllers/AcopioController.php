<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use App\Models\QualityGrade;
use App\Models\SaleLotAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            
        // Verificar déficit de inventario
        $alertas = [];
        foreach ($acopio as $item) {
            $qualityName = $item->qualityGrade ? $item->qualityGrade->name : 'Sin calidad';
            $pesoDisponible = $item->peso_disponible ?? 0;
            $pesoComprometido = $ventasComprometidas->get($qualityName)->cantidad_vendida ?? 0;
            
            // El balance real ES el peso disponible (ya descontadas las ventas)
            // El peso comprometido se usa solo para verificar consistencia
            $balanceReal = $pesoDisponible;
            
            $item->peso_comprometido = $pesoComprometido;
            $item->balance_real = $balanceReal;
            
            // Déficit ocurre cuando hay más ventas comprometidas que peso disponible
            $deficit = $pesoComprometido - $pesoDisponible;
            $item->tiene_deficit = $deficit > 0;
            
            if ($deficit > 0) {
                $alertas[] = [
                    'calidad' => $qualityName,
                    'deficit' => $deficit,
                    'disponible' => $pesoDisponible,
                    'comprometido' => $pesoComprometido
                ];
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

        // Calcular movimientos de inventario (ventas realizadas)
        $movimientos = SaleLotAllocation::with(['saleItem.sale.customer', 'lot'])
            ->whereHas('saleItem.sale', function($query) {
                $query->whereDate('sale_date', '>=', now()->subDays(30));
            })
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        if ($request->ajax()) {
            return response()->json([
                'acopio' => $acopio,
                'stats' => $stats,
                'movimientos' => $movimientos,
                'alertas' => $alertas
            ]);
        }

        return view('acopio.index', compact('acopio', 'stats', 'lotesRecientes', 'movimientos', 'alertas'));
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

    public function movimientos(Request $request)
    {
        // Handle DataTables AJAX requests
        if ($request->ajax()) {
            try {
                \Log::info('Movimientos AJAX request', [
                    'has_draw' => $request->has('draw'),
                    'draw' => $request->get('draw'),
                    'start' => $request->get('start'),
                    'length' => $request->get('length'),
                    'filters' => [
                        'quality_grade' => $request->get('quality_grade'),
                        'date_from' => $request->get('date_from'),
                        'date_to' => $request->get('date_to')
                    ]
                ]);
            
            // Obtener fechas de filtro - usar rango más amplio por defecto
            $fechaInicio = $request->get('date_from', now()->subDays(90)->format('Y-m-d'));
            $fechaFin = $request->get('date_to', now()->format('Y-m-d'));
            
            \Log::info('Fechas de filtro para movimientos', [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ]);
            
            // Entradas (lotes ingresados) - incluir todos los lotes si no hay filtro específico
            $entradas = Lot::with(['supplier', 'qualityGrade'])
                ->where('quality_grade_id', '!=', null)
                ->where('status', '!=', 'cancelled');
                
            // Aplicar filtro de fecha solo si se especifica
            if ($request->filled('date_from') || $request->filled('date_to')) {
                $entradas->whereBetween('entry_date', [$fechaInicio, $fechaFin]);
            } else {
                // Si no hay filtro de fecha, mostrar últimos 90 días
                $entradas->where('entry_date', '>=', now()->subDays(90));
            }
                
            // Salidas (ventas realizadas)
            $salidas = SaleLotAllocation::with(['lot.qualityGrade', 'saleItem.sale.customer']);
                
            // Aplicar filtro de fecha para salidas también
            if ($request->filled('date_from') || $request->filled('date_to')) {
                $salidas->whereHas('saleItem.sale', function($query) use ($fechaInicio, $fechaFin) {
                    $query->whereBetween('sale_date', [$fechaInicio, $fechaFin]);
                });
            } else {
                // Si no hay filtro de fecha, mostrar últimos 90 días
                $salidas->whereHas('saleItem.sale', function($query) {
                    $query->where('sale_date', '>=', now()->subDays(90));
                });
            }
            
            $salidas->orderBy('created_at', 'desc');

            // Aplicar filtros de calidad
            if ($request->filled('quality_grade')) {
                $qualityGradeRecord = \App\Models\QualityGrade::where('name', $request->quality_grade)->first();
                if ($qualityGradeRecord) {
                    $entradas->where('quality_grade_id', $qualityGradeRecord->id);
                    $salidas->whereHas('lot', function($q) use ($qualityGradeRecord) {
                        $q->where('quality_grade_id', $qualityGradeRecord->id);
                    });
                }
            }

            // Combinar movimientos
            $movimientos = collect();
            
            // Agregar entradas
            $lotesEncontrados = $entradas->get();
            \Log::info('Lotes encontrados para movimientos', [
                'count' => $lotesEncontrados->count(),
                'lotes_ids' => $lotesEncontrados->pluck('id')->toArray()
            ]);
            
            foreach ($lotesEncontrados as $lote) {
                $movimientos->push([
                    'id' => 'entrada_' . $lote->id,
                    'tipo' => 'entrada',
                    'fecha' => $lote->entry_date,
                    'calidad' => $lote->qualityGrade ? $lote->qualityGrade->name : 'Sin calidad',
                    'peso' => $lote->total_weight,
                    'referencia' => $lote->supplier ? $lote->supplier->name : 'Sin proveedor',
                    'descripcion' => "Ingreso de lote - {$lote->lot_code}",
                    'costo_unitario' => $lote->purchase_price_per_kg,
                    'valor_total' => $lote->total_purchase_cost,
                    'created_at' => $lote->created_at,
                    'fecha_sort' => $lote->entry_date
                ]);
            }
            
            // Agregar salidas (agrupadas por venta y calidad)
            $ventasAgrupadas = $salidas->get()
                ->groupBy(function($allocation) {
                    return $allocation->saleItem->sale->id . '_' . ($allocation->lot->qualityGrade ? $allocation->lot->qualityGrade->name : 'sin_calidad');
                });
                
            foreach ($ventasAgrupadas as $grupo => $allocations) {
                $primeraAllocation = $allocations->first();
                $pesoTotal = $allocations->sum('allocated_weight');
                $valorTotal = $allocations->sum(function($alloc) {
                    return $alloc->allocated_weight * $alloc->saleItem->price_per_kg;
                });
                
                $movimientos->push([
                    'id' => 'salida_' . $grupo,
                    'tipo' => 'salida',
                    'fecha' => $primeraAllocation->saleItem->sale->sale_date,
                    'calidad' => $primeraAllocation->lot->qualityGrade ? $primeraAllocation->lot->qualityGrade->name : 'Sin calidad',
                    'peso' => $pesoTotal,
                    'referencia' => $primeraAllocation->saleItem->sale->customer ? $primeraAllocation->saleItem->sale->customer->name : 'Sin cliente',
                    'descripcion' => "Venta - " . ($primeraAllocation->saleItem->sale->sale_code ?? $primeraAllocation->saleItem->sale->invoice_number ?? 'S/N'),
                    'costo_unitario' => $primeraAllocation->saleItem->price_per_kg,
                    'valor_total' => $valorTotal,
                    'created_at' => $primeraAllocation->created_at,
                    'fecha_sort' => $primeraAllocation->saleItem->sale->sale_date
                ]);
            }
            
            // Ordenar por fecha
            $movimientos = $movimientos->sortByDesc('fecha_sort')->values();
            
            \Log::info('Movimientos procesados', [
                'total_movimientos' => $movimientos->count(),
                'entradas_count' => $entradas->count(),
                'salidas_groups' => count($ventasAgrupadas)
            ]);
            
            // Aplicar búsqueda
            $search = $request->get('search');
            $searchValue = is_array($search) ? ($search['value'] ?? '') : '';
            if (!empty($searchValue)) {
                $movimientos = $movimientos->filter(function($item) use ($searchValue) {
                    return stripos($item['descripcion'], $searchValue) !== false ||
                           stripos($item['referencia'], $searchValue) !== false ||
                           stripos($item['calidad'], $searchValue) !== false;
                });
            }
            
            $totalRecords = $movimientos->count();
            
            // Aplicar paginación
            $start = $request->get('start', 0);
            $length = $request->get('length', 50);
            $movimientosPaginados = $movimientos->slice($start, $length)->values();
            
            // Format data for better compatibility
            $formattedData = $movimientosPaginados->map(function($movimiento) {
                return [
                    'fecha' => is_string($movimiento['fecha']) ? $movimiento['fecha'] : $movimiento['fecha']->format('Y-m-d'),
                    'tipo' => $movimiento['tipo'],
                    'descripcion' => $movimiento['descripcion'],
                    'calidad' => $movimiento['calidad'],
                    'referencia' => $movimiento['referencia'],
                    'peso' => (float) $movimiento['peso'],
                    'costo_unitario' => (float) $movimiento['costo_unitario'],
                    'valor_total' => (float) $movimiento['valor_total']
                ];
            });
            
            $response = [
                'draw' => intval($request->get('draw', 1)),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $formattedData
            ];
            
            \Log::info('Returning DataTables response', [
                'draw' => $response['draw'],
                'recordsTotal' => $response['recordsTotal'],
                'data_count' => count($response['data'])
            ]);
            
            return response()->json($response);
            
            } catch (\Exception $e) {
                \Log::error('Error in acopio movimientos', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                
                return response()->json([
                    'draw' => intval($request->get('draw', 1)),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        return view('acopio.movimientos');
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

        $reporte = [
            'periodo' => [
                'inicio' => $fechaInicio,
                'fin' => $fechaFin
            ],
            'resumen' => $resumen,
            'ventas' => $ventasPorCalidad
        ];

        if ($request->wantsJson()) {
            return response()->json($reporte);
        }

        return view('acopio.reporte', compact('reporte'));
    }
}