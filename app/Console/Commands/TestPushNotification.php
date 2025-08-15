<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PushNotificationService;

class TestPushNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'push:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test push notification system configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing push notification system configuration...');
        
        try {
            // Test if we can create the service without errors
            $pushService = new PushNotificationService();
            
            $this->info('✅ PushNotificationService created successfully');
            
            // Test VAPID keys format
            $publicKey = env('VAPID_PUBLIC_KEY');
            $privateKey = env('VAPID_PRIVATE_KEY');
            $subject = env('VAPID_SUBJECT');
            
            if (empty($publicKey)) {
                $this->error('❌ VAPID_PUBLIC_KEY is not set in .env');
                return Command::FAILURE;
            }
            
            if (empty($privateKey)) {
                $this->error('❌ VAPID_PRIVATE_KEY is not set in .env');
                return Command::FAILURE;
            }
            
            if (empty($subject)) {
                $this->error('❌ VAPID_SUBJECT is not set in .env');
                return Command::FAILURE;
            }
            
            $this->info('✅ All VAPID environment variables are set');
            $this->line('  - Public Key: ' . substr($publicKey, 0, 20) . '...');
            $this->line('  - Private Key: ' . substr($privateKey, 0, 20) . '...');
            $this->line('  - Subject: ' . $subject);
            
            // Test push service statistics
            $stats = $pushService->getStats();
            $this->info('✅ Push service statistics loaded');
            $this->line('  - Total subscriptions: ' . $stats['total_subscriptions']);
            $this->line('  - Active subscriptions: ' . $stats['active_subscriptions']);
            
            $this->line('');
            $this->info('🎉 Push notification system is configured correctly!');
            $this->line('');
            $this->line('Next steps:');
            $this->line('1. Visit the Configuration page in your browser');
            $this->line('2. Go to the Push Notifications tab');
            $this->line('3. Click "Activar Notificaciones" to subscribe');
            $this->line('4. Test sending notifications');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Error testing push notification system:');
            $this->error('   ' . $e->getMessage());
            $this->line('');
            $this->warn('Possible solutions:');
            $this->line('1. Check that all VAPID keys are set in .env file');
            $this->line('2. Verify the database is accessible');
            $this->line('3. Run: php artisan migrate to ensure tables exist');
            
            return Command::FAILURE;
        }
    }
}
