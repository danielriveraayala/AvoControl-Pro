<?php

namespace App\Services;

use App\Models\Lot;
use App\Models\Payment;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class LotService
{
    public function createLot(array $data): Lot
    {
        return DB::transaction(function () use ($data) {
            // Generate lot code
            $lastLot = Lot::whereDate('created_at', today())->count();
            $lotCode = 'LOT-' . date('Ymd') . '-' . str_pad($lastLot + 1, 3, '0', STR_PAD_LEFT);
            
            // Create lot
            $lot = Lot::create([
                'lot_code' => $lotCode,
                'harvest_date' => $data['harvest_date'],
                'entry_date' => $data['entry_date'],
                'total_weight' => $data['total_weight'],
                'purchase_price_per_kg' => $data['purchase_price_per_kg'],
                'total_purchase_cost' => $data['total_weight'] * $data['purchase_price_per_kg'],
                'quality_grade' => $data['quality_grade'],
                'status' => 'active',
                'supplier_id' => $data['supplier_id'],
                'weight_sold' => 0,
                'weight_available' => $data['total_weight']
            ]);
            
            // If payment is immediate
            if (isset($data['payment_status']) && $data['payment_status'] === 'paid') {
                $this->createImmediatePayment($lot, $data);
            } else {
                // Update supplier balance
                $lot->supplier->increment('balance_owed', $lot->total_purchase_cost);
            }
            
            // Update supplier total purchased
            $lot->supplier->increment('total_purchased', $lot->total_purchase_cost);
            
            return $lot;
        });
    }
    
    private function createImmediatePayment(Lot $lot, array $data): Payment
    {
        $lastPayment = Payment::whereDate('created_at', today())->count();
        $paymentCode = 'PAY-' . date('Ymd') . '-' . str_pad($lastPayment + 1, 3, '0', STR_PAD_LEFT);
        
        return Payment::create([
            'payment_code' => $paymentCode,
            'type' => 'expense',
            'concept' => 'lot_purchase',
            'payable_type' => Lot::class,
            'payable_id' => $lot->id,
            'amount' => $lot->total_purchase_cost,
            'payment_date' => now(),
            'payment_method' => $data['payment_method'] ?? 'cash',
            'created_by' => auth()->id()
        ]);
    }
    
    public function calculateLotMetrics(Lot $lot): array
    {
        $profitability = $this->calculateProfitability($lot);
        $paymentStatus = $this->getLotPaymentStatus($lot);
        
        return [
            'weight_metrics' => [
                'total' => $lot->total_weight,
                'sold' => $lot->weight_sold,
                'available' => $lot->weight_available,
                'sold_percentage' => $lot->total_weight > 0 ? 
                    ($lot->weight_sold / $lot->total_weight) * 100 : 0
            ],
            'financial_metrics' => [
                'purchase_cost' => $lot->total_purchase_cost,
                'revenue' => $profitability['revenue'],
                'profit' => $profitability['profit'],
                'margin' => $profitability['margin'],
                'roi' => $lot->total_purchase_cost > 0 ? 
                    ($profitability['profit'] / $lot->total_purchase_cost) * 100 : 0
            ],
            'payment_status' => $paymentStatus,
            'days_in_inventory' => $lot->created_at->diffInDays(now())
        ];
    }
    
    private function calculateProfitability(Lot $lot): array
    {
        $totalSold = $lot->saleItems()->sum('subtotal');
        $costSold = $lot->weight_sold * $lot->purchase_price_per_kg;
        $profit = $totalSold - $costSold;
        $margin = $costSold > 0 ? ($profit / $costSold) * 100 : 0;
        
        return [
            'revenue' => $totalSold,
            'cost' => $costSold,
            'profit' => $profit,
            'margin' => $margin,
            'status' => $profit > 0 ? 'profitable' : 'loss'
        ];
    }
    
    private function getLotPaymentStatus(Lot $lot): array
    {
        $totalPaid = $lot->payments()->where('type', 'expense')->sum('amount');
        $pending = $lot->total_purchase_cost - $totalPaid;
        
        return [
            'total_cost' => $lot->total_purchase_cost,
            'paid' => $totalPaid,
            'pending' => $pending,
            'percentage_paid' => $lot->total_purchase_cost > 0 ?
                ($totalPaid / $lot->total_purchase_cost) * 100 : 0,
            'status' => $pending == 0 ? 'paid' : ($totalPaid > 0 ? 'partial' : 'pending')
        ];
    }
    
    public function getAvailableLots()
    {
        return Lot::where('status', '!=', 'sold')
            ->where('weight_available', '>', 0)
            ->with('supplier')
            ->orderBy('harvest_date', 'desc')
            ->get();
    }
    
    public function getLotsNearExpiry($daysThreshold = 7)
    {
        $thresholdDate = now()->subDays(30 - $daysThreshold);
        
        return Lot::where('status', '!=', 'sold')
            ->where('harvest_date', '<=', $thresholdDate)
            ->with('supplier')
            ->orderBy('harvest_date')
            ->get();
    }
}