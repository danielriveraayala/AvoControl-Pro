<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Lot;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        
        $query = Sale::with(['customer', 'saleItems.allocations.lot']);

        // Filters
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }
        
        if ($request->has('payment_status') && $request->payment_status !== '') {
            $query->where('payment_status', $request->payment_status);
        }
        
        if ($request->has('customer_id') && $request->customer_id !== '') {
            $query->where('customer_id', $request->customer_id);
        }
        
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        $sales = $query->latest('sale_date')->paginate(20);
        $customers = Customer::orderBy('name')->get();
        
        // Calcular inventario disponible por calidad
        $inventarioDisponible = DB::table('lots')
            ->join('quality_grades', 'lots.quality_grade_id', '=', 'quality_grades.id')
            ->where('lots.status', '!=', 'cancelled')
            ->select('quality_grades.name as quality_name')
            ->selectRaw('SUM(lots.total_weight) as peso_disponible')
            ->groupBy('quality_grades.name')
            ->pluck('peso_disponible', 'quality_name');
            
        // Calcular déficit por venta
        $deficitPorVenta = [];
        $alertasDeficit = [];
        
        foreach($sales as $sale) {
            $deficitVenta = [];
            foreach($sale->saleItems as $item) {
                $disponible = $inventarioDisponible->get($item->quality_grade, 0);
                $requerido = $item->weight;
                
                // Calcular cuánto ya está comprometido en otras ventas
                $comprometidoOtrasVentas = DB::table('sale_items')
                    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->where('sales.status', '!=', 'cancelled')
                    ->where('sale_items.quality_grade', $item->quality_grade)
                    ->where('sales.id', '!=', $sale->id)
                    ->sum('sale_items.weight');
                
                $disponibleReal = $disponible - $comprometidoOtrasVentas;
                $deficit = $requerido - $disponibleReal;
                
                if ($deficit > 0) {
                    $deficitVenta[] = [
                        'quality' => $item->quality_grade,
                        'requerido' => $requerido,
                        'disponible' => max(0, $disponibleReal),
                        'deficit' => $deficit
                    ];
                    
                    // Agregar a alertas generales
                    if (!isset($alertasDeficit[$item->quality_grade])) {
                        $alertasDeficit[$item->quality_grade] = [
                            'quality' => $item->quality_grade,
                            'deficit_total' => 0,
                            'disponible' => $disponible,
                            'ventas_afectadas' => 0
                        ];
                    }
                    $alertasDeficit[$item->quality_grade]['deficit_total'] += $deficit;
                    $alertasDeficit[$item->quality_grade]['ventas_afectadas']++;
                }
            }
            
            if (!empty($deficitVenta)) {
                $deficitPorVenta[$sale->id] = $deficitVenta;
            }
        }

        // Handle DataTables AJAX requests (only for GET requests)
        if ($request->ajax() && $request->isMethod('GET')) {
            try {
                $start = $request->input('start', 0);
                $length = $request->input('length', 10);
                $searchValue = $request->input('search.value', '');
                $orderColumn = $request->input('order.0.column', 0);
                $orderDir = $request->input('order.0.dir', 'desc');
                
                // Column mapping for ordering
                $columns = ['id', 'sale_code', 'customer.name', 'sale_date', 'total_weight', 'total_amount', 'status', 'payment_status'];
                $orderBy = $columns[$orderColumn] ?? 'id';
            
            // Base query with relationships
            $salesQuery = Sale::with(['customer', 'saleItems.allocations.lot']);
            
            // Apply filters from form
            if ($request->filled('status_filter')) {
                $salesQuery->where('sales.status', $request->status_filter);
            }
            
            if ($request->filled('payment_status_filter')) {
                $salesQuery->where('sales.payment_status', $request->payment_status_filter);
            }
            
            if ($request->filled('customer_id_filter')) {
                $salesQuery->where('customer_id', $request->customer_id_filter);
            }
            
            if ($request->filled('date_from_filter')) {
                $salesQuery->whereDate('sale_date', '>=', $request->date_from_filter);
            }
            
            if ($request->filled('date_to_filter')) {
                $salesQuery->whereDate('sale_date', '<=', $request->date_to_filter);
            }
            
            // Apply search
            if ($searchValue) {
                $salesQuery->where(function ($query) use ($searchValue) {
                    $query->where('sale_code', 'like', "%{$searchValue}%")
                          ->orWhere('invoice_number', 'like', "%{$searchValue}%")
                          ->orWhereHas('customer', function ($q) use ($searchValue) {
                              $q->where('name', 'like', "%{$searchValue}%");
                          });
                });
            }
            
            // Count total and filtered records
            $totalRecords = Sale::count();
            $filteredRecords = $salesQuery->count();
            
            // Apply ordering and pagination
            if (strpos($orderBy, '.') !== false) {
                // Handle relationship ordering
                if ($orderBy === 'customer.name') {
                    $salesQuery->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
                              ->orderBy('customers.name', $orderDir)
                              ->select('sales.*');
                }
            } else {
                $salesQuery->orderBy('sales.' . $orderBy, $orderDir);
            }
            
            $sales = $salesQuery->skip($start)->take($length)->get();
            
            // Transform data for DataTables
            $data = $sales->map(function ($sale) {
                try {
                    return [
                        'id' => $sale->id,
                        'sale_code' => $sale->sale_code ?? $sale->invoice_number ?? 'N/A',
                        'invoice_number' => $sale->invoice_number,
                        'customer' => $sale->customer ? $sale->customer->name : 'Cliente eliminado',
                        'customer_type' => $sale->customer ? $sale->customer->customer_type : null,
                        'sale_date' => $sale->sale_date ? $sale->sale_date->format('d/m/Y') : 'N/A',
                        'sale_date_human' => $sale->sale_date ? $sale->sale_date->diffForHumans() : 'N/A',
                        'delivery_date' => $sale->delivery_date ? $sale->delivery_date->format('d/m/Y') : null,
                        'total_weight' => number_format($sale->total_weight ?? 0, 2),
                        'total_amount' => number_format($sale->total_amount ?? 0, 2),
                        'avg_price' => ($sale->total_weight ?? 0) > 0 ? number_format(($sale->total_amount ?? 0) / $sale->total_weight, 2) : '0.00',
                        'status' => $sale->status ?? 'draft',
                        'payment_status' => $sale->payment_status ?? 'pending',
                        'items_count' => $sale->saleItems ? $sale->saleItems->count() : 0,
                        'can_edit' => ($sale->status ?? 'draft') === 'draft',
                        'can_delete' => ($sale->status ?? 'draft') === 'draft', // Simplified for now
                    ];
                } catch (\Exception $e) {
                    \Log::error('Error transforming sale data', [
                        'sale_id' => $sale->id ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                    
                    return [
                        'id' => $sale->id ?? 0,
                        'sale_code' => 'ERROR',
                        'customer' => 'Error loading',
                        'sale_date' => 'N/A',
                        'total_weight' => '0.00',
                        'total_amount' => '0.00',
                        'status' => 'error',
                        'payment_status' => 'error'
                    ];
                }
            });
            
            // Calculate stats for the current filtered data
            $statsQuery = Sale::query();
            
            // Apply same filters to stats
            if ($request->filled('status_filter')) {
                $statsQuery->where('sales.status', $request->status_filter);
            }
            if ($request->filled('payment_status_filter')) {
                $statsQuery->where('sales.payment_status', $request->payment_status_filter);
            }
            if ($request->filled('customer_id_filter')) {
                $statsQuery->where('customer_id', $request->customer_id_filter);
            }
            if ($request->filled('date_from_filter')) {
                $statsQuery->whereDate('sale_date', '>=', $request->date_from_filter);
            }
            if ($request->filled('date_to_filter')) {
                $statsQuery->whereDate('sale_date', '<=', $request->date_to_filter);
            }
            
            $stats = [
                'total' => $statsQuery->count(),
                'value' => number_format($statsQuery->sum('total_amount'), 0),
                'weight' => number_format($statsQuery->sum('total_weight'), 0),
                'pending' => $statsQuery->whereIn('sales.payment_status', ['pending', 'partial'])->count()
            ];

                $response = [
                    'draw' => intval($request->input('draw')),
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $filteredRecords,
                    'data' => $data,
                    'stats' => $stats
                ];

                return response()->json($response);
            } catch (\Exception $e) {
                \Log::error('DataTables Error', [
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ]);

                return response()->json([
                    'draw' => intval($request->input('draw')),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => $e->getMessage()
                ]);
            }
        }

        return view('sales.index', compact('sales', 'customers', 'deficitPorVenta', 'alertasDeficit'));
    }

    public function create(Request $request)
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        
        // Obtener inventario agrupado por calidad
        $inventario = Lot::with(['qualityGrade'])
            ->select('quality_grade_id')
            ->selectRaw('SUM(weight_available) as peso_disponible')
            ->selectRaw('AVG(purchase_price_per_kg) as precio_promedio')
            ->selectRaw('COUNT(*) as total_lotes')
            ->where('status', '!=', 'sold')
            ->where('weight_available', '>', 0)
            ->where('quality_grade_id', '!=', null)
            ->groupBy('quality_grade_id')
            ->orderBy('quality_grade_id')
            ->get();

        // Handle AJAX requests for modal
        if ($request->ajax()) {
            $inventarioFormatted = $inventario->mapWithKeys(function($item) {
                $qualityName = $item->qualityGrade ? $item->qualityGrade->name : 'Sin calidad';
                $qualityColor = $item->qualityGrade ? $item->qualityGrade->color : '#6c757d';
                return [$qualityName => [
                    'quality_grade' => $qualityName,
                    'quality_color' => $qualityColor,
                    'peso_disponible' => $item->peso_disponible,
                    'precio_promedio' => $item->precio_promedio,
                    'total_lotes' => $item->total_lotes,
                    'quality_grade_id' => $item->quality_grade_id
                ]];
            });
            
            return response()->json([
                'inventario' => $inventarioFormatted,
                'customers' => $customers
            ]);
        }
            
        return view('sales.create', compact('customers', 'inventario'));
    }

    public function store(Request $request)
    {
        try {
            // Obtener calidades válidas de la base de datos
            $validQualityNames = \App\Models\QualityGrade::where('active', true)
                ->pluck('name')
                ->toArray();
            
            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'sale_date' => 'required|date',
                'delivery_date' => 'nullable|date|after_or_equal:sale_date',
                'invoice_number' => 'nullable|string|unique:sales,invoice_number',
                'notes' => 'nullable|string|max:1000',
                'items' => 'required|array|min:1',
                'items.*.quality_grade' => 'required|string|in:' . implode(',', $validQualityNames),
                'items.*.weight' => 'required|numeric|min:0.01',
                'items.*.price_per_kg' => 'required|numeric|min:0.01',
                'items.*.subtotal' => 'required|numeric|min:0.01'
            ]);

        $sale = DB::transaction(function () use ($validated, $request) {
            // Generar código de venta
            $lastSale = Sale::whereDate('created_at', today())->count();
            $saleCode = 'VTA-' . date('Ymd') . '-' . str_pad($lastSale + 1, 3, '0', STR_PAD_LEFT);

            // Calcular totales
            $totalWeight = 0;
            $totalAmount = 0;

            foreach ($validated['items'] as $item) {
                $totalWeight += $item['weight'];
                $totalAmount += $item['subtotal'];
            }

            // Crear venta
            $sale = Sale::create([
                'sale_code' => $saleCode,
                'customer_id' => $validated['customer_id'],
                'created_by' => auth()->id() ?? 1, // Fallback al usuario admin si no hay auth
                'sale_date' => $validated['sale_date'],
                'delivery_date' => $validated['delivery_date'] ?? null,
                'invoice_number' => $validated['invoice_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'total_weight' => $totalWeight,
                'total_amount' => $totalAmount,
                'status' => 'draft',
                'payment_status' => 'pending'
            ]);

            // Crear items de venta y asignar automáticamente a lotes
            foreach ($validated['items'] as $item) {
                // Verificar inventario disponible
                $qualityGradeRecord = \App\Models\QualityGrade::where('name', $item['quality_grade'])->first();
                if (!$qualityGradeRecord) {
                    throw new \Exception("Calidad '{$item['quality_grade']}' no encontrada");
                }
                
                $availableWeight = Lot::where('quality_grade_id', $qualityGradeRecord->id)
                    ->where('weight_available', '>', 0)
                    ->sum('weight_available');
                    
                if ($item['weight'] > $availableWeight) {
                    throw new \Exception("Inventario insuficiente para calidad {$item['quality_grade']}. Disponible: {$availableWeight} kg, Solicitado: {$item['weight']} kg");
                }

                // Crear sale item (la asignación automática se hace en el modelo)
                $saleItem = SaleItem::create([
                    'sale_id' => $sale->id,
                    'quality_grade' => $item['quality_grade'],
                    'weight' => $item['weight'],
                    'price_per_kg' => $item['price_per_kg'],
                    'subtotal' => $item['subtotal']
                ]);
                
                // El modelo SaleItem automáticamente:
                // 1. Busca lotes de la calidad especificada con inventario disponible
                // 2. Los asigna usando FIFO (First In, First Out)  
                // 3. Actualiza weight_sold y weight_available de cada lote
                // 4. Crea registros en sale_lot_allocations para trazabilidad
            }

            // Actualizar balance del cliente
            $customer = Customer::find($validated['customer_id']);
            if ($customer->current_balance !== null) {
                $customer->current_balance += $totalAmount;
                $customer->save();
            }

            return $sale;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Venta registrada exitosamente',
                'sale' => [
                    'id' => $sale->id,
                    'sale_code' => $sale->sale_code,
                    'total_amount' => $sale->total_amount,
                    'total_weight' => $sale->total_weight
                ]
            ]);
        }

        return redirect()->route('sales.index')
            ->with('success', 'Venta registrada exitosamente');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error en SaleController@store: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Error al registrar la venta: ' . $e->getMessage())->withInput();
        }
    }

    // public function show(Sale $sale)
    // {
    //     // Removed - using AJAX-only approach in index view
    // }

    public function edit(Sale $sale)
    {
        // Only allow editing if sale is draft
        if ($sale->status !== 'draft') {
            return redirect()->back()
                ->with('error', 'Solo se pueden editar ventas en borrador');
        }

        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $lots = Lot::where('status', '!=', 'sold')
            ->orWhereIn('id', $sale->saleItems->pluck('lot_id'))
            ->with('supplier')
            ->get();

        return view('sales.edit', compact('sale', 'customers', 'lots'));
    }

    public function update(Request $request, Sale $sale)
    {
        // Only allow editing if sale is draft
        if ($sale->status !== 'draft') {
            return redirect()->back()
                ->with('error', 'Solo se pueden editar ventas en borrador');
        }

        $validated = $request->validate([
            'sale_date' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:sale_date',
            'invoice_number' => 'nullable|string|unique:sales,invoice_number,' . $sale->id,
            'status' => 'required|in:draft,confirmed,shipped,delivered,cancelled'
        ]);

        $sale->update($validated);

        return redirect()->route('sales.index')
            ->with('success', 'Venta actualizada exitosamente');
    }

    public function destroy(Request $request, Sale $sale)
    {
        // Only allow deletion if sale is draft and has no payments
        if ($sale->status !== 'draft' || $sale->payments()->count() > 0) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar esta venta. Debe estar en borrador y sin pagos.'
                ], 400);
            }
            return redirect()->back()
                ->with('error', 'No se puede eliminar esta venta');
        }

        try {
            DB::transaction(function () use ($sale) {
                // Restore lot quantities
                foreach ($sale->saleItems as $item) {
                    // Revert allocations instead of direct lot access
                    $item->revertAllocations();
                }

                // Update customer balance
                if ($sale->customer && $sale->customer->current_balance !== null) {
                    $sale->customer->current_balance -= $sale->total_amount;
                    $sale->customer->save();
                }

                // Delete sale items and sale
                $sale->saleItems()->delete();
                $sale->delete();
            });

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Venta eliminada exitosamente'
                ]);
            }

            return redirect()->route('sales.index')
                ->with('success', 'Venta eliminada exitosamente');
        } catch (\Exception $e) {
            \Log::error('Error deleting sale', [
                'sale_id' => $sale->id,
                'error' => $e->getMessage()
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar la venta: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Error al eliminar la venta');
        }
    }

    public function markDelivered(Sale $sale)
    {
        $sale->update([
            'status' => 'delivered',
            'delivery_date' => now()
        ]);

        return redirect()->back()
            ->with('success', 'Venta marcada como entregada');
    }

    // ========================================
    // MODAL METHODS FOR SALES MANAGEMENT
    // ========================================
    
    public function details(Sale $sale)
    {
        try {
            $sale->load(['customer', 'saleItems.allocations.lot.supplier', 'payments']);
            
            $html = view('sales.partials.details', compact('sale'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in sales details', ['error' => $e->getMessage(), 'sale_id' => $sale->id]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar detalles: ' . $e->getMessage()
            ], 500);
        }
    }

    public function editModal(Sale $sale)
    {
        try {
            if ($sale->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden editar ventas en borrador'
                ], 400);
            }

            $sale->load(['customer', 'saleItems']);
            $customers = Customer::where('status', 'active')->orderBy('name')->get();
            
            $html = view('sales.partials.edit-form', compact('sale', 'customers'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in sales edit modal', ['error' => $e->getMessage(), 'sale_id' => $sale->id]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar formulario: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateModal(Request $request, Sale $sale)
    {
        try {
            if ($sale->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden editar ventas en borrador'
                ], 400);
            }

            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'sale_date' => 'required|date',
                'delivery_date' => 'nullable|date|after_or_equal:sale_date',
                'invoice_number' => 'nullable|string|unique:sales,invoice_number,' . $sale->id,
                'notes' => 'nullable|string|max:1000'
            ]);

            $sale->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Venta actualizada exitosamente'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating sale', ['error' => $e->getMessage(), 'sale_id' => $sale->id]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, Sale $sale)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:confirmed,delivered,cancelled'
            ]);

            $oldStatus = $sale->status;
            $newStatus = $validated['status'];

            DB::transaction(function () use ($sale, $newStatus, $oldStatus) {
                if ($newStatus === 'cancelled' && in_array($oldStatus, ['draft', 'confirmed'])) {
                    // Restore inventory when cancelling
                    foreach ($sale->saleItems as $item) {
                        $item->revertAllocations();
                    }
                }

                if ($newStatus === 'delivered') {
                    $sale->delivery_date = now();
                }

                $sale->status = $newStatus;
                $sale->save();
            });

            $statusMessages = [
                'confirmed' => 'Venta confirmada exitosamente',
                'delivered' => 'Venta marcada como entregada',
                'cancelled' => 'Venta cancelada exitosamente'
            ];

            return response()->json([
                'success' => true,
                'message' => $statusMessages[$newStatus]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating sale status', ['error' => $e->getMessage(), 'sale_id' => $sale->id]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estado: ' . $e->getMessage()
            ], 500);
        }
    }

    public function paymentForm(Sale $sale)
    {
        try {
            $sale->load(['customer', 'payments']);
            
            $totalPaid = $sale->payments->sum('amount');
            $remainingBalance = $sale->total_amount - $totalPaid;
            
            $html = view('sales.partials.payment-form', compact('sale', 'remainingBalance'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in payment form', ['error' => $e->getMessage(), 'sale_id' => $sale->id]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar formulario: ' . $e->getMessage()
            ], 500);
        }
    }

    public function invoice(Sale $sale)
    {
        try {
            $sale->load(['customer', 'saleItems.allocations.lot.supplier', 'payments', 'createdBy']);
            
            $pdf = Pdf::loadView('sales.invoice', compact('sale'));
            
            return $pdf->stream("factura-{$sale->sale_code}.pdf");
        } catch (\Exception $e) {
            \Log::error('Error generating invoice', ['error' => $e->getMessage(), 'sale_id' => $sale->id]);
            
            return redirect()->back()->with('error', 'Error al generar factura: ' . $e->getMessage());
        }
    }

    public function paymentTimeline(Sale $sale)
    {
        try {
            $sale->load(['customer', 'payments.createdBy']);
            
            $html = view('sales.partials.payment-timeline', compact('sale'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in payment timeline', ['error' => $e->getMessage(), 'sale_id' => $sale->id]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar timeline: ' . $e->getMessage()
            ], 500);
        }
    }
}