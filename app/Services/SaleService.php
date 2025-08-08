<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Lot;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function createSale(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            // Generate sale code
            $lastSale = Sale::whereDate('created_at', today())->count();
            $saleCode = 'VTA-' . date('Ymd') . '-' . str_pad($lastSale + 1, 3, '0', STR_PAD_LEFT);
            
            // Calculate totals
            $totalWeight = 0;
            $totalAmount = 0;
            
            foreach ($data['items'] as $item) {
                $subtotal = $item['weight'] * $item['price_per_kg'];
                $totalWeight += $item['weight'];
                $totalAmount += $subtotal;
                
                // Validate available weight
                $lot = Lot::find($item['lot_id']);
                if ($item['weight'] > $lot->weight_available) {
                    throw new \Exception("Peso solicitado ({$item['weight']} kg) excede el disponible ({$lot->weight_available} kg) para el lote {$lot->lot_code}");
                }
            }
            
            // Create sale
            $sale = Sale::create([
                'sale_code' => $saleCode,
                'customer_id' => $data['customer_id'],
                'sale_date' => $data['sale_date'] ?? now(),
                'delivery_date' => $data['delivery_date'] ?? null,
                'invoice_number' => $data['invoice_number'] ?? null,
                'total_weight' => $totalWeight,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_status' => 'pending'
            ]);
            
            // Create sale items and update lots
            foreach ($data['items'] as $item) {
                $this->createSaleItem($sale, $item);
            }
            
            // Update customer balance
            $sale->customer->increment('current_balance', $totalAmount);
            
            return $sale;
        });
    }
    
    private function createSaleItem(Sale $sale, array $itemData): SaleItem
    {
        $lot = Lot::find($itemData['lot_id']);
        
        // Create sale item
        $saleItem = SaleItem::create([
            'sale_id' => $sale->id,
            'lot_id' => $itemData['lot_id'],
            'weight' => $itemData['weight'],
            'price_per_kg' => $itemData['price_per_kg'],
            'subtotal' => $itemData['weight'] * $itemData['price_per_kg']
        ]);
        
        // Update lot
        $lot->weight_sold += $itemData['weight'];
        $lot->weight_available -= $itemData['weight'];
        $lot->status = $lot->weight_available == 0 ? 'sold' : 'partial';
        $lot->save();
        
        return $saleItem;
    }
    
    public function cancelSale(Sale $sale): bool
    {
        if ($sale->status === 'cancelled') {
            return false;
        }
        
        if ($sale->payments()->count() > 0) {
            throw new \Exception('No se puede cancelar una venta con pagos registrados');
        }
        
        return DB::transaction(function () use ($sale) {
            // Restore lot quantities
            foreach ($sale->saleItems as $item) {
                $lot = $item->lot;
                $lot->weight_sold -= $item->weight;
                $lot->weight_available += $item->weight;
                $lot->status = $lot->weight_sold == 0 ? 'active' : 'partial';
                $lot->save();
            }
            
            // Update customer balance
            $sale->customer->decrement('current_balance', $sale->total_amount);
            
            // Update sale status
            $sale->status = 'cancelled';
            $sale->save();
            
            return true;
        });
    }
    
    public function calculateSaleMetrics(Sale $sale): array
    {
        $totalPaid = $sale->payments()->where('type', 'income')->sum('amount');
        $pendingAmount = $sale->total_amount - $totalPaid;
        
        // Calculate profit
        $totalCost = 0;
        foreach ($sale->saleItems as $item) {
            $totalCost += $item->weight * $item->lot->purchase_price_per_kg;
        }
        
        $profit = $sale->total_amount - $totalCost;
        $margin = $totalCost > 0 ? ($profit / $totalCost) * 100 : 0;
        
        return [
            'financial' => [
                'total_amount' => $sale->total_amount,
                'total_paid' => $totalPaid,
                'pending_amount' => $pendingAmount,
                'payment_percentage' => $sale->total_amount > 0 ?
                    ($totalPaid / $sale->total_amount) * 100 : 0
            ],
            'profitability' => [
                'revenue' => $sale->total_amount,
                'cost' => $totalCost,
                'profit' => $profit,
                'margin' => $margin
            ],
            'weight' => [
                'total' => $sale->total_weight,
                'items_count' => $sale->saleItems->count()
            ],
            'status' => [
                'delivery' => $sale->status,
                'payment' => $sale->payment_status
            ]
        ];
    }
    
    public function getSalesByPeriod($startDate, $endDate)
    {
        return Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->with(['customer', 'saleItems.lot'])
            ->orderBy('sale_date', 'desc')
            ->get();
    }
    
    public function getPendingSales()
    {
        return Sale::where('payment_status', '!=', 'paid')
            ->orWhere('status', 'pending')
            ->with(['customer', 'saleItems.lot'])
            ->orderBy('sale_date', 'desc')
            ->get();
    }
    
    public function getTopCustomers($limit = 10)
    {
        return Customer::withSum('sales', 'total_amount')
            ->orderByDesc('sales_sum_total_amount')
            ->take($limit)
            ->get();
    }
}