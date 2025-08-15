<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Models\User;
use App\Models\QualityGrade;
use App\Models\Lot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CheckInventoryLevelsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:check-inventory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check inventory levels and send low stock alerts via email, push and database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('🔍 Checking inventory levels...');
            
            // Get low inventory items (≤20% threshold)
            $lowInventoryItems = $this->getLowInventoryItems();
            
            if ($lowInventoryItems->isEmpty()) {
                $this->info('✅ All inventory levels are healthy');
                return 0;
            }

            // Get users who should receive inventory alerts (admin and super_admin)
            $users = User::whereIn('role', ['super_admin', 'admin'])->get();
            
            if ($users->isEmpty()) {
                $this->error('❌ No admin users found to send notifications');
                return 1;
            }

            $notificationsSent = 0;
            
            foreach ($lowInventoryItems as $item) {
                $qualityName = $item->quality_name ?: 'Sin Calidad';
                $availableKg = number_format($item->available_weight, 2);
                $totalKg = number_format($item->total_weight, 2);
                $percentage = round($item->percentage_available, 1);
                
                foreach ($users as $user) {
                    $notification = Notification::create([
                        'type' => 'inventory_low',
                        'notifiable_type' => User::class,
                        'notifiable_id' => $user->id,
                        'data' => [
                            'title' => "⚠️ Inventario Bajo: {$qualityName}",
                            'message' => "Solo queda el {$percentage}% del inventario total ({$availableKg} kg de {$totalKg} kg disponibles). Considera reabastecer pronto.",
                            'action_url' => route('lots.index'),
                            'action_text' => 'Ver Inventario',
                            'quality' => $qualityName,
                            'available_kg' => $availableKg,
                            'total_kg' => $totalKg,
                            'percentage' => $percentage
                        ],
                        'priority' => $percentage <= 10 ? 'critical' : ($percentage <= 15 ? 'high' : 'normal'),
                        'channels' => ['email', 'push', 'database'],
                        'category' => 'inventory',
                        'metadata' => [
                            'quality_id' => $item->quality_id,
                            'threshold_type' => 'low_stock',
                            'checked_at' => Carbon::now()->toISOString()
                        ]
                    ]);
                    
                    $notificationsSent++;
                }
                
                $this->warn("⚠️  Low inventory: {$qualityName} - {$percentage}% remaining");
            }

            $this->info("✅ Sent {$notificationsSent} inventory alerts to " . $users->count() . " users via email, push and database");
            
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error checking inventory levels: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Get inventory items that are below the 20% threshold
     */
    private function getLowInventoryItems()
    {
        return DB::table('lots')
            ->leftJoin('quality_grades', 'lots.quality_id', '=', 'quality_grades.id')
            ->select([
                'lots.quality_id',
                'quality_grades.name as quality_name',
                DB::raw('SUM(CASE WHEN lots.status = "disponible" THEN lots.peso_restante ELSE 0 END) as available_weight'),
                DB::raw('SUM(lots.peso_inicial) as total_weight'),
                DB::raw('ROUND((SUM(CASE WHEN lots.status = "disponible" THEN lots.peso_restante ELSE 0 END) / SUM(lots.peso_inicial)) * 100, 2) as percentage_available')
            ])
            ->where('lots.deleted_at', null)
            ->groupBy('lots.quality_id', 'quality_grades.name')
            ->havingRaw('percentage_available <= 20 AND percentage_available > 0')
            ->orderBy('percentage_available', 'asc')
            ->get();
    }
}