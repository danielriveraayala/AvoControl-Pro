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

    public function create(Request $request)
    {
        // Redirect to specific payment creation based on type
        if ($request->has('sale_id')) {
            $sale = Sale::findOrFail($request->sale_id);
            return $this->createSalePayment($sale);
        }
        
        if ($request->has('lot_id')) {
            $lot = Lot::findOrFail($request->lot_id);
            return $this->createLotPayment($lot);
        }
        
        // Default create view for general payments
        return view('payments.create');
    }

    public function store(Request $request)
    {
        // Handle general payment creation
        $validated = $request->validate([
            'type' => 'required|in:income,expense',
            'concept' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,transfer,check,credit',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'description' => 'required|string|max:500'
        ]);

        // Generate payment code
        $lastPayment = Payment::whereDate('created_at', today())->count();
        $paymentCode = 'PAY-' . date('Ymd') . '-' . str_pad($lastPayment + 1, 3, '0', STR_PAD_LEFT);

        Payment::create([
            'payment_code' => $paymentCode,
            'type' => $validated['type'],
            'concept' => $validated['concept'],
            'payable_type' => null,
            'payable_id' => null,
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'payment_method' => $validated['payment_method'],
            'reference' => $validated['reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'description' => $validated['description'],
            'created_by' => auth()->id()
        ]);

        return redirect()->route('payments.index')
            ->with('success', 'Pago registrado exitosamente');
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

    public function storeSalePayment(Request $request)
    {
        try {
            \Log::info('StoreSalePayment called', $request->all());

            $validated = $request->validate([
                'sale_id' => 'required|exists:sales,id',
                'amount' => 'required|numeric|min:0.01',
                'payment_date' => 'required|date',
                'payment_method' => 'required|in:cash,transfer,check,card,credit',
                'reference_number' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:1000'
            ]);

            \Log::info('Validation passed', $validated);

            $sale = Sale::findOrFail($validated['sale_id']);
            \Log::info('Sale found', ['sale_id' => $sale->id]);
            
            // Check remaining balance
            $totalPaid = $sale->payments->sum('amount');
            $remainingBalance = $sale->total_amount - $totalPaid;
            
            \Log::info('Balance check', [
                'total_amount' => $sale->total_amount,
                'total_paid' => $totalPaid,
                'remaining_balance' => $remainingBalance,
                'requested_amount' => $validated['amount']
            ]);
            
            if ($validated['amount'] > $remainingBalance) {
                return response()->json([
                    'success' => false,
                    'message' => 'El monto excede el saldo pendiente de $' . number_format($remainingBalance, 2)
                ]);
            }

            DB::transaction(function () use ($sale, $validated) {
                // Generate payment code
                $lastPayment = Payment::whereDate('created_at', today())->count();
                $paymentCode = 'PAY-' . date('Ymd') . '-' . str_pad($lastPayment + 1, 3, '0', STR_PAD_LEFT);

                \Log::info('Creating payment', [
                    'payment_code' => $paymentCode,
                    'sale_id' => $sale->id,
                    'amount' => $validated['amount']
                ]);

                // Create payment using morphMany relationship
                $payment = $sale->payments()->create([
                    'payment_code' => $paymentCode,
                    'type' => 'income',
                    'concept' => 'sale_payment',
                    'amount' => $validated['amount'],
                    'payment_date' => $validated['payment_date'],
                    'payment_method' => $validated['payment_method'],
                    'reference' => $validated['reference_number'],
                    'notes' => $validated['notes'],
                    'created_by' => auth()->id() ?? 1
                ]);

                \Log::info('Payment created successfully', ['payment_id' => $payment->id]);

                // Update sale payment status - payments() already includes the new payment
                $newTotalPaid = $sale->payments()->sum('amount');
                
                \Log::info('Payment status update', [
                    'total_amount' => $sale->total_amount,
                    'new_total_paid' => $newTotalPaid,
                    'payment_status' => $newTotalPaid >= $sale->total_amount ? 'paid' : 'partial'
                ]);
            
                if ($newTotalPaid >= $sale->total_amount) {
                    $sale->payment_status = 'paid';
                } else {
                    $sale->payment_status = 'partial';
                }
                
                $sale->save();

            // Update customer balance
            if ($sale->customer) {
                $sale->customer->current_balance -= $validated['amount'];
                $sale->customer->save();
            }
            });

            return response()->json([
                'success' => true,
                'message' => 'Pago registrado exitosamente'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in storeSalePayment', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeLotPayment(Request $request)
    {
        try {
            \Log::info('StoreLotPayment called', $request->all());

            $validated = $request->validate([
                'lot_id' => 'required|exists:lots,id',
                'amount' => 'required|numeric|min:0.01',
                'payment_date' => 'required|date',
                'payment_method' => 'required|in:cash,transfer,check,card,credit',
                'reference_number' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:1000'
            ]);

            \Log::info('Validation passed', $validated);

            $lot = \App\Models\Lot::findOrFail($validated['lot_id']);
            \Log::info('Lot found', ['lot_id' => $lot->id]);
            
            // Check remaining balance from both payment systems
            $polymorphicPayments = $lot->payments->sum('amount');
            $lotPayments = $lot->lotPayments->sum('amount');
            $totalPaid = $polymorphicPayments + $lotPayments;
            $remainingBalance = $lot->total_purchase_cost - $totalPaid;
            
            \Log::info('Balance check', [
                'total_purchase_cost' => $lot->total_purchase_cost,
                'total_paid' => $totalPaid,
                'remaining_balance' => $remainingBalance,
                'requested_amount' => $validated['amount']
            ]);
            
            if ($validated['amount'] > $remainingBalance) {
                return response()->json([
                    'success' => false,
                    'message' => 'El monto excede el saldo pendiente de $' . number_format($remainingBalance, 2)
                ]);
            }

            DB::transaction(function () use ($lot, $validated) {
                // Generate payment code
                $lastPayment = Payment::whereDate('created_at', today())->count();
                $paymentCode = 'PAY-' . date('Ymd') . '-' . str_pad($lastPayment + 1, 3, '0', STR_PAD_LEFT);

                \Log::info('Creating payment', [
                    'payment_code' => $paymentCode,
                    'lot_id' => $lot->id,
                    'amount' => $validated['amount']
                ]);

                // Create payment using morphMany relationship
                $payment = $lot->payments()->create([
                    'payment_code' => $paymentCode,
                    'type' => 'expense',
                    'concept' => 'lot_purchase',
                    'amount' => $validated['amount'],
                    'payment_date' => $validated['payment_date'],
                    'payment_method' => $validated['payment_method'],
                    'reference' => $validated['reference_number'],
                    'notes' => $validated['notes'],
                    'created_by' => auth()->id() ?? 1
                ]);

                \Log::info('Payment created successfully', ['payment_id' => $payment->id]);

                // Update lot payment status - payments() already includes the new payment
                $newTotalPaid = $lot->payments()->sum('amount');
                
                \Log::info('Payment status update', [
                    'total_purchase_cost' => $lot->total_purchase_cost,
                    'new_total_paid' => $newTotalPaid,
                    'payment_status' => $newTotalPaid >= $lot->total_purchase_cost ? 'paid' : 'partial'
                ]);
            
                if ($newTotalPaid >= $lot->total_purchase_cost) {
                    $lot->payment_status = 'paid';
                } else {
                    $lot->payment_status = 'partial';
                }
                
                $lot->save();

                // Update supplier balance
                if ($lot->supplier) {
                    $lot->supplier->balance_owed -= $validated['amount'];
                    $lot->supplier->save();
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Pago registrado exitosamente'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in storeLotPayment', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el pago: ' . $e->getMessage()
            ], 500);
        }
    }
}