<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;

class CreateTestNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:create-test {user? : User ID or email to send to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test notification for the UI';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get user
        $userIdentifier = $this->argument('user');
        
        if ($userIdentifier) {
            $user = is_numeric($userIdentifier) 
                ? User::find($userIdentifier)
                : User::where('email', $userIdentifier)->first();
        } else {
            $user = User::first();
        }

        if (!$user) {
            $this->error('No user found!');
            return 1;
        }

        // Create notification
        $notification = Notification::create([
            'id' => \Str::uuid(),
            'type' => 'test_daily',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => [
                'title' => '🔔 Notificación de Prueba UI',
                'message' => 'Esta es una notificación de prueba para verificar el sistema de UI. Creada a las ' . Carbon::now()->format('H:i:s'),
                'action_url' => route('dashboard'),
                'action_text' => 'Ir al Dashboard'
            ],
            'priority' => 'normal',
            'channel' => 'all',
            'category' => 'system',
            'metadata' => [
                'test_notification' => true,
                'created_via' => 'artisan command',
                'command' => 'notifications:create-test'
            ]
        ]);

        $this->info("✅ Test notification created for {$user->name} ({$user->email})");
        $this->info("Notification ID: {$notification->id}");
        
        return 0;
    }
}
