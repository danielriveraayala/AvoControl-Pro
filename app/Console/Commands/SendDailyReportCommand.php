<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Models\User;
use App\Models\Sale;
use App\Models\Lot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SendDailyReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:daily-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily sales and operations report via email, push and database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('📊 Generating daily report...');
            
            $today = Carbon::today();
            $yesterday = Carbon::yesterday();
            
            // Get daily statistics
            $stats = $this->getDailyStats($today, $yesterday);
            
            // Get users who should receive daily reports
            $users = User::whereIn('role', ['super_admin', 'admin'])->get();
            
            if ($users->isEmpty()) {
                $this->error('❌ No users found to send daily reports');
                return 1;
            }

            $notificationsSent = 0;
            
            foreach ($users as $user) {
                $notification = Notification::create([
                    'type' => 'report_daily',
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->id,
                    'data' => [
                        'title' => "📈 Reporte Diario - " . $today->format('d/m/Y'),
                        'message' => $this->formatReportMessage($stats),
                        'action_url' => route('dashboard'),
                        'action_text' => 'Ver Dashboard Completo',
                        'report_date' => $today->toDateString(),
                        'stats' => $stats
                    ],
                    'priority' => 'normal',
                    'channels' => ['email', 'push', 'database'],
                    'category' => 'report',
                    'metadata' => [
                        'report_type' => 'daily',
                        'generated_at' => Carbon::now()->toISOString(),
                        'period_start' => $yesterday->toDateString(),
                        'period_end' => $today->toDateString()
                    ]
                ]);
                
                $notificationsSent++;
            }

            $this->info("✅ Sent {$notificationsSent} daily reports via email, push and database");
            
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error generating daily report: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Get daily statistics
     */
    private function getDailyStats($today, $yesterday)
    {
        // Sales statistics
        $todaySales = Sale::whereDate('sale_date', $today)->count();
        $yesterdaySales = Sale::whereDate('sale_date', $yesterday)->count();
        
        $todaySalesValue = Sale::whereDate('sale_date', $today)->sum('total_amount');
        $yesterdaySalesValue = Sale::whereDate('sale_date', $yesterday)->sum('total_amount');
        
        // Lots statistics
        $todayLots = Lot::whereDate('created_at', $today)->count();
        $yesterdayLots = Lot::whereDate('created_at', $yesterday)->count();
        
        // Inventory
        $availableInventory = Lot::where('status', 'disponible')->sum('peso_restante');
        $totalInventoryValue = Lot::where('status', 'disponible')
            ->selectRaw('SUM(peso_restante * precio_compra) as total_value')
            ->first()->total_value ?? 0;
        
        return [
            'sales' => [
                'today_count' => $todaySales,
                'yesterday_count' => $yesterdaySales,
                'today_value' => $todaySalesValue,
                'yesterday_value' => $yesterdaySalesValue,
                'change_percent' => $yesterdaySales > 0 ? round((($todaySales - $yesterdaySales) / $yesterdaySales) * 100, 1) : 0
            ],
            'lots' => [
                'today_count' => $todayLots,
                'yesterday_count' => $yesterdayLots,
                'change_percent' => $yesterdayLots > 0 ? round((($todayLots - $yesterdayLots) / $yesterdayLots) * 100, 1) : 0
            ],
            'inventory' => [
                'available_weight' => $availableInventory,
                'total_value' => $totalInventoryValue
            ]
        ];
    }

    /**
     * Format the report message
     */
    private function formatReportMessage($stats)
    {
        $salesChange = $stats['sales']['change_percent'];
        $salesTrend = $salesChange > 0 ? "📈 +{$salesChange}%" : ($salesChange < 0 ? "📉 {$salesChange}%" : "➡️ Sin cambio");
        
        $lotsChange = $stats['lots']['change_percent'];
        $lotsTrend = $lotsChange > 0 ? "📈 +{$lotsChange}%" : ($lotsChange < 0 ? "📉 {$lotsChange}%" : "➡️ Sin cambio");
        
        return "RESUMEN DEL DÍA:\n\n" .
               "🛒 Ventas: {$stats['sales']['today_count']} (vs {$stats['sales']['yesterday_count']} ayer) {$salesTrend}\n" .
               "💰 Ingresos: $" . number_format($stats['sales']['today_value'], 2) . " (vs $" . number_format($stats['sales']['yesterday_value'], 2) . " ayer)\n" .
               "📦 Lotes nuevos: {$stats['lots']['today_count']} (vs {$stats['lots']['yesterday_count']} ayer) {$lotsTrend}\n" .
               "📊 Inventario: " . number_format($stats['inventory']['available_weight'], 2) . " kg disponibles\n" .
               "💎 Valor inventario: $" . number_format($stats['inventory']['total_value'], 2);
    }
}