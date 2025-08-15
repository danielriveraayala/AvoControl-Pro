<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Models\User;
use App\Models\Sale;
use App\Models\Lot;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SendWeeklyReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:weekly-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send weekly summary report via email, push and database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('📈 Generating weekly report...');
            
            $now = Carbon::now();
            $weekStart = $now->copy()->startOfWeek();
            $weekEnd = $now->copy()->endOfWeek();
            $previousWeekStart = $now->copy()->subWeek()->startOfWeek();
            $previousWeekEnd = $now->copy()->subWeek()->endOfWeek();
            
            // Get weekly statistics
            $stats = $this->getWeeklyStats($weekStart, $weekEnd, $previousWeekStart, $previousWeekEnd);
            
            // Get users who should receive weekly reports
            $users = User::whereIn('role', ['super_admin', 'admin'])->get();
            
            if ($users->isEmpty()) {
                $this->error('❌ No users found to send weekly reports');
                return 1;
            }

            $notificationsSent = 0;
            
            foreach ($users as $user) {
                $notification = Notification::create([
                    'type' => 'report_weekly',
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->id,
                    'data' => [
                        'title' => "📊 Resumen Semanal - " . $weekStart->format('d/m') . ' a ' . $weekEnd->format('d/m/Y'),
                        'message' => $this->formatWeeklyReport($stats),
                        'action_url' => route('dashboard'),
                        'action_text' => 'Ver Dashboard Completo',
                        'week_start' => $weekStart->toDateString(),
                        'week_end' => $weekEnd->toDateString(),
                        'stats' => $stats
                    ],
                    'priority' => 'normal',
                    'channels' => ['email', 'push', 'database'],
                    'category' => 'report',
                    'metadata' => [
                        'report_type' => 'weekly',
                        'generated_at' => $now->toISOString(),
                        'period_start' => $weekStart->toDateString(),
                        'period_end' => $weekEnd->toDateString()
                    ]
                ]);
                
                $notificationsSent++;
            }

            $this->info("✅ Sent {$notificationsSent} weekly reports via email, push and database");
            
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error generating weekly report: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Get weekly statistics with comparison to previous week
     */
    private function getWeeklyStats($weekStart, $weekEnd, $prevWeekStart, $prevWeekEnd)
    {
        return [
            'sales' => [
                'current_count' => Sale::whereBetween('sale_date', [$weekStart, $weekEnd])->count(),
                'previous_count' => Sale::whereBetween('sale_date', [$prevWeekStart, $prevWeekEnd])->count(),
                'current_value' => Sale::whereBetween('sale_date', [$weekStart, $weekEnd])->sum('total_amount'),
                'previous_value' => Sale::whereBetween('sale_date', [$prevWeekStart, $prevWeekEnd])->sum('total_amount'),
                'current_weight' => DB::table('sale_items')
                    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->whereBetween('sales.sale_date', [$weekStart, $weekEnd])
                    ->sum('sale_items.peso_vendido')
            ],
            'lots' => [
                'new_lots' => Lot::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
                'new_weight' => Lot::whereBetween('created_at', [$weekStart, $weekEnd])->sum('peso_inicial'),
                'new_investment' => Lot::whereBetween('created_at', [$weekStart, $weekEnd])
                    ->selectRaw('SUM(peso_inicial * precio_compra) as total')
                    ->first()->total ?? 0
            ],
            'payments' => [
                'received' => Payment::whereBetween('payment_date', [$weekStart, $weekEnd])
                    ->where('type', 'cliente')
                    ->sum('amount'),
                'paid' => Payment::whereBetween('payment_date', [$weekStart, $weekEnd])
                    ->where('type', 'proveedor')
                    ->sum('amount'),
                'net_flow' => Payment::whereBetween('payment_date', [$weekStart, $weekEnd])
                    ->selectRaw('
                        SUM(CASE WHEN type = "cliente" THEN amount ELSE 0 END) - 
                        SUM(CASE WHEN type = "proveedor" THEN amount ELSE 0 END) as net_flow
                    ')->first()->net_flow ?? 0
            ],
            'inventory' => [
                'current_available' => Lot::where('status', 'disponible')->sum('peso_restante'),
                'current_value' => Lot::where('status', 'disponible')
                    ->selectRaw('SUM(peso_restante * precio_compra) as total_value')
                    ->first()->total_value ?? 0
            ]
        ];
    }

    /**
     * Format weekly report message
     */
    private function formatWeeklyReport($stats)
    {
        // Calculate percentage changes
        $salesChange = $stats['sales']['previous_count'] > 0 ? 
            round((($stats['sales']['current_count'] - $stats['sales']['previous_count']) / $stats['sales']['previous_count']) * 100, 1) : 0;
        
        $valueChange = $stats['sales']['previous_value'] > 0 ? 
            round((($stats['sales']['current_value'] - $stats['sales']['previous_value']) / $stats['sales']['previous_value']) * 100, 1) : 0;
        
        $salesTrend = $salesChange > 0 ? "📈 +{$salesChange}%" : ($salesChange < 0 ? "📉 {$salesChange}%" : "➡️ Sin cambio");
        $valueTrend = $valueChange > 0 ? "📈 +{$valueChange}%" : ($valueChange < 0 ? "📉 {$valueChange}%" : "➡️ Sin cambio");
        
        return "RESUMEN SEMANAL DE OPERACIONES:\n\n" .
               "🛒 VENTAS:\n" .
               "   • Total: {$stats['sales']['current_count']} ventas (vs {$stats['sales']['previous_count']} semana anterior) {$salesTrend}\n" .
               "   • Ingresos: $" . number_format($stats['sales']['current_value'], 2) . " (vs $" . number_format($stats['sales']['previous_value'], 2) . ") {$valueTrend}\n" .
               "   • Peso vendido: " . number_format($stats['sales']['current_weight'], 2) . " kg\n\n" .
               
               "📦 INVENTARIO:\n" .
               "   • Nuevos lotes: {$stats['lots']['new_lots']}\n" .
               "   • Peso agregado: " . number_format($stats['lots']['new_weight'], 2) . " kg\n" .
               "   • Inversión: $" . number_format($stats['lots']['new_investment'], 2) . "\n" .
               "   • Stock actual: " . number_format($stats['inventory']['current_available'], 2) . " kg\n\n" .
               
               "💰 FLUJO DE EFECTIVO:\n" .
               "   • Cobros: $" . number_format($stats['payments']['received'], 2) . "\n" .
               "   • Pagos: $" . number_format($stats['payments']['paid'], 2) . "\n" .
               "   • Flujo neto: $" . number_format($stats['payments']['net_flow'], 2) . "\n\n" .
               
               "📊 INVENTARIO ACTUAL:\n" .
               "   • Peso disponible: " . number_format($stats['inventory']['current_available'], 2) . " kg\n" .
               "   • Valor del inventario: $" . number_format($stats['inventory']['current_value'], 2);
    }
}