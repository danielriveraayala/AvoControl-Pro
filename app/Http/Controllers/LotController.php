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
        $query = Lot::with(['supplier', 'saleItems']);

        // Filters - Only apply if values are not null/empty
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('quality')) {
            $query->where('quality_grade', $request->quality);
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
                      ->orWhere('quality_grade', 'LIKE', "%{$search}%");
                });
                $totalFiltered = $query->count();
            }
            
            // Apply ordering
            if ($request->has('order')) {
                $columns = ['lot_code', 'supplier.name', 'harvest_date', 'total_weight', 'weight_available', 
                           'quality_grade', 'status', 'purchase_price_per_kg', 'total_purchase_cost', 'id'];
                $orderColumnIndex = $request->order[0]['column'] ?? 2;
                $orderDir = $request->order[0]['dir'] ?? 'desc';
                
                if ($orderColumnIndex == 1) { // Supplier column
                    $query->leftJoin('suppliers', 'lots.supplier_id', '=', 'suppliers.id')
                          ->select('lots.*')
                          ->orderByRaw('suppliers.name IS NULL, suppliers.name ' . $orderDir);
                } else {
                    $orderColumn = ['lot_code', 'supplier_id', 'harvest_date', 'total_weight', 'weight_available', 
                                   'quality_grade', 'status', 'purchase_price_per_kg', 'total_purchase_cost', 'id'][$orderColumnIndex] ?? 'harvest_date';
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

        // Calculate stats
        $totalWeight = $statsQuery->sum('weight_available') ?: 0;
        $totalValue = $statsQuery->sum('total_purchase_cost') ?: 0;
        
        // Calculate payment statistics
        $totalDebt = (clone $statsQuery)->sum('amount_owed') ?: 0;
        $totalPaid = (clone $statsQuery)->sum('amount_paid') ?: 0;
        $lotsWithDebt = (clone $statsQuery)->where('amount_owed', '>', 0)->count();
        
        // Calculate potential profit
        $potentialProfit = 0;
        $lotsWithSuggestedPrice = (clone $statsQuery)->whereNotNull('metadata')->get();
        foreach ($lotsWithSuggestedPrice as $lot) {
            $metadata = is_array($lot->metadata) ? $lot->metadata : json_decode($lot->metadata ?? '{}', true);
            if (isset($metadata['precio_venta_sugerido']) && $metadata['precio_venta_sugerido'] > 0) {
                $potentialRevenue = $lot->total_weight * $metadata['precio_venta_sugerido'];
                $potentialProfit += $potentialRevenue - $lot->total_purchase_cost;
            }
        }
        
        $stats = [
            'total' => $statsQuery->count(),
            'active' => (clone $statsQuery)->where('status', 'active')->count(),
            'weight' => number_format((float)$totalWeight, 2),
            'value' => number_format((float)$totalValue, 2),
            // Payment statistics
            'total_debt' => number_format((float)$totalDebt, 2),
            'total_paid' => number_format((float)$totalPaid, 2),
            'potential_profit' => number_format((float)$potentialProfit, 2),
            'lots_with_debt' => $lotsWithDebt
        ];

        // Handle AJAX requests (non-DataTables)
        if ($request->ajax() || $request->wantsJson()) {
            try {
                \Log::info('AJAX Response Data', [
                    'lots_count' => $lots->count(),
                    'stats' => $stats,
                    'query_sql' => $query->toSql()
                ]);
                
                $html = view('lots.partials.table', compact('lots'))->render();
                return response()->json([
                    'html' => $html,
                    'stats' => $stats,
                    'success' => true,
                    'debug' => [
                        'lots_count' => $lots->count(),
                        'sort' => $sortField,
                        'direction' => $sortDirection
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
        return view('lots.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|unique:lots,lot_code',
            'fecha_compra' => 'required|date',
            'peso_inicial' => 'required|numeric|min:0.01',
            'precio_compra' => 'required|numeric|min:0.01',
            'precio_venta_sugerido' => 'nullable|numeric|min:0.01',
            'calidad' => 'required|exists:quality_grades,name',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'notas' => 'nullable|string|max:1000',
            'monto_pago' => 'nullable|numeric|min:0',
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
                
                $lot = Lot::create([
                    'lot_code' => $validated['codigo'],
                    'harvest_date' => $validated['fecha_compra'],
                    'entry_date' => now(),
                    'total_weight' => $validated['peso_inicial'],
                    'purchase_price_per_kg' => $validated['precio_compra'],
                    'quality_grade' => $validated['calidad'],
                    'supplier_id' => $validated['supplier_id'],
                    'status' => 'active',
                    'weight_sold' => 0,
                    'weight_available' => $validated['peso_inicial'],
                    'total_purchase_cost' => $totalCost,
                    'amount_paid' => $amountPaid,
                    'amount_owed' => $amountOwed,
                    'payment_status' => $paymentStatus,
                    'metadata' => json_encode([
                        'notas' => $validated['notas'],
                        'precio_venta_sugerido' => $validated['precio_venta_sugerido']
                    ])
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
        $lot->load(['supplier', 'saleItems.sale.customer', 'payments', 'lotPayments']);
        
        if ($request->wantsJson()) {
            // Process metadata properly - handle both array and JSON string
            $metadata = is_array($lot->metadata) ? $lot->metadata : json_decode($lot->metadata ?? '{}', true);
            
            // Get latest payment for pre-filling form
            $latestPayment = $lot->lotPayments->first();
            
            // Debug logging
            \Log::info('Loading lot for edit', [
                'lot_id' => $lot->id,
                'raw_metadata' => $lot->getAttributes()['metadata'],
                'processed_metadata' => $metadata,
                'precio_venta_sugerido' => $metadata['precio_venta_sugerido'] ?? 'NOT_FOUND'
            ]);
            
            $response = [
                'id' => $lot->id,
                'lot_code' => $lot->lot_code,
                'supplier_id' => $lot->supplier_id,
                'codigo' => $lot->lot_code,
                'peso_inicial' => $lot->total_weight,
                'precio_compra' => $lot->purchase_price_per_kg,
                'precio_venta_sugerido' => $metadata['precio_venta_sugerido'] ?? null,
                'calidad' => $lot->quality_grade,
                'notas' => $metadata['notas'] ?? '',
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
        
        // Calculate metrics
        $metrics = [
            'weight_metrics' => [
                'total' => $lot->total_weight,
                'sold' => $lot->weight_sold,
                'available' => $lot->weight_available,
                'sold_percentage' => $lot->total_weight > 0 ? ($lot->weight_sold / $lot->total_weight) * 100 : 0
            ],
            'financial_metrics' => [
                'purchase_cost' => $lot->total_purchase_cost,
                'revenue' => $lot->saleItems->sum('subtotal') ?? 0,
                'profit' => ($lot->saleItems->sum('subtotal') ?? 0) - ($lot->weight_sold * $lot->purchase_price_per_kg),
            ]
        ];

        return view('lots.show', compact('lot', 'metrics'));
    }

    public function edit(Lot $lot)
    {
        $suppliers = Supplier::orderBy('name')->get();
        return view('lots.edit', compact('lot', 'suppliers'));
    }

    public function update(Request $request, Lot $lot)
    {
        // Only allow editing if lot hasn't been sold
        if ($lot->weight_sold > 0) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No se puede editar un lote que ya tiene ventas'], 422);
            }
            return redirect()->back()->with('error', 'No se puede editar un lote que ya tiene ventas');
        }

        $validated = $request->validate([
            'codigo' => 'required|string|unique:lots,lot_code,' . $lot->id,
            'fecha_compra' => 'required|date',
            'peso_inicial' => 'required|numeric|min:0.01',
            'precio_compra' => 'required|numeric|min:0.01',
            'precio_venta_sugerido' => 'nullable|numeric|min:0.01',
            'calidad' => 'required|exists:quality_grades,name',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'notas' => 'nullable|string|max:1000',
            'monto_pago' => 'nullable|numeric|min:0',
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

                // Update lot
                $lot->update([
                    'lot_code' => $validated['codigo'],
                    'harvest_date' => $validated['fecha_compra'],
                    'entry_date' => now(),
                    'total_weight' => $validated['peso_inicial'],
                    'purchase_price_per_kg' => $validated['precio_compra'],
                    'quality_grade' => $validated['calidad'],
                    'supplier_id' => $validated['supplier_id'],
                    'weight_available' => $validated['peso_inicial'] - $lot->weight_sold,
                    'total_purchase_cost' => $newCost,
                    'amount_paid' => $newPaid,
                    'amount_owed' => $newOwed,
                    'payment_status' => $paymentStatus,
                    'metadata' => json_encode([
                        'notas' => $validated['notas'],
                        'precio_venta_sugerido' => $validated['precio_venta_sugerido']
                    ])
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
        // Only allow deletion if lot hasn't been sold
        if ($lot->weight_sold > 0) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No se puede eliminar un lote que ya tiene ventas'], 422);
            }
            return redirect()->back()->with('error', 'No se puede eliminar un lote que ya tiene ventas');
        }

        try {
            DB::transaction(function () use ($lot) {
                // Update supplier balance only if supplier exists
                if ($lot->supplier) {
                    $lot->supplier->balance_owed -= $lot->total_purchase_cost;
                    $lot->supplier->total_purchased -= $lot->total_purchase_cost;
                    $lot->supplier->save();
                }

                $lot->delete();
            });

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Lote eliminado exitosamente']);
            }

            return redirect()->route('lots.index')->with('success', 'Lote eliminado exitosamente');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Error al eliminar el lote'], 422);
            }
            return back()->with('error', 'Error al eliminar el lote');
        }
    }

    public function report(Request $request, Lot $lot)
    {
        $lot->load(['supplier', 'saleItems.sale.customer', 'payments', 'lotPayments.paidByUser']);
        
        // Get metadata for suggested price
        $metadata = is_array($lot->metadata) ? $lot->metadata : json_decode($lot->metadata ?? '{}', true);
        $suggestedPrice = $metadata['precio_venta_sugerido'] ?? null;
        
        // Calculate metrics
        $metrics = [
            'weight_metrics' => [
                'total' => $lot->total_weight,
                'sold' => $lot->weight_sold,
                'available' => $lot->weight_available,
                'sold_percentage' => $lot->total_weight > 0 ? ($lot->weight_sold / $lot->total_weight) * 100 : 0
            ],
            'financial_metrics' => [
                'purchase_cost' => $lot->total_purchase_cost,
                'revenue' => $lot->saleItems->sum('subtotal') ?? 0,
                'profit' => ($lot->saleItems->sum('subtotal') ?? 0) - ($lot->weight_sold * $lot->purchase_price_per_kg),
                'suggested_price_per_kg' => $suggestedPrice,
                'potential_revenue' => $suggestedPrice ? ($lot->total_weight * $suggestedPrice) : null,
                'potential_profit' => $suggestedPrice ? (($lot->total_weight * $suggestedPrice) - $lot->total_purchase_cost) : null,
                'potential_margin' => $suggestedPrice && $lot->total_purchase_cost > 0 ? ((($lot->total_weight * $suggestedPrice) - $lot->total_purchase_cost) / $lot->total_purchase_cost * 100) : null
            ]
        ];

        if ($request->wantsJson()) {
            try {
                $html = view('lots.partials.report', compact('lot', 'metrics'))->render();
                \Log::info('Report HTML generated successfully', ['lot_id' => $lot->id, 'html_length' => strlen($html)]);
                return response()->json(['html' => $html, 'success' => true]);
            } catch (\Exception $e) {
                \Log::error('Error generating report HTML: ' . $e->getMessage(), ['lot_id' => $lot->id]);
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        return view('lots.report', compact('lot', 'metrics'));
    }

    public function downloadPDF(Lot $lot)
    {
        $lot->load(['supplier', 'saleItems.sale.customer', 'payments', 'lotPayments.paidByUser']);
        
        // Get metadata for suggested price
        $metadata = is_array($lot->metadata) ? $lot->metadata : json_decode($lot->metadata ?? '{}', true);
        $suggestedPrice = $metadata['precio_venta_sugerido'] ?? null;
        
        // Calculate metrics
        $metrics = [
            'weight_metrics' => [
                'total' => $lot->total_weight,
                'sold' => $lot->weight_sold,
                'available' => $lot->weight_available,
                'sold_percentage' => $lot->total_weight > 0 ? ($lot->weight_sold / $lot->total_weight) * 100 : 0
            ],
            'financial_metrics' => [
                'purchase_cost' => $lot->total_purchase_cost,
                'revenue' => $lot->saleItems->sum('subtotal') ?? 0,
                'profit' => ($lot->saleItems->sum('subtotal') ?? 0) - ($lot->weight_sold * $lot->purchase_price_per_kg),
                'suggested_price_per_kg' => $suggestedPrice,
                'potential_revenue' => $suggestedPrice ? ($lot->total_weight * $suggestedPrice) : null,
                'potential_profit' => $suggestedPrice ? (($lot->total_weight * $suggestedPrice) - $lot->total_purchase_cost) : null,
                'potential_margin' => $suggestedPrice && $lot->total_purchase_cost > 0 ? ((($lot->total_weight * $suggestedPrice) - $lot->total_purchase_cost) / $lot->total_purchase_cost * 100) : null
            ]
        ];

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('lots.pdf.report', compact('lot', 'metrics'));
        
        return $pdf->download("lote_{$lot->lot_code}_reporte.pdf");
    }

    public function payments(Request $request, Lot $lot)
    {
        $lot->load(['lotPayments.paidByUser', 'supplier']);
        
        if ($request->wantsJson()) {
            return response()->json([
                'lot' => [
                    'id' => $lot->id,
                    'lot_code' => $lot->lot_code,
                    'total_purchase_cost' => $lot->total_purchase_cost,
                    'amount_paid' => $lot->amount_paid,
                    'amount_owed' => $lot->amount_owed,
                    'payment_status' => $lot->payment_status,
                    'supplier_name' => $lot->supplier ? $lot->supplier->name : 'Sin proveedor'
                ],
                'payments' => $lot->lotPayments->map(function($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => $payment->amount,
                        'payment_date' => $payment->payment_date->format('Y-m-d'),
                        'payment_type' => $payment->payment_type,
                        'notes' => $payment->notes,
                        'paid_by' => $payment->paidByUser ? $payment->paidByUser->name : 'Sistema',
                        'created_at' => $payment->created_at->format('d/m/Y H:i')
                    ];
                })
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
}