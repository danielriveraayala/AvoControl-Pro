<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Sale;
use App\Models\Lot;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['payable', 'createdBy']);

        // Filters
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }
        
        if ($request->has('concept') && $request->concept !== 'all') {
            $query->where('concept', $request->concept);
        }
        
        if ($request->has('payment_method') && $request->payment_method !== 'all') {
            $query->where('payment_method', $request->payment_method);
        }
        
        if ($request->has('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        $payments = $query->latest('payment_date')->paginate(20);

        // Calculate totals for the period
        $totals = [
            'income' => Payment::where('type', 'income')
                ->when($request->date_from, fn($q) => $q->whereDate('payment_date', '>=', $request->date_from))
                ->when($request->date_to, fn($q) => $q->whereDate('payment_date', '<=', $request->date_to))
                ->sum('amount'),
            'expense' => Payment::where('type', 'expense')
                ->when($request->date_from, fn($q) => $q->whereDate('payment_date', '>=', $request->date_from))
                ->when($request->date_to, fn($q) => $q->whereDate('payment_date', '<=', $request->date_to))
                ->sum('amount')
        ];
        $totals['balance'] = $totals['income'] - $totals['expense'];

        return view('payments.index', compact('payments', 'totals'));
    }

    public function createSalePayment(Sale $sale)
    {
        $totalPaid = $sale->payments()->where('type', 'income')->sum('amount');
        $pendingAmount = $sale->total_amount - $totalPaid;
        
        if ($pendingAmount <= 0) {
            return redirect()->back()
                ->with('error', 'Esta venta ya está completamente pagada');
        }

        return view('payments.create-sale', compact('sale', 'pendingAmount', 'totalPaid'));
    }

    public function storeSalePayment(Request $request, Sale $sale)
    {
        $totalPaid = $sale->payments()->where('type', 'income')->sum('amount');
        $pendingAmount = $sale->total_amount - $totalPaid;

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $pendingAmount,
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,transfer,check,credit',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        DB::transaction(function () use ($sale, $validated) {
            // Generate payment code
            $lastPayment = Payment::whereDate('created_at', today())->count();
            $paymentCode = 'PAY-' . date('Ymd') . '-' . str_pad($lastPayment + 1, 3, '0', STR_PAD_LEFT);

            // Create payment
            Payment::create([
                'payment_code' => $paymentCode,
                'type' => 'income',
                'concept' => 'sale_payment',
                'payable_type' => Sale::class,
                'payable_id' => $sale->id,
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id()
            ]);

            // Update sale payment status
            $newTotalPaid = $sale->payments()->where('type', 'income')->sum('amount');
            if ($newTotalPaid >= $sale->total_amount) {
                $sale->payment_status = 'paid';
            } else {
                $sale->payment_status = 'partial';
            }
            $sale->save();

            // Update customer balance
            $sale->customer->current_balance -= $validated['amount'];
            $sale->customer->save();
        });

        return redirect()->route('sales.show', $sale)
            ->with('success', 'Pago registrado exitosamente');
    }

    public function createLotPayment(Lot $lot)
    {
        $totalPaid = $lot->payments()->where('type', 'expense')->sum('amount');
        $pendingAmount = $lot->total_purchase_cost - $totalPaid;
        
        if ($pendingAmount <= 0) {
            return redirect()->back()
                ->with('error', 'Este lote ya está completamente pagado');
        }

        return view('payments.create-lot', compact('lot', 'pendingAmount', 'totalPaid'));
    }

    public function storeLotPayment(Request $request, Lot $lot)
    {
        $totalPaid = $lot->payments()->where('type', 'expense')->sum('amount');
        $pendingAmount = $lot->total_purchase_cost - $totalPaid;

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $pendingAmount,
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,transfer,check,credit',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        DB::transaction(function () use ($lot, $validated) {
            // Generate payment code
            $lastPayment = Payment::whereDate('created_at', today())->count();
            $paymentCode = 'PAY-' . date('Ymd') . '-' . str_pad($lastPayment + 1, 3, '0', STR_PAD_LEFT);

            // Create payment
            Payment::create([
                'payment_code' => $paymentCode,
                'type' => 'expense',
                'concept' => 'lot_purchase',
                'payable_type' => Lot::class,
                'payable_id' => $lot->id,
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id()
            ]);

            // Update supplier balance
            $lot->supplier->balance_owed -= $validated['amount'];
            $lot->supplier->save();
        });

        return redirect()->route('lots.show', $lot)
            ->with('success', 'Pago registrado exitosamente');
    }

    public function show(Payment $payment)
    {
        $payment->load(['payable', 'createdBy']);
        return view('payments.show', compact('payment'));
    }

    public function destroy(Payment $payment)
    {
        DB::transaction(function () use ($payment) {
            // Reverse the payment effect
            if ($payment->concept === 'sale_payment') {
                // Update sale payment status
                $sale = $payment->payable;
                $sale->customer->current_balance += $payment->amount;
                $sale->customer->save();
                
                $totalPaid = $sale->payments()->where('type', 'income')->where('id', '!=', $payment->id)->sum('amount');
                if ($totalPaid == 0) {
                    $sale->payment_status = 'pending';
                } else {
                    $sale->payment_status = 'partial';
                }
                $sale->save();
            } elseif ($payment->concept === 'lot_purchase') {
                // Update supplier balance
                $lot = $payment->payable;
                $lot->supplier->balance_owed += $payment->amount;
                $lot->supplier->save();
            }

            $payment->delete();
        });

        return redirect()->route('payments.index')
            ->with('success', 'Pago eliminado exitosamente');
    }

    public function dailyCashFlow(Request $request)
    {
        $date = $request->date ?? today();
        
        $income = Payment::where('type', 'income')
            ->whereDate('payment_date', $date)
            ->sum('amount');
            
        $expense = Payment::where('type', 'expense')
            ->whereDate('payment_date', $date)
            ->sum('amount');
            
        $payments = Payment::with(['payable', 'createdBy'])
            ->whereDate('payment_date', $date)
            ->orderBy('created_at')
            ->get();

        return view('payments.daily-cash-flow', compact('date', 'income', 'expense', 'payments'));
    }
}