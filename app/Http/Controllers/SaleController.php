<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Lot;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'saleItems.lot']);

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

        // Handle AJAX requests
        if ($request->ajax()) {
            $stats = [
                'total' => $sales->total(),
                'value' => number_format($sales->sum('total_amount'), 0),
                'weight' => number_format($sales->sum('total_weight'), 0),
                'pending' => $sales->whereIn('payment_status', ['pending', 'partial'])->count()
            ];

            return response()->json([
                'html' => view('sales.partials.table', compact('sales'))->render(),
                'stats' => $stats
            ]);
        }

        return view('sales.index', compact('sales', 'customers'));
    }

    public function create()
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $lots = Lot::where('status', '!=', 'sold')
            ->where('weight_available', '>', 0)
            ->with('supplier')
            ->get();
            
        return view('sales.create', compact('customers', 'lots'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'sale_date' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:sale_date',
            'invoice_number' => 'nullable|string|unique:sales,invoice_number',
            'items' => 'required|array|min:1',
            'items.*.lot_id' => 'required|exists:lots,id',
            'items.*.weight' => 'required|numeric|min:0.01',
            'items.*.price_per_kg' => 'required|numeric|min:0.01'
        ]);

        $sale = DB::transaction(function () use ($validated) {
            // Generate sale code
            $lastSale = Sale::whereDate('created_at', today())->count();
            $saleCode = 'VTA-' . date('Ymd') . '-' . str_pad($lastSale + 1, 3, '0', STR_PAD_LEFT);

            // Calculate totals
            $totalWeight = 0;
            $totalAmount = 0;

            foreach ($validated['items'] as $item) {
                $subtotal = $item['weight'] * $item['price_per_kg'];
                $totalWeight += $item['weight'];
                $totalAmount += $subtotal;
            }

            // Create sale
            $sale = Sale::create([
                'sale_code' => $saleCode,
                'customer_id' => $validated['customer_id'],
                'sale_date' => $validated['sale_date'],
                'delivery_date' => $validated['delivery_date'] ?? null,
                'invoice_number' => $validated['invoice_number'] ?? null,
                'total_weight' => $totalWeight,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_status' => 'pending'
            ]);

            // Create sale items and update lots
            foreach ($validated['items'] as $item) {
                $lot = Lot::find($item['lot_id']);
                
                // Validate available weight
                if ($item['weight'] > $lot->weight_available) {
                    throw new \Exception("Peso solicitado excede el disponible para el lote {$lot->lot_code}");
                }

                // Create sale item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'lot_id' => $item['lot_id'],
                    'weight' => $item['weight'],
                    'price_per_kg' => $item['price_per_kg'],
                    'subtotal' => $item['weight'] * $item['price_per_kg']
                ]);

                // Update lot
                $lot->weight_sold += $item['weight'];
                $lot->weight_available -= $item['weight'];
                $lot->status = $lot->weight_available == 0 ? 'sold' : 'partial';
                $lot->save();
            }

            // Update customer balance
            $customer = Customer::find($validated['customer_id']);
            $customer->current_balance += $totalAmount;
            $customer->save();

            return $sale;
        });

        return redirect()->route('sales.show', $sale)
            ->with('success', 'Venta registrada exitosamente');
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'saleItems.lot.supplier', 'payments']);
        
        $totalPaid = $sale->payments()
            ->where('type', 'income')
            ->sum('amount');
            
        $pendingAmount = $sale->total_amount - $totalPaid;

        return view('sales.show', compact('sale', 'totalPaid', 'pendingAmount'));
    }

    public function edit(Sale $sale)
    {
        // Only allow editing if sale is pending
        if ($sale->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Solo se pueden editar ventas pendientes');
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
        // Only allow editing if sale is pending
        if ($sale->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Solo se pueden editar ventas pendientes');
        }

        $validated = $request->validate([
            'sale_date' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:sale_date',
            'invoice_number' => 'nullable|string|unique:sales,invoice_number,' . $sale->id,
            'status' => 'required|in:pending,delivered,cancelled'
        ]);

        $sale->update($validated);

        return redirect()->route('sales.show', $sale)
            ->with('success', 'Venta actualizada exitosamente');
    }

    public function destroy(Sale $sale)
    {
        // Only allow deletion if sale is pending and has no payments
        if ($sale->status !== 'pending' || $sale->payments()->count() > 0) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar esta venta');
        }

        DB::transaction(function () use ($sale) {
            // Restore lot quantities
            foreach ($sale->saleItems as $item) {
                $lot = $item->lot;
                $lot->weight_sold -= $item->weight;
                $lot->weight_available += $item->weight;
                $lot->status = $lot->weight_sold == 0 ? 'active' : 'partial';
                $lot->save();
            }

            // Update customer balance
            $sale->customer->current_balance -= $sale->total_amount;
            $sale->customer->save();

            // Delete sale items and sale
            $sale->saleItems()->delete();
            $sale->delete();
        });

        return redirect()->route('sales.index')
            ->with('success', 'Venta eliminada exitosamente');
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
}