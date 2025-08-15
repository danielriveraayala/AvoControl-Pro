<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vapid:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate VAPID keys for push notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating VAPID keys for push notifications...');
        
        try {
            $vapidKeys = VAPID::createVapidKeys();
            
            $this->line('');
            $this->line('VAPID keys generated successfully!');
            $this->line('');
            $this->line('Add these to your .env file:');
            $this->line('');
            $this->info('VAPID_PUBLIC_KEY=' . $vapidKeys['publicKey']);
            $this->info('VAPID_PRIVATE_KEY=' . $vapidKeys['privateKey']);
            $this->info('VAPID_SUBJECT=mailto:avocontrol@kreativos.pro');
            $this->line('');
            $this->warn('Important: Keep the private key secure and never expose it publicly!');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Error generating VAPID keys: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
