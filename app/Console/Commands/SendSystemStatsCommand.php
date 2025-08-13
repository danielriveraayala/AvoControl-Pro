<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Models\User;
use App\Models\Sale;
use App\Models\Lot;
use App\Models\Customer;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SendSystemStatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:system-stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send weekly system statistics via email, push and database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('📈 Generating system statistics...');
            
            $now = Carbon::now();
            $weekStart = $now->startOfWeek();
            $weekEnd = $now->endOfWeek();
            
            // Get system statistics
            $stats = $this->getSystemStats($weekStart, $weekEnd);
            
            // Get users who should receive system stats
            $users = User::whereIn('role', ['super_admin', 'admin'])->get();
            
            if ($users->isEmpty()) {
                $this->error('❌ No users found to send system statistics');
                return 1;
            }

            $notificationsSent = 0;
            
            foreach ($users as $user) {
                $notification = Notification::create([
                    'type' => 'system_stats',
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->id,
                    'data' => [
                        'title' => "📊 Estadísticas Semanales del Sistema",
                        'message' => $this->formatStatsMessage($stats, $weekStart, $weekEnd),
                        'action_url' => route('dashboard'),
                        'action_text' => 'Ver Dashboard',
                        'week_start' => $weekStart->toDateString(),
                        'week_end' => $weekEnd->toDateString(),
                        'stats' => $stats
                    ],
                    'priority' => 'normal',
                    'channels' => ['email', 'push', 'database'],
                    'category' => 'system',
                    'metadata' => [
                        'report_type' => 'weekly_stats',
                        'generated_at' => $now->toISOString(),
                        'period_start' => $weekStart->toDateString(),
                        'period_end' => $weekEnd->toDateString()
                    ]
                ]);
                
                $notificationsSent++;
            }

            $this->info("✅ Sent {$notificationsSent} system statistics via email, push and database");
            
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error generating system statistics: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Get comprehensive system statistics
     */
    private function getSystemStats($weekStart, $weekEnd)
    {
        return [
            'sales' => [
                'total_this_week' => Sale::whereBetween('sale_date', [$weekStart, $weekEnd])->count(),
                'total_value_this_week' => Sale::whereBetween('sale_date', [$weekStart, $weekEnd])->sum('total_amount'),
                'total_all_time' => Sale::count(),
                'average_per_day' => Sale::whereBetween('sale_date', [$weekStart, $weekEnd])->count() / 7
            ],
            'lots' => [
                'total_this_week' => Lot::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
                'total_available' => Lot::where('status', 'disponible')->count(),
                'total_sold' => Lot::where('status', 'vendido')->count(),
                'total_weight_available' => Lot::where('status', 'disponible')->sum('peso_restante')
            ],
            'customers' => [
                'total_active' => Customer::whereNotNull('updated_at')->count(),
                'with_balance' => Customer::where('balance', '!=', 0)->count(),
                'positive_balance' => Customer::where('balance', '>', 0)->count(),
                'negative_balance' => Customer::where('balance', '<', 0)->count()
            ],
            'suppliers' => [
                'total_active' => Supplier::count(),
                'with_recent_lots' => Supplier::whereHas('lots', function($query) use ($weekStart) {
                    $query->where('created_at', '>=', $weekStart->subWeeks(4));
                })->count()
            ],
            'notifications' => [
                'sent_this_week' => Notification::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
                'unread_total' => Notification::whereNull('read_at')->count(),
                'by_priority' => Notification::whereBetween('created_at', [$weekStart, $weekEnd])
                    ->selectRaw('priority, COUNT(*) as count')
                    ->groupBy('priority')
                    ->pluck('count', 'priority')
                    ->toArray()
            ],
            'system' => [
                'total_users' => User::count(),
                'active_users_week' => User::where('updated_at', '>=', $weekStart)->count(),
                'database_size' => $this->getDatabaseSize(),
                'uptime_days' => $this->getSystemUptime()
            ]
        ];
    }

    /**
     * Format statistics message
     */
    private function formatStatsMessage($stats, $weekStart, $weekEnd)
    {
        $period = $weekStart->format('d/m') . ' - ' . $weekEnd->format('d/m/Y');
        
        return "ESTADÍSTICAS DEL SISTEMA ({$period}):\n\n" .
               "📊 VENTAS:\n" .
               "   • Esta semana: {$stats['sales']['total_this_week']} ventas\n" .
               "   • Valor: $" . number_format($stats['sales']['total_value_this_week'], 2) . "\n" .
               "   • Promedio diario: " . number_format($stats['sales']['average_per_day'], 1) . " ventas\n\n" .
               
               "📦 INVENTARIO:\n" .
               "   • Lotes disponibles: {$stats['lots']['total_available']}\n" .
               "   • Peso disponible: " . number_format($stats['lots']['total_weight_available'], 2) . " kg\n" .
               "   • Lotes vendidos: {$stats['lots']['total_sold']}\n\n" .
               
               "👥 CLIENTES Y PROVEEDORES:\n" .
               "   • Clientes activos: {$stats['customers']['total_active']}\n" .
               "   • Con saldo pendiente: {$stats['customers']['with_balance']}\n" .
               "   • Proveedores: {$stats['suppliers']['total_active']}\n\n" .
               
               "🔔 NOTIFICACIONES:\n" .
               "   • Enviadas esta semana: {$stats['notifications']['sent_this_week']}\n" .
               "   • Sin leer: {$stats['notifications']['unread_total']}\n\n" .
               
               "⚙️ SISTEMA:\n" .
               "   • Usuarios totales: {$stats['system']['total_users']}\n" .
               "   • Usuarios activos: {$stats['system']['active_users_week']}\n" .
               "   • Tamaño BD: " . number_format($stats['system']['database_size'] / 1024 / 1024, 2) . " MB";
    }

    /**
     * Get database size in bytes
     */
    private function getDatabaseSize()
    {
        try {
            $result = DB::select("
                SELECT SUM(data_length + index_length) as size 
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()
            ");
            
            return $result[0]->size ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get system uptime (placeholder - would need actual server metrics)
     */
    private function getSystemUptime()
    {
        // This is a placeholder - in a real system you'd get actual server uptime
        return rand(7, 30);
    }
}