<?php

namespace App\Services;

use App\Models\Lot;
use App\Models\Sale;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getDashboardMetrics(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();
        
        return [
            'inventory' => $this->getInventoryMetrics(),
            'financial' => $this->getFinancialMetrics($startDate, $endDate),
            'operations' => $this->getOperationalMetrics($startDate, $endDate),
            'alerts' => $this->getSystemAlerts(),
            'charts' => $this->getChartData($startDate, $endDate)
        ];
    }
    
    private function getInventoryMetrics(): array
    {
        $activeLots = Lot::where('status', '!=', 'sold');
        
        return [
            'total_weight' => $activeLots->sum('weight_available'),
            'total_value' => $activeLots->sum(DB::raw('weight_available * purchase_price_per_kg')),
            'active_lots' => Lot::where('status', 'active')->count(),
            'partial_lots' => Lot::where('status', 'partial')->count(),
            'sold_lots' => Lot::where('status', 'sold')->count(),
            'quality_distribution' => Lot::where('status', '!=', 'sold')
                ->select('quality_grade', DB::raw('SUM(weight_available) as weight'))
                ->groupBy('quality_grade')
                ->get()
                ->mapWithKeys(fn($item) => [$item->quality_grade => $item->weight])
                ->toArray(),
            'oldest_lot_days' => Lot::where('status', '!=', 'sold')
                ->orderBy('harvest_date')
                ->first()
                ?->harvest_date->diffInDays(now()) ?? 0
        ];
    }
    
    private function getFinancialMetrics(Carbon $start, Carbon $end): array
    {
        // Purchases (expenses)
        $purchases = Payment::where('type', 'expense')
            ->whereBetween('payment_date', [$start, $end])
            ->sum('amount');
        
        // Sales (income)
        $salesIncome = Payment::where('type', 'income')
            ->whereBetween('payment_date', [$start, $end])
            ->sum('amount');
        
        // Total sales value (invoiced, not necessarily paid)
        $totalSalesValue = Sale::whereBetween('sale_date', [$start, $end])
            ->sum('total_amount');
        
        // Accounts receivable
        $accountsReceivable = Sale::where('payment_status', '!=', 'paid')
            ->sum(DB::raw('total_amount - COALESCE((SELECT SUM(amount) FROM payments WHERE payable_type = "App\\\\Models\\\\Sale" AND payable_id = sales.id AND type = "income"), 0)'));
        
        // Accounts payable
        $accountsPayable = Supplier::sum('balance_owed');
        
        return [
            'total_purchases' => $purchases,
            'total_sales_income' => $salesIncome,
            'total_sales_value' => $totalSalesValue,
            'net_profit' => $salesIncome - $purchases,
            'accounts_receivable' => $accountsReceivable,
            'accounts_payable' => $accountsPayable,
            'cash_flow' => $salesIncome - $purchases,
            'profit_margin' => $totalSalesValue > 0 ? 
                (($salesIncome - $purchases) / $totalSalesValue) * 100 : 0
        ];
    }
    
    private function getOperationalMetrics(Carbon $start, Carbon $end): array
    {
        $salesQuery = Sale::whereBetween('sale_date', [$start, $end]);
        $lotsQuery = Lot::whereBetween('entry_date', [$start, $end]);
        
        return [
            'total_sales' => $salesQuery->count(),
            'total_weight_sold' => $salesQuery->sum('total_weight'),
            'average_sale_value' => $salesQuery->avg('total_amount') ?? 0,
            'new_lots' => $lotsQuery->count(),
            'total_weight_purchased' => $lotsQuery->sum('total_weight'),
            'active_customers' => Sale::whereBetween('sale_date', [$start, $end])
                ->distinct('customer_id')
                ->count('customer_id'),
            'active_suppliers' => Lot::whereBetween('entry_date', [$start, $end])
                ->distinct('supplier_id')
                ->count('supplier_id'),
            'delivery_rate' => $salesQuery->count() > 0 ?
                ($salesQuery->where('status', 'delivered')->count() / $salesQuery->count()) * 100 : 0,
            'payment_collection_rate' => $salesQuery->count() > 0 ?
                ($salesQuery->where('payment_status', 'paid')->count() / $salesQuery->count()) * 100 : 0
        ];
    }
    
    private function getSystemAlerts(): array
    {
        $alerts = [];
        
        // Check for old unsold inventory
        $oldLots = Lot::where('status', '!=', 'sold')
            ->where('harvest_date', '<=', now()->subDays(30))
            ->count();
        
        if ($oldLots > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "Hay {$oldLots} lotes con más de 30 días sin vender completamente",
                'priority' => 'high'
            ];
        }
        
        // Check for high accounts receivable
        $receivable = Customer::where('current_balance', '>', 0)->sum('current_balance');
        if ($receivable > 100000) {
            $alerts[] = [
                'type' => 'info',
                'message' => 'Las cuentas por cobrar superan los $' . number_format($receivable, 2),
                'priority' => 'medium'
            ];
        }
        
        // Check for pending deliveries
        $pendingDeliveries = Sale::where('status', 'pending')
            ->where('sale_date', '<=', now()->subDays(3))
            ->count();
        
        if ($pendingDeliveries > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "Hay {$pendingDeliveries} ventas pendientes de entrega con más de 3 días",
                'priority' => 'high'
            ];
        }
        
        // Check low inventory
        $totalAvailable = Lot::where('status', '!=', 'sold')->sum('weight_available');
        if ($totalAvailable < 1000) {
            $alerts[] = [
                'type' => 'danger',
                'message' => 'Inventario bajo: Solo ' . number_format($totalAvailable, 2) . ' kg disponibles',
                'priority' => 'critical'
            ];
        }
        
        return $alerts;
    }
    
    private function getChartData(Carbon $start, Carbon $end): array
    {
        // Daily sales for the period
        $dailySales = Sale::whereBetween('sale_date', [$start, $end])
            ->select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('SUM(total_weight) as weight')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Daily purchases for the period
        $dailyPurchases = Lot::whereBetween('entry_date', [$start, $end])
            ->select(
                DB::raw('DATE(entry_date) as date'),
                DB::raw('SUM(total_purchase_cost) as total'),
                DB::raw('SUM(total_weight) as weight')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return [
            'sales_trend' => $dailySales->map(fn($item) => [
                'date' => $item->date,
                'amount' => $item->total,
                'weight' => $item->weight
            ]),
            'purchases_trend' => $dailyPurchases->map(fn($item) => [
                'date' => $item->date,
                'amount' => $item->total,
                'weight' => $item->weight
            ])
        ];
    }
    
    public function getProfitabilityReport(Carbon $startDate, Carbon $endDate): array
    {
        $lots = Lot::with(['saleItems.sale'])
            ->whereBetween('harvest_date', [$startDate, $endDate])
            ->get()
            ->map(function ($lot) {
                $revenue = $lot->saleItems->sum('subtotal');
                $cost = $lot->weight_sold * $lot->purchase_price_per_kg;
                $profit = $revenue - $cost;
                $margin = $cost > 0 ? ($profit / $cost) * 100 : 0;
                
                return [
                    'lot' => $lot,
                    'metrics' => [
                        'revenue' => $revenue,
                        'cost' => $cost,
                        'profit' => $profit,
                        'margin' => $margin,
                        'status' => $profit > 0 ? 'profitable' : 'loss'
                    ],
                    'days_in_inventory' => $lot->created_at->diffInDays(now()),
                    'turnover_rate' => $lot->total_weight > 0 ?
                        ($lot->weight_sold / $lot->total_weight) * 100 : 0
                ];
            })
            ->sortByDesc('metrics.profit');
        
        return [
            'lots' => $lots,
            'summary' => [
                'total_lots' => $lots->count(),
                'profitable_lots' => $lots->where('metrics.status', 'profitable')->count(),
                'total_revenue' => $lots->sum('metrics.revenue'),
                'total_cost' => $lots->sum('metrics.cost'),
                'total_profit' => $lots->sum('metrics.profit'),
                'average_margin' => $lots->avg('metrics.margin'),
                'best_performing' => $lots->first(),
                'worst_performing' => $lots->where('metrics.profit', '<', 0)->last()
            ]
        ];
    }
    
    public function getCustomerAnalysis(): array
    {
        $customers = Customer::with(['sales' => function ($query) {
            $query->whereBetween('sale_date', [now()->subMonths(3), now()]);
        }])->get();
        
        return $customers->map(function ($customer) {
            $totalPurchases = $customer->sales->sum('total_amount');
            $totalWeight = $customer->sales->sum('total_weight');
            $avgPrice = $totalWeight > 0 ? $totalPurchases / $totalWeight : 0;
            
            return [
                'customer' => $customer,
                'metrics' => [
                    'total_purchases' => $totalPurchases,
                    'total_weight' => $totalWeight,
                    'average_price_per_kg' => $avgPrice,
                    'purchase_frequency' => $customer->sales->count(),
                    'outstanding_balance' => $customer->current_balance,
                    'payment_behavior' => $customer->current_balance > 0 ? 'debtor' : 'good'
                ]
            ];
        })->sortByDesc('metrics.total_purchases');
    }
}