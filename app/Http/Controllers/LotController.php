<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LotController extends Controller
{
    public function index(Request $request)
    {
        $query = Lot::with(['supplier', 'saleItems']);

        // Filters
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }
        
        if ($request->has('quality') && $request->quality !== '') {
            $query->where('quality_grade', $request->quality);
        }
        
        if ($request->has('supplier_id') && $request->supplier_id !== '') {
            $query->where('supplier_id', $request->supplier_id);
        }
        
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('harvest_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('harvest_date', '<=', $request->date_to);
        }

        $lots = $query->latest('harvest_date')->paginate(20);
        $suppliers = Supplier::orderBy('name')->get();

        // Handle AJAX requests
        if ($request->ajax()) {
            $stats = [
                'total' => $lots->total(),
                'active' => Lot::where('status', 'active')->count(),
                'weight' => number_format($lots->sum('weight_available'), 0),
                'value' => number_format($lots->sum('total_purchase_cost'), 0)
            ];

            return response()->json([
                'html' => view('lots.partials.table', compact('lots'))->render(),
                'stats' => $stats
            ]);
        }

        return view('lots.index', compact('lots', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        return view('lots.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'harvest_date' => 'required|date',
            'entry_date' => 'required|date',
            'total_weight' => 'required|numeric|min:0.01',
            'purchase_price_per_kg' => 'required|numeric|min:0.01',
            'quality_grade' => 'required|in:Primera,Segunda,Tercera',
            'supplier_id' => 'required|exists:suppliers,id',
        ]);

        DB::transaction(function () use ($validated) {
            $lot = Lot::create([
                'harvest_date' => $validated['harvest_date'],
                'entry_date' => $validated['entry_date'],
                'total_weight' => $validated['total_weight'],
                'purchase_price_per_kg' => $validated['purchase_price_per_kg'],
                'quality_grade' => $validated['quality_grade'],
                'supplier_id' => $validated['supplier_id'],
                'status' => 'active',
                'weight_sold' => 0,
                'weight_available' => $validated['total_weight'],
                'total_purchase_cost' => $validated['total_weight'] * $validated['purchase_price_per_kg']
            ]);

            // Update supplier totals
            $supplier = Supplier::find($validated['supplier_id']);
            $supplier->total_purchased += $lot->total_purchase_cost;
            $supplier->balance_owed += $lot->total_purchase_cost;
            $supplier->save();
        });

        return redirect()->route('lots.index')
            ->with('success', 'Lote registrado exitosamente');
    }

    public function show(Lot $lot)
    {
        $lot->load(['supplier', 'saleItems.sale.customer', 'payments']);
        
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
                'revenue' => $lot->saleItems->sum('subtotal'),
                'profit' => $lot->saleItems->sum('subtotal') - ($lot->weight_sold * $lot->purchase_price_per_kg),
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
            return redirect()->back()
                ->with('error', 'No se puede editar un lote que ya tiene ventas');
        }

        $validated = $request->validate([
            'harvest_date' => 'required|date',
            'entry_date' => 'required|date',
            'total_weight' => 'required|numeric|min:0.01',
            'purchase_price_per_kg' => 'required|numeric|min:0.01',
            'quality_grade' => 'required|in:Primera,Segunda,Tercera',
        ]);

        DB::transaction(function () use ($lot, $validated) {
            // Update supplier balance with difference
            $oldCost = $lot->total_purchase_cost;
            $newCost = $validated['total_weight'] * $validated['purchase_price_per_kg'];
            $difference = $newCost - $oldCost;

            $lot->supplier->balance_owed += $difference;
            $lot->supplier->total_purchased += $difference;
            $lot->supplier->save();

            // Update lot
            $lot->update([
                'harvest_date' => $validated['harvest_date'],
                'entry_date' => $validated['entry_date'],
                'total_weight' => $validated['total_weight'],
                'purchase_price_per_kg' => $validated['purchase_price_per_kg'],
                'quality_grade' => $validated['quality_grade'],
                'weight_available' => $validated['total_weight'],
                'total_purchase_cost' => $newCost
            ]);
        });

        return redirect()->route('lots.show', $lot)
            ->with('success', 'Lote actualizado exitosamente');
    }

    public function destroy(Lot $lot)
    {
        // Only allow deletion if lot hasn't been sold
        if ($lot->weight_sold > 0) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar un lote que ya tiene ventas');
        }

        DB::transaction(function () use ($lot) {
            // Update supplier balance
            $lot->supplier->balance_owed -= $lot->total_purchase_cost;
            $lot->supplier->total_purchased -= $lot->total_purchase_cost;
            $lot->supplier->save();

            $lot->delete();
        });

        return redirect()->route('lots.index')
            ->with('success', 'Lote eliminado exitosamente');
    }
}