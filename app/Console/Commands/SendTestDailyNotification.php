<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;

class SendTestDailyNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:test-daily {--type=all : Type of notification (email, push, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily test notifications to all users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $type = $this->option('type');
            $now = Carbon::now();
            $timeOfDay = $now->hour < 12 ? 'Buenos días' : ($now->hour < 18 ? 'Buenas tardes' : 'Buenas noches');
            
            // Get all active users (super_admin and admin users)
            $users = User::whereIn('role', ['super_admin', 'admin'])->get();
            
            if ($users->isEmpty()) {
                $this->error('No users found to send notifications to.');
                return 1;
            }

            $notificationsSent = 0;

            foreach ($users as $user) {
                // Create notification
                $notification = Notification::create([
                    'type' => 'test_daily',
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->id,
                    'data' => [
                        'title' => "🌟 {$timeOfDay} - Notificación de Prueba",
                        'message' => "Sistema AvoControl Pro funcionando correctamente. Hora: {$now->format('H:i')} - Usuario: {$user->name}",
                        'action_url' => route('dashboard'),
                        'action_text' => 'Ver Dashboard'
                    ],
                    'priority' => 'normal',
                    'channels' => $type === 'all' ? ['email', 'push', 'database'] : [$type],
                    'category' => 'system',
                    'metadata' => [
                        'test_notification' => true,
                        'sent_at' => $now->toISOString(),
                        'command_run' => 'notifications:test-daily'
                    ]
                ]);

                $notificationsSent++;
                $this->info("Notification sent to: {$user->name} ({$user->email})");
            }

            $this->info("✅ Successfully sent {$notificationsSent} test notifications via {$type}");
            
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error sending test notifications: " . $e->getMessage());
            return 1;
        }
    }
}
