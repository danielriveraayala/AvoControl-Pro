<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\Notifications\SystemAlert;
use App\Services\DynamicMailService;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email?} {--type=simple} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email sending functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? 'avocontrol@kreativos.pro';
        $type = $this->option('type');

        $this->info("Testing email sending to: {$email}");
        
        // Get current configuration
        $currentConfig = DynamicMailService::getCurrentConfig();
        
        $this->info("Current SMTP settings:");
        $this->line("- Source: " . $currentConfig['source']);
        $this->line("- Host: " . $currentConfig['config']['mail_host']);
        $this->line("- Port: " . $currentConfig['config']['mail_port']);
        $this->line("- Username: " . $currentConfig['config']['mail_username']);
        $this->line("- Encryption: " . $currentConfig['config']['mail_encryption']);
        $this->line("- From: " . $currentConfig['config']['mail_from_address']);
        $this->line("- Settings in DB: " . ($currentConfig['settings_in_database'] ? 'Yes' : 'No'));

        try {
            if ($this->option('all')) {
                // Test all email templates
                $this->testAllTemplates($email);
            } elseif ($type === 'simple') {
                // Send simple test email using dynamic service
                $result = DynamicMailService::testConnection($email);
                if ($result['success']) {
                    $this->info('✅ Simple test email sent successfully! (from database config)');
                } else {
                    throw new \Exception($result['message']);
                }
            } else {
                // Send notification email using dynamic service
                $alert = new SystemAlert(SystemAlert::prepareSampleData('normal'));
                DynamicMailService::send($email, $alert);
                $this->info('✅ Notification email sent successfully! (from database config)');
            }

        } catch (\Exception $e) {
            $this->error('❌ Failed to send email:');
            $this->error($e->getMessage());
            
            // Additional debugging information
            $this->newLine();
            $this->warn('Debug information:');
            $this->line('- Error type: ' . get_class($e));
            $this->line('- Error code: ' . $e->getCode());
            
            if (str_contains($e->getMessage(), 'authentication')) {
                $this->newLine();
                $this->warn('Authentication error detected. Please check:');
                $this->line('1. Email account exists and is active');
                $this->line('2. Password is correct');
                $this->line('3. SMTP settings are correct for your provider');
                $this->line('4. Account has SMTP access enabled');
                $this->line('5. Try using an app-specific password if using 2FA');
            }
            
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function testAllTemplates($email)
    {
        $this->info('🚀 Testing all email templates...');

        // Import all mail classes
        $mailClasses = [
            'Low Inventory Alert' => \App\Mail\Notifications\LowInventoryAlert::class,
            'Payment Reminder' => \App\Mail\Notifications\PaymentReminder::class,
            'Daily Report' => \App\Mail\Notifications\DailyReport::class,
            'New Lot Received' => \App\Mail\Notifications\NewLotReceived::class,
            'System Alert (Normal)' => [\App\Mail\Notifications\SystemAlert::class, 'normal'],
            'System Alert (Critical)' => [\App\Mail\Notifications\SystemAlert::class, 'critical']
        ];

        foreach ($mailClasses as $name => $mailClass) {
            $this->line("Sending: {$name}...");
            
            try {
                if (is_array($mailClass)) {
                    // For SystemAlert with priority
                    $mailable = new $mailClass[0]($mailClass[0]::prepareSampleData($mailClass[1]));
                } else {
                    // For other mail classes, create with mock data
                    switch ($mailClass) {
                        case \App\Mail\Notifications\LowInventoryAlert::class:
                            $data = $mailClass::prepareData([]);
                            $mailable = new $mailClass($data['lowStockQualities'], $data['summary'], '4 horas');
                            break;
                            
                        case \App\Mail\Notifications\PaymentReminder::class:
                            $data = $mailClass::prepareData([]);
                            $mailable = new $mailClass($data['overduePayments'], $data['upcomingPayments'], $data['summary'], $data['criticalActions'], '9:00 AM');
                            break;
                            
                        case \App\Mail\Notifications\DailyReport::class:
                            $data = $mailClass::prepareData();
                            $mailable = new $mailClass($data, now()->format('d/m/Y'), '8:00 AM');
                            break;
                            
                        case \App\Mail\Notifications\NewLotReceived::class:
                            $data = $mailClass::prepareData();
                            $mailable = new $mailClass($data['lotData'], $data['inventoryImpact'], $data['priceAnalysis'], 50000);
                            break;
                            
                        default:
                            $mailable = new $mailClass();
                            break;
                    }
                }
                
                DynamicMailService::send($email, $mailable);
                $this->info("  ✅ {$name} sent successfully");
                
                // Small delay between emails to avoid overwhelming
                sleep(1);
                
            } catch (\Exception $e) {
                $this->error("  ❌ {$name} failed: " . $e->getMessage());
            }
        }
        
        $this->info('🎉 All template tests completed!');
    }
}
