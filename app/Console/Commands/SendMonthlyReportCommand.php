<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Models\User;
use App\Models\Sale;
use App\Models\Lot;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SendMonthlyReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:monthly-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send monthly financial and operations report via email, push and database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('📈 Generating monthly report...');
            
            $now = Carbon::now();
            $monthStart = $now->copy()->startOfMonth();
            $monthEnd = $now->copy()->endOfMonth();
            $previousMonthStart = $now->copy()->subMonth()->startOfMonth();
            $previousMonthEnd = $now->copy()->subMonth()->endOfMonth();
            
            // Get monthly statistics
            $stats = $this->getMonthlyStats($monthStart, $monthEnd, $previousMonthStart, $previousMonthEnd);
            
            // Get users who should receive monthly reports
            $users = User::whereIn('role', ['super_admin', 'admin', 'contador'])->get();
            
            if ($users->isEmpty()) {
                $this->error('❌ No users found to send monthly reports');
                return 1;
            }

            $notificationsSent = 0;
            
            foreach ($users as $user) {
                $notification = Notification::create([
                    'type' => 'report_monthly',
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->id,
                    'data' => [
                        'title' => "📊 Reporte Mensual - " . $monthStart->format('F Y'),
                        'message' => $this->formatMonthlyReport($stats, $monthStart),
                        'action_url' => route('dashboard'),
                        'action_text' => 'Ver Dashboard Completo',
                        'month_start' => $monthStart->toDateString(),
                        'month_end' => $monthEnd->toDateString(),
                        'stats' => $stats
                    ],
                    'priority' => 'high',
                    'channels' => ['email', 'push', 'database'],
                    'category' => 'report',
                    'metadata' => [
                        'report_type' => 'monthly',
                        'generated_at' => $now->toISOString(),
                        'period_start' => $monthStart->toDateString(),
                        'period_end' => $monthEnd->toDateString()
                    ]
                ]);
                
                $notificationsSent++;
            }

            $this->info("✅ Sent {$notificationsSent} monthly reports via email, push and database");
            
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error generating monthly report: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Get comprehensive monthly statistics
     */
    private function getMonthlyStats($monthStart, $monthEnd, $prevMonthStart, $prevMonthEnd)
    {
        return [
            'sales' => [
                'current_count' => Sale::whereBetween('sale_date', [$monthStart, $monthEnd])->count(),
                'previous_count' => Sale::whereBetween('sale_date', [$prevMonthStart, $prevMonthEnd])->count(),
                'current_revenue' => Sale::whereBetween('sale_date', [$monthStart, $monthEnd])->sum('total_amount'),
                'previous_revenue' => Sale::whereBetween('sale_date', [$prevMonthStart, $prevMonthEnd])->sum('total_amount'),
                'current_weight' => DB::table('sale_items')
                    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->whereBetween('sales.sale_date', [$monthStart, $monthEnd])
                    ->sum('sale_items.peso_vendido'),
                'top_customers' => $this->getTopCustomers($monthStart, $monthEnd)
            ],
            'purchases' => [
                'new_lots' => Lot::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                'total_weight' => Lot::whereBetween('created_at', [$monthStart, $monthEnd])->sum('peso_inicial'),
                'total_cost' => Lot::whereBetween('created_at', [$monthStart, $monthEnd])
                    ->selectRaw('SUM(peso_inicial * precio_compra) as total')
                    ->first()->total ?? 0,
                'top_suppliers' => $this->getTopSuppliers($monthStart, $monthEnd)
            ],
            'financial' => [
                'gross_profit' => $this->calculateGrossProfit($monthStart, $monthEnd),
                'cash_inflows' => Payment::whereBetween('payment_date', [$monthStart, $monthEnd])
                    ->where('type', 'cliente')->sum('amount'),
                'cash_outflows' => Payment::whereBetween('payment_date', [$monthStart, $monthEnd])
                    ->where('type', 'proveedor')->sum('amount'),
                'net_cash_flow' => Payment::whereBetween('payment_date', [$monthStart, $monthEnd])
                    ->selectRaw('
                        SUM(CASE WHEN type = "cliente" THEN amount ELSE 0 END) - 
                        SUM(CASE WHEN type = "proveedor" THEN amount ELSE 0 END) as net_flow
                    ')->first()->net_flow ?? 0,
                'accounts_receivable' => Customer::where('balance', '<', 0)->sum(DB::raw('ABS(balance)')),
                'accounts_payable' => Supplier::where('balance', '>', 0)->sum('balance')
            ],
            'inventory' => [
                'current_weight' => Lot::where('status', 'disponible')->sum('peso_restante'),
                'current_value' => Lot::where('status', 'disponible')
                    ->selectRaw('SUM(peso_restante * precio_compra) as total_value')
                    ->first()->total_value ?? 0,
                'turnover_ratio' => $this->calculateInventoryTurnover($monthStart, $monthEnd)
            ],
            'performance' => [
                'profit_margin' => $this->calculateProfitMargin($monthStart, $monthEnd),
                'average_sale_value' => Sale::whereBetween('sale_date', [$monthStart, $monthEnd])->avg('total_amount') ?? 0,
                'sales_per_day' => Sale::whereBetween('sale_date', [$monthStart, $monthEnd])->count() / $monthStart->daysInMonth
            ]
        ];
    }

    /**
     * Get top customers by sales volume
     */
    private function getTopCustomers($start, $end)
    {
        return DB::table('sales')
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->whereBetween('sales.sale_date', [$start, $end])
            ->groupBy('customers.id', 'customers.name')
            ->selectRaw('customers.name, SUM(sales.total_amount) as total_sales')
            ->orderByDesc('total_sales')
            ->limit(3)
            ->get();
    }

    /**
     * Get top suppliers by purchase volume
     */
    private function getTopSuppliers($start, $end)
    {
        return DB::table('lots')
            ->join('suppliers', 'lots.supplier_id', '=', 'suppliers.id')
            ->whereBetween('lots.created_at', [$start, $end])
            ->groupBy('suppliers.id', 'suppliers.name')
            ->selectRaw('suppliers.name, SUM(lots.peso_inicial * lots.precio_compra) as total_purchases')
            ->orderByDesc('total_purchases')
            ->limit(3)
            ->get();
    }

    /**
     * Calculate gross profit for the period
     */
    private function calculateGrossProfit($start, $end)
    {
        return DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('lots', 'sale_items.lot_id', '=', 'lots.id')
            ->whereBetween('sales.sale_date', [$start, $end])
            ->selectRaw('SUM((sale_items.precio_venta - lots.precio_compra) * sale_items.peso_vendido) as gross_profit')
            ->first()->gross_profit ?? 0;
    }

    /**
     * Calculate profit margin percentage
     */
    private function calculateProfitMargin($start, $end)
    {
        $revenue = Sale::whereBetween('sale_date', [$start, $end])->sum('total_amount');
        $grossProfit = $this->calculateGrossProfit($start, $end);
        
        return $revenue > 0 ? round(($grossProfit / $revenue) * 100, 2) : 0;
    }

    /**
     * Calculate inventory turnover ratio (simplified)
     */
    private function calculateInventoryTurnover($start, $end)
    {
        $avgInventoryValue = Lot::where('status', 'disponible')
            ->selectRaw('AVG(peso_restante * precio_compra) as avg_value')
            ->first()->avg_value ?? 1;
        
        $cogs = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('lots', 'sale_items.lot_id', '=', 'lots.id')
            ->whereBetween('sales.sale_date', [$start, $end])
            ->selectRaw('SUM(lots.precio_compra * sale_items.peso_vendido) as cogs')
            ->first()->cogs ?? 0;
        
        return $avgInventoryValue > 0 ? round($cogs / $avgInventoryValue, 2) : 0;
    }

    /**
     * Format monthly report message
     */
    private function formatMonthlyReport($stats, $monthStart)
    {
        $monthName = $monthStart->format('F Y');
        
        // Calculate changes from previous month
        $salesChange = $stats['sales']['previous_count'] > 0 ? 
            round((($stats['sales']['current_count'] - $stats['sales']['previous_count']) / $stats['sales']['previous_count']) * 100, 1) : 0;
        
        $revenueChange = $stats['sales']['previous_revenue'] > 0 ? 
            round((($stats['sales']['current_revenue'] - $stats['sales']['previous_revenue']) / $stats['sales']['previous_revenue']) * 100, 1) : 0;
        
        $salesTrend = $salesChange > 0 ? "📈 +{$salesChange}%" : ($salesChange < 0 ? "📉 {$salesChange}%" : "➡️ Sin cambio");
        $revenueTrend = $revenueChange > 0 ? "📈 +{$revenueChange}%" : ($revenueChange < 0 ? "📉 {$revenueChange}%" : "➡️ Sin cambio");
        
        return "REPORTE FINANCIERO MENSUAL - {$monthName}\n\n" .
               "📊 RESUMEN EJECUTIVO:\n" .
               "   • Ventas: {$stats['sales']['current_count']} {$salesTrend}\n" .
               "   • Ingresos: $" . number_format($stats['sales']['current_revenue'], 2) . " {$revenueTrend}\n" .
               "   • Margen de utilidad: {$stats['performance']['profit_margin']}%\n" .
               "   • Utilidad bruta: $" . number_format($stats['financial']['gross_profit'], 2) . "\n\n" .
               
               "💰 FLUJO DE EFECTIVO:\n" .
               "   • Ingresos: $" . number_format($stats['financial']['cash_inflows'], 2) . "\n" .
               "   • Egresos: $" . number_format($stats['financial']['cash_outflows'], 2) . "\n" .
               "   • Flujo neto: $" . number_format($stats['financial']['net_cash_flow'], 2) . "\n\n" .
               
               "📦 OPERACIONES:\n" .
               "   • Peso vendido: " . number_format($stats['sales']['current_weight'], 2) . " kg\n" .
               "   • Nuevos lotes: {$stats['purchases']['new_lots']}\n" .
               "   • Inversión en compras: $" . number_format($stats['purchases']['total_cost'], 2) . "\n" .
               "   • Rotación inventario: {$stats['inventory']['turnover_ratio']}x\n\n" .
               
               "💳 CUENTAS:\n" .
               "   • Por cobrar: $" . number_format($stats['financial']['accounts_receivable'], 2) . "\n" .
               "   • Por pagar: $" . number_format($stats['financial']['accounts_payable'], 2) . "\n\n" .
               
               "📈 KPIs:\n" .
               "   • Venta promedio: $" . number_format($stats['performance']['average_sale_value'], 2) . "\n" .
               "   • Ventas/día: " . number_format($stats['performance']['sales_per_day'], 1) . "\n" .
               "   • Inventario actual: " . number_format($stats['inventory']['current_weight'], 2) . " kg";
    }
}