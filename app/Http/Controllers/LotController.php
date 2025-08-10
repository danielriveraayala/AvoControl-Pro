<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use App\Models\Supplier;
use App\Models\QualityGrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LotController extends Controller
{
    public function index(Request $request)
    {
        $query = Lot::with(['supplier', 'saleAllocations']);

        // Filters - Only apply if values are not null/empty
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('quality')) {
            $qualityGradeRecord = \App\Models\QualityGrade::where('name', $request->quality)->first();
            if ($qualityGradeRecord) {
                $query->where('quality_grade_id', $qualityGradeRecord->id);
            }
        }
        
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('harvest_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('harvest_date', '<=', $request->date_to);
        }

        // DataTables server-side processing will handle sorting

        // Handle DataTables requests
        if ($request->has('draw')) {
            $totalData = Lot::count();
            $totalFiltered = $query->count();
            
            // Apply search if exists
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->where(function($q) use ($search) {
                    $q->where('lot_code', 'LIKE', "%{$search}%")
                      ->orWhereHas('supplier', function($sq) use ($search) {
                          $sq->where('name', 'LIKE', "%{$search}%");
                      })
                      ->orWhereHas('qualityGrade', function($qg) use ($search) {
                          $qg->where('name', 'LIKE', "%{$search}%");
                      });
                });
                $totalFiltered = $query->count();
            }
            
            // Apply ordering
            if ($request->has('order')) {
                // Updated columns with payment_status column
                $columns = ['supplier.name', 'harvest_date', 'total_weight', 
                           'quality_grade', 'purchase_price_per_kg', 'total_purchase_cost', 'payment_status', 'id'];
                $orderColumnIndex = $request->order[0]['column'] ?? 1;
                $orderDir = $request->order[0]['dir'] ?? 'desc';
                
                if ($orderColumnIndex == 0) { // Supplier column
                    $query->leftJoin('suppliers', 'lots.supplier_id', '=', 'suppliers.id')
                          ->select('lots.*')
                          ->orderByRaw('suppliers.name IS NULL, suppliers.name ' . $orderDir);
                } else {
                    $orderColumn = ['supplier_id', 'harvest_date', 'total_weight', 
                                   'quality_grade', 'purchase_price_per_kg', 'total_purchase_cost', 'payment_status', 'id'][$orderColumnIndex] ?? 'harvest_date';
                    $query->orderBy($orderColumn, $orderDir);
                }
            } else {
                $query->latest('harvest_date');
            }
            
            // Apply pagination
            $start = $request->start ?? 0;
            $length = $request->length ?? 10;
            $lots = $query->skip($start)->take($length)->get();
            
            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $totalData,
                'recordsFiltered' => $totalFiltered,
                'data' => $lots
            ]);
        }

        // Create a base query for stats before pagination
        $statsQuery = clone $query;
        
        // Debug: Let's see the final query before pagination
        try {
            $testCount = $query->count();
            \Log::info('Query count before pagination', ['count' => $testCount]);
        } catch (\Exception $e) {
            \Log::error('Query count error', ['error' => $e->getMessage()]);
        }
        
        // Handle pagination
        $perPage = $request->get('per_page', 20);
        if ($perPage === 'all') {
            $lots = $query->get();
            // Create a fake paginator for consistency
            $lots = new \Illuminate\Pagination\LengthAwarePaginator(
                $lots,
                $lots->count(),
                $lots->count(),
                1,
                ['path' => request()->url(), 'pageName' => 'page']
            );
        } else {
            $lots = $query->paginate((int)$perPage);
        }
        
        $suppliers = Supplier::orderBy('name')->get();
        $qualityGrades = QualityGrade::active()->ordered()->get();

        // Calculate comprehensive stats
        $totalLots = $statsQuery->count();
        $availableWeight = $statsQuery->sum('weight_available') ?: 0;
        $soldWeight = $statsQuery->sum('weight_sold') ?: 0;
        $totalInvestment = $statsQuery->sum('total_purchase_cost') ?: 0;
        $pendingDebt = $statsQuery->sum('amount_owed') ?: 0;
        $avgPurchasePrice = $totalLots > 0 ? $statsQuery->avg('purchase_price_per_kg') : 0;
        
        // Calculate stats by quality
        $qualityBreakdown = [];
        $qualityStats = Lot::with(['qualityGrade'])
            ->select('quality_grade_id')
            ->selectRaw('COUNT(*) as lots')
            ->selectRaw('SUM(total_weight) as total_kg')
            ->selectRaw('AVG(purchase_price_per_kg) as avg_price')
            ->selectRaw('SUM(total_purchase_cost) as total_value')
            ->where('quality_grade_id', '!=', null);
            
        // Apply same filters to quality stats
        if ($request->filled('status')) {
            $qualityStats->where('status', $request->status);
        }
        if ($request->filled('quality')) {
            $qualityGradeRecord = \App\Models\QualityGrade::where('name', $request->quality)->first();
            if ($qualityGradeRecord) {
                $qualityStats->where('quality_grade_id', $qualityGradeRecord->id);
            }
        }
        if ($request->filled('supplier_id')) {
            $qualityStats->where('supplier_id', $request->supplier_id);
        }
        if ($request->has('date_from') && $request->date_from) {
            $qualityStats->whereDate('harvest_date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $qualityStats->whereDate('harvest_date', '<=', $request->date_to);
        }
        
        $qualityStats = $qualityStats->groupBy('quality_grade_id')->get();
        
        foreach ($qualityStats as $stat) {
            if ($stat->qualityGrade) {
                $qualityBreakdown[] = [
                    'quality_name' => $stat->qualityGrade->name,
                    'lots' => $stat->lots,
                    'total_kg' => $stat->total_kg ?: 0,
                    'avg_price' => $stat->avg_price ?: 0,
                    'total_value' => $stat->total_value ?: 0
                ];
            }
        }
        
        $stats = [
            'total' => $totalLots,
            'available_weight' => $availableWeight,
            'sold_weight' => $soldWeight,
            'avg_purchase_price' => $avgPurchasePrice,
            'total_investment' => $totalInvestment,
            'pending_debt' => $pendingDebt,
            'quality_breakdown' => $qualityBreakdown
        ];

        // Handle AJAX requests (non-DataTables)
        if ($request->ajax() || $request->wantsJson()) {
            try {
                \Log::info('AJAX Response Data', [
                    'lots_count' => $lots->count(),
                    'stats' => $stats,
                    'query_sql' => $query->toSql()
                ]);
                
                // Check if it's a stats-only request
                if ($request->has('ajax') && $request->get('ajax') == '1') {
                    return response()->json([
                        'stats' => $stats,
                        'success' => true
                    ]);
                }
                
                $html = view('lots.partials.table', compact('lots'))->render();
                return response()->json([
                    'html' => $html,
                    'stats' => $stats,
                    'success' => true,
                    'debug' => [
                        'lots_count' => $lots->count()
                    ]
                ]);
            } catch (\Exception $e) {
                \Log::error('Error rendering lots table: ' . $e->getMessage());
                \Log::error('Error stack trace: ' . $e->getTraceAsString());
                return response()->json([
                    'error' => $e->getMessage(),
                    'success' => false
                ], 500);
            }
        }

        return view('lots.index', compact('lots', 'suppliers', 'qualityGrades', 'stats'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $qualityGrades = QualityGrade::active()->ordered()->get();
        return view('lots.create', compact('suppliers', 'qualityGrades'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|unique:lots,lot_code',
            'fecha_compra' => 'required|date',
            'peso_inicial' => 'required|numeric|min:0.01|max:9999999',
            'precio_compra' => 'required|numeric|min:0.01|max:99999',
            'calidad' => 'required|exists:quality_grades,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'notas' => 'nullable|string|max:1000',
            'monto_pago' => 'nullable|numeric|min:0|max:99999999999',
            'fecha_pago' => 'nullable|date',
            'tipo_pago' => 'nullable|in:efectivo,transferencia,cheque,deposito,otro',
            'notas_pago' => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($validated, $request) {
                $totalCost = $validated['peso_inicial'] * $validated['precio_compra'];
                $amountPaid = $validated['monto_pago'] ?? 0;
                $amountOwed = $totalCost - $amountPaid;
                
                // Determine payment status
                $paymentStatus = 'pending';
                if ($amountOwed <= 0) {
                    $paymentStatus = 'paid';
                } elseif ($amountPaid > 0) {
                    $paymentStatus = 'partial';
                }
                
                // Get quality grade name for backward compatibility
                $qualityGrade = QualityGrade::find($validated['calidad']);
                
                $lot = Lot::create([
                    'lot_code' => $validated['codigo'],
                    'harvest_date' => $validated['fecha_compra'],
                    'entry_date' => now(),
                    'total_weight' => $validated['peso_inicial'],
                    'purchase_price_per_kg' => $validated['precio_compra'],
                    'quality_grade' => $qualityGrade->name, // Keep for backward compatibility
                    'quality_grade_id' => $validated['calidad'], // Use ID for relationship
                    'supplier_id' => $validated['supplier_id'],
                    'status' => 'active',
                    'weight_sold' => 0,
                    'weight_available' => $validated['peso_inicial'],
                    'total_purchase_cost' => $totalCost,
                    'amount_paid' => $amountPaid,
                    'amount_owed' => $amountOwed,
                    'payment_status' => $paymentStatus,
                    'notes' => $validated['notas']
                ]);

                // Create initial payment record if payment was made
                if ($amountPaid > 0 && $validated['fecha_pago']) {
                    \App\Models\LotPayment::create([
                        'lot_id' => $lot->id,
                        'amount' => $amountPaid,
                        'payment_date' => $validated['fecha_pago'],
                        'payment_type' => $validated['tipo_pago'] ?? 'efectivo',
                        'paid_by_user_id' => auth()->id(),
                        'notes' => $validated['notas_pago']
                    ]);
                }

                // Update supplier totals only if supplier exists
                if ($validated['supplier_id']) {
                    $supplier = Supplier::find($validated['supplier_id']);
                    $supplier->total_purchased += $lot->total_purchase_cost;
                    // Only add amount owed to balance, not the full cost if partially paid
                    $supplier->balance_owed += $amountOwed;
                    $supplier->save();
                }
            });

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Lote creado exitosamente']);
            }

            return redirect()->route('lots.index')->with('success', 'Lote registrado exitosamente');
        } catch (\Exception $e) {
            \Log::error('Error creating lot: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Error al crear el lote: ' . $e->getMessage()
                ], 422);
            }
            return back()->with('error', 'Error al registrar el lote: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Request $request, Lot $lot)
    {
        $lot->load(['supplier', 'saleAllocations.saleItem.sale.customer', 'payments']);
        
        if ($request->wantsJson()) {
            // Get latest payment for pre-filling form
            $latestPayment = $lot->payments->sortByDesc('payment_date')->first();
            
            $response = [
                'id' => $lot->id,
                'lot_code' => $lot->lot_code,
                'supplier_id' => $lot->supplier_id,
                'codigo' => $lot->lot_code,
                'peso_inicial' => $lot->total_weight,
                'precio_compra' => $lot->purchase_price_per_kg,
                'calidad' => $lot->quality_grade,
                'quality_grade_id' => $lot->quality_grade_id, // Add ID for form
                'notas' => $lot->notes ?? '',
                'fecha_compra' => $lot->harvest_date->format('Y-m-d'),
                // Payment fields
                'amount_paid' => $lot->amount_paid,
                'amount_owed' => $lot->amount_owed,
                'payment_status' => $lot->payment_status,
                'latest_payment' => $latestPayment ? [
                    'payment_date' => $latestPayment->payment_date->format('Y-m-d'),
                    'payment_type' => $latestPayment->payment_type,
                    'notes' => $latestPayment->notes
                ] : null
            ];
            
            \Log::info('Response data', $response);
            
            return response()->json($response);
        }

        return view('lots.show', compact('lot'));
    }

    public function edit(Lot $lot)
    {
        $suppliers = Supplier::orderBy('name')->get();
        return view('lots.edit', compact('lot', 'suppliers'));
    }

    public function update(Request $request, Lot $lot)
    {
        // No restrictions for editing in acopio model
        // Lots contribute to aggregated inventory, not individual sales
        
        $validated = $request->validate([
            'codigo' => 'required|string|unique:lots,lot_code,' . $lot->id,
            'fecha_compra' => 'required|date',
            'peso_inicial' => 'required|numeric|min:0.01|max:9999999',
            'precio_compra' => 'required|numeric|min:0.01|max:99999',
            'calidad' => 'required|exists:quality_grades,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'notas' => 'nullable|string|max:1000',
            'monto_pago' => 'nullable|numeric|min:0|max:99999999999',
            'fecha_pago' => 'nullable|date',
            'tipo_pago' => 'nullable|in:efectivo,transferencia,cheque,deposito,otro',
            'notas_pago' => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($lot, $validated, $request) {
                // Update supplier balance with difference only if supplier exists
                $oldCost = $lot->total_purchase_cost;
                $oldOwed = $lot->amount_owed;
                $newCost = $validated['peso_inicial'] * $validated['precio_compra'];
                $newPaid = $validated['monto_pago'] ?? $lot->amount_paid;
                $newOwed = $newCost - $newPaid;
                $costDifference = $newCost - $oldCost;
                $owedDifference = $newOwed - $oldOwed;

                // Determine payment status
                $paymentStatus = 'pending';
                if ($newOwed <= 0) {
                    $paymentStatus = 'paid';
                } elseif ($newPaid > 0) {
                    $paymentStatus = 'partial';
                }

                if ($lot->supplier_id && $validated['supplier_id']) {
                    // Update existing supplier
                    $lot->supplier->balance_owed += $owedDifference;
                    $lot->supplier->total_purchased += $costDifference;
                    $lot->supplier->save();
                } elseif ($lot->supplier_id && !$validated['supplier_id']) {
                    // Remove from old supplier
                    $lot->supplier->balance_owed -= $oldOwed;
                    $lot->supplier->total_purchased -= $oldCost;
                    $lot->supplier->save();
                } elseif (!$lot->supplier_id && $validated['supplier_id']) {
                    // Add to new supplier
                    $supplier = Supplier::find($validated['supplier_id']);
                    $supplier->balance_owed += $newOwed;
                    $supplier->total_purchased += $newCost;
                    $supplier->save();
                }

                // Get quality grade name for backward compatibility
                $qualityGrade = QualityGrade::find($validated['calidad']);
                
                // Update lot
                $lot->update([
                    'lot_code' => $validated['codigo'],
                    'harvest_date' => $validated['fecha_compra'],
                    'entry_date' => now(),
                    'total_weight' => $validated['peso_inicial'],
                    'purchase_price_per_kg' => $validated['precio_compra'],
                    'quality_grade' => $qualityGrade->name, // Keep for backward compatibility
                    'quality_grade_id' => $validated['calidad'], // Use ID for relationship
                    'supplier_id' => $validated['supplier_id'],
                    'weight_available' => $validated['peso_inicial'] - $lot->weight_sold,
                    'total_purchase_cost' => $newCost,
                    'amount_paid' => $newPaid,
                    'amount_owed' => $newOwed,
                    'payment_status' => $paymentStatus,
                    'notes' => $validated['notas']
                ]);

                // Create additional payment record if new payment was made
                if (isset($validated['monto_pago']) && $validated['monto_pago'] > 0 && 
                    $validated['fecha_pago'] && $validated['monto_pago'] > $lot->amount_paid) {
                    $additionalPayment = $validated['monto_pago'] - $lot->amount_paid;
                    \App\Models\LotPayment::create([
                        'lot_id' => $lot->id,
                        'amount' => $additionalPayment,
                        'payment_date' => $validated['fecha_pago'],
                        'payment_type' => $validated['tipo_pago'] ?? 'efectivo',
                        'paid_by_user_id' => auth()->id(),
                        'notes' => $validated['notas_pago']
                    ]);
                }
            });

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Lote actualizado exitosamente']);
            }

            return redirect()->route('lots.show', $lot)->with('success', 'Lote actualizado exitosamente');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Error al actualizar el lote'], 422);
            }
            return back()->with('error', 'Error al actualizar el lote')->withInput();
        }
    }

    public function destroy(Request $request, Lot $lot)
    {
        try {
            // Verificar si eliminar este lote causará déficit
            if ($lot->quality_grade_id) {
                // Obtener inventario actual de esta calidad
                $inventarioActual = Lot::where('quality_grade_id', $lot->quality_grade_id)
                    ->where('status', '!=', 'cancelled')
                    ->sum('total_weight');
                    
                // Obtener ventas comprometidas de esta calidad
                $qualityName = $lot->qualityGrade->name;
                $ventasComprometidas = DB::table('sale_items')
                    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->where('sales.status', '!=', 'cancelled')
                    ->where('sale_items.quality_name', $qualityName)
                    ->sum('sale_items.quantity');
                    
                // Calcular nuevo balance después de eliminar
                $nuevoInventario = $inventarioActual - $lot->total_weight;
                $deficit = $ventasComprometidas - $nuevoInventario;
                
                // Si habrá déficit, advertir pero permitir continuar
                if ($deficit > 0) {
                    \Log::warning("Eliminación de lote causará déficit", [
                        'lot_id' => $lot->id,
                        'quality' => $qualityName,
                        'deficit' => $deficit,
                        'inventario_actual' => $inventarioActual,
                        'nuevo_inventario' => $nuevoInventario,
                        'ventas_comprometidas' => $ventasComprometidas
                    ]);
                    
                    // Opcionalmente, podríamos retornar una advertencia
                    if ($request->wantsJson() && !$request->has('force')) {
                        return response()->json([
                            'warning' => true,
                            'message' => "¡Advertencia! Eliminar este lote causará un déficit de {$deficit} kg en {$qualityName}. ¿Desea continuar?",
                            'deficit' => $deficit,
                            'quality' => $qualityName
                        ], 200);
                    }
                }
            }
            
            DB::transaction(function () use ($lot) {
                // Delete any sale allocations related to this lot (cleanup)
                if ($lot->saleAllocations) {
                    $lot->saleAllocations()->delete();
                }
                
                // Delete any lot payments
                if ($lot->payments) {
                    $lot->payments()->delete();
                }
                
                // Update supplier balance only if supplier exists
                if ($lot->supplier) {
                    // Only adjust if there's an amount owed
                    if ($lot->amount_owed > 0) {
                        $lot->supplier->balance_owed -= $lot->amount_owed;
                    }
                    $lot->supplier->total_purchased -= $lot->total_purchase_cost;
                    $lot->supplier->save();
                }

                // Delete the lot
                $lot->delete();
            });

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Lote eliminado exitosamente']);
            }

            return redirect()->route('lots.index')->with('success', 'Lote eliminado exitosamente');
        } catch (\Exception $e) {
            \Log::error('Error deleting lot: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Error al eliminar el lote: ' . $e->getMessage()], 422);
            }
            return back()->with('error', 'Error al eliminar el lote: ' . $e->getMessage());
        }
    }

    public function report(Request $request, Lot $lot)
    {
        // Ensure payment amounts are up to date before showing report
        $lot->updatePaymentAmounts();
        
        // Reload the lot with fresh data and relationships
        $lot = $lot->fresh(['supplier', 'payments.createdBy', 'saleAllocations']);

        if ($request->wantsJson()) {
            try {
                $html = view('lots.partials.report', compact('lot'))->render();
                \Log::info('Report HTML generated successfully', [
                    'lot_id' => $lot->id, 
                    'html_length' => strlen($html),
                    'amount_paid' => $lot->amount_paid,
                    'amount_owed' => $lot->amount_owed,
                    'payments_count' => $lot->payments->count()
                ]);
                return response()->json(['html' => $html, 'success' => true]);
            } catch (\Exception $e) {
                \Log::error('Error generating report HTML: ' . $e->getMessage(), ['lot_id' => $lot->id]);
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        return view('lots.report', compact('lot'));
    }

    public function downloadPDF(Lot $lot)
    {
        // Ensure payment amounts are up to date before generating PDF
        $lot->updatePaymentAmounts();
        
        // Reload with fresh data
        $lot = $lot->fresh(['supplier', 'payments.createdBy', 'saleAllocations']);

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('lots.pdf.report', compact('lot'));
        
        return $pdf->download("lote_{$lot->lot_code}_reporte.pdf");
    }

    public function payments(Request $request, Lot $lot)
    {
        // Load only polymorphic payment system
        $lot->load(['payments.createdBy', 'supplier']);
        
        if ($request->wantsJson()) {
            // Use only polymorphic payments
            $allPayments = collect();
            
            // Add polymorphic payments
            foreach($lot->payments as $payment) {
                $allPayments->push([
                    'id' => $payment->id,
                    'type' => 'polymorphic',
                    'amount' => $payment->amount,
                    'payment_date' => $payment->payment_date->format('Y-m-d'),
                    'payment_method' => $payment->payment_method,
                    'notes' => $payment->notes,
                    'paid_by' => $payment->createdBy ? $payment->createdBy->name : 'Sistema',
                    'created_at' => $payment->created_at->format('d/m/Y H:i'),
                    'reference' => $payment->reference
                ]);
            }
            
            // Sort by payment date, then by created_at
            $allPayments = $allPayments->sortBy([
                ['payment_date', 'asc'],
                ['created_at', 'asc']
            ])->values();
            
            // Calculate totals from polymorphic system only
            $totalPaid = $lot->payments->sum('amount');
            $remainingBalance = $lot->total_purchase_cost - $totalPaid;
            
            return response()->json([
                'lot' => [
                    'id' => $lot->id,
                    'lot_code' => $lot->lot_code,
                    'total_purchase_cost' => $lot->total_purchase_cost,
                    'amount_paid' => $totalPaid,
                    'amount_owed' => $remainingBalance,
                    'payment_status' => $remainingBalance <= 0 ? 'paid' : ($totalPaid > 0 ? 'partial' : 'pending'),
                    'supplier_name' => $lot->supplier ? $lot->supplier->name : 'Sin proveedor'
                ],
                'payments' => $allPayments
            ]);
        }
    }

    public function addPayment(Request $request, Lot $lot)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_type' => 'required|in:efectivo,transferencia,cheque,deposito,otro',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::transaction(function () use ($lot, $validated) {
                // Create payment record
                \App\Models\LotPayment::create([
                    'lot_id' => $lot->id,
                    'amount' => $validated['amount'],
                    'payment_date' => $validated['payment_date'],
                    'payment_type' => $validated['payment_type'],
                    'paid_by_user_id' => auth()->id(),
                    'notes' => $validated['notes']
                ]);

                // Update lot payment amounts
                $lot->updatePaymentAmounts();

                // Update supplier balance if supplier exists
                if ($lot->supplier) {
                    $lot->supplier->balance_owed -= $validated['amount'];
                    $lot->supplier->save();
                }
            });

            return response()->json([
                'success' => true, 
                'message' => 'Pago agregado exitosamente'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error adding payment: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Error al agregar el pago: ' . $e->getMessage()
            ], 422);
        }
    }

    // ========================================  
    // DEBUG METHOD - TEMPORARY
    // ========================================
    
    public function debugPayments(Lot $lot)
    {
        // Only polymorphic payments exist now
        $polyPayments = $lot->payments;  
        $polyTotal = $polyPayments->sum('amount');
        
        $totalPaid = $polyTotal;
        $remaining = $lot->total_purchase_cost - $totalPaid;
        
        return response()->json([
            'lot_info' => [
                'id' => $lot->id,
                'lot_code' => $lot->lot_code,
                'total_cost' => $lot->total_purchase_cost,
                'current_status' => $lot->payment_status
            ],
            'polymorphic_payments' => [
                'count' => $polyPayments->count(),  
                'total' => $polyTotal,
                'payments' => $polyPayments->map(function($p) {
                    return [
                        'id' => $p->id,
                        'amount' => $p->amount,
                        'date' => $p->payment_date,
                        'method' => $p->payment_method
                    ];
                })
            ],
            'summary' => [
                'total_paid' => $totalPaid,
                'remaining_balance' => $remaining,
                'should_be_status' => $remaining <= 0 ? 'paid' : ($totalPaid > 0 ? 'partial' : 'pending')
            ]
        ]);
    }

    // ========================================
    // NEW PAYMENT SYSTEM METHODS (Following Sales Pattern)
    // ========================================
    
    public function paymentTimeline(Lot $lot)
    {
        try {
            // Load only polymorphic payment system
            $lot->load(['supplier', 'payments.createdBy']);
            
            $html = view('lots.partials.payment-timeline', compact('lot'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in lot payment timeline', ['error' => $e->getMessage(), 'lot_id' => $lot->id]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar timeline: ' . $e->getMessage()
            ], 500);
        }
    }

    public function paymentForm(Lot $lot)
    {
        try {
            $lot->load(['supplier', 'payments']);
            
            // Calculate total paid from polymorphic system only
            $totalPaid = $lot->payments->sum('amount');
            $remainingBalance = $lot->total_purchase_cost - $totalPaid;
            
            $html = view('lots.partials.payment-form', compact('lot', 'remainingBalance'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in lot payment form', ['error' => $e->getMessage(), 'lot_id' => $lot->id]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar formulario: ' . $e->getMessage()
            ], 500);
        }
    }
}