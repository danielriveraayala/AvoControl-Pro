<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PayPalService;
use Illuminate\Support\Facades\Config;

class DiagnosePayPalConfig extends Command
{
    protected $signature = 'paypal:diagnose-config';
    protected $description = 'Diagnose PayPal configuration differences between CLI and web contexts';

    public function handle()
    {
        $this->info('🔍 PayPal Configuration Diagnostics');
        $this->line('=====================================');
        $this->newLine();

        // 1. Environment Variables Direct Access
        $this->info('1. Direct Environment Variables:');
        $this->table(['Variable', 'Value'], [
            ['PAYPAL_ENVIRONMENT', env('PAYPAL_ENVIRONMENT', 'NOT SET')],
            ['PAYPAL_SANDBOX_CLIENT_ID', env('PAYPAL_SANDBOX_CLIENT_ID') ? substr(env('PAYPAL_SANDBOX_CLIENT_ID'), 0, 20) . '...' : 'NOT SET'],
            ['PAYPAL_SANDBOX_CLIENT_SECRET', env('PAYPAL_SANDBOX_CLIENT_SECRET') ? 'SET (' . strlen(env('PAYPAL_SANDBOX_CLIENT_SECRET')) . ' chars)' : 'NOT SET'],
            ['PAYPAL_LIVE_CLIENT_ID', env('PAYPAL_LIVE_CLIENT_ID') ? substr(env('PAYPAL_LIVE_CLIENT_ID'), 0, 20) . '...' : 'NOT SET'],
            ['PAYPAL_LIVE_CLIENT_SECRET', env('PAYPAL_LIVE_CLIENT_SECRET') ? 'SET (' . strlen(env('PAYPAL_LIVE_CLIENT_SECRET')) . ' chars)' : 'NOT SET'],
        ]);
        $this->newLine();

        // 2. Laravel Config Values
        $this->info('2. Laravel Config Values:');
        $this->table(['Config Key', 'Value'], [
            ['paypal.mode', config('paypal.mode', 'NOT SET')],
            ['paypal.sandbox.client_id', config('paypal.sandbox.client_id') ? substr(config('paypal.sandbox.client_id'), 0, 20) . '...' : 'NOT SET'],
            ['paypal.sandbox.client_secret', config('paypal.sandbox.client_secret') ? 'SET (' . strlen(config('paypal.sandbox.client_secret')) . ' chars)' : 'NOT SET'],
            ['paypal.live.client_id', config('paypal.live.client_id') ? substr(config('paypal.live.client_id'), 0, 20) . '...' : 'NOT SET'],
            ['paypal.live.client_secret', config('paypal.live.client_secret') ? 'SET (' . strlen(config('paypal.live.client_secret')) . ' chars)' : 'NOT SET'],
        ]);
        $this->newLine();

        // 3. PayPal Service Configuration
        $this->info('3. PayPal Service Internal Configuration:');
        try {
            $paypalService = app(PayPalService::class);
            $serviceConfig = $paypalService->getConfig();
            
            $this->table(['Service Config', 'Value'], [
                ['mode', $serviceConfig['mode'] ?? 'NOT SET'],
                ['client_id', $serviceConfig['client_id'] ? substr($serviceConfig['client_id'], 0, 20) . '...' : 'NOT SET'],
                ['base_url', $serviceConfig['base_url'] ?? 'NOT SET'],
            ]);
        } catch (\Exception $e) {
            $this->error('Failed to get PayPal service config: ' . $e->getMessage());
        }
        $this->newLine();

        // 4. Config Cache Status
        $this->info('4. Configuration Cache Status:');
        $configCached = file_exists(base_path('bootstrap/cache/config.php'));
        $this->line('Config cached: ' . ($configCached ? 'YES' : 'NO'));
        
        if ($configCached) {
            $this->warn('⚠️  Configuration is cached. Web requests use cached values.');
            $this->line('Cache file: ' . base_path('bootstrap/cache/config.php'));
            $this->line('Last modified: ' . date('Y-m-d H:i:s', filemtime(base_path('bootstrap/cache/config.php'))));
        } else {
            $this->info('✅ Configuration is not cached. Using live .env values.');
        }
        $this->newLine();

        // 5. Test Direct Token Acquisition
        $this->info('5. Direct Token Test (CLI Context):');
        $environment = env('PAYPAL_ENVIRONMENT', 'sandbox');
        $clientId = $environment === 'sandbox' 
            ? env('PAYPAL_SANDBOX_CLIENT_ID') 
            : env('PAYPAL_LIVE_CLIENT_ID');
        $clientSecret = $environment === 'sandbox' 
            ? env('PAYPAL_SANDBOX_CLIENT_SECRET') 
            : env('PAYPAL_LIVE_CLIENT_SECRET');

        if ($clientId && $clientSecret) {
            try {
                $this->line('Attempting direct token acquisition...');
                $apiUrl = $environment === 'sandbox' 
                    ? 'https://api-m.sandbox.paypal.com'
                    : 'https://api-m.paypal.com';

                $client = new \GuzzleHttp\Client();
                $response = $client->post($apiUrl . '/v1/oauth2/token', [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Accept-Language' => 'en_US',
                    ],
                    'auth' => [$clientId, $clientSecret],
                    'form_params' => [
                        'grant_type' => 'client_credentials'
                    ]
                ]);

                $data = json_decode($response->getBody()->getContents(), true);
                $this->info('✅ Token acquired successfully in CLI context');
                $this->line('Token type: ' . $data['token_type']);
                $this->line('Expires in: ' . $data['expires_in'] . ' seconds');
            } catch (\Exception $e) {
                $this->error('❌ Failed to acquire token in CLI context: ' . $e->getMessage());
            }
        } else {
            $this->error('❌ Missing credentials for direct test');
        }
        $this->newLine();

        // 6. File System Checks
        $this->info('6. File System Checks:');
        $envPath = base_path('.env');
        $this->line('.env file exists: ' . (file_exists($envPath) ? 'YES' : 'NO'));
        $this->line('.env file readable: ' . (is_readable($envPath) ? 'YES' : 'NO'));
        $this->line('.env file size: ' . (file_exists($envPath) ? filesize($envPath) . ' bytes' : 'N/A'));
        $this->line('.env last modified: ' . (file_exists($envPath) ? date('Y-m-d H:i:s', filemtime($envPath)) : 'N/A'));
        $this->newLine();

        // 7. Recommendations
        $this->info('7. Recommendations:');
        if ($configCached) {
            $this->warn('• Clear config cache: php artisan config:clear');
        }
        $this->line('• Verify .env file permissions and content');
        $this->line('• Check web server environment variable access');
        $this->line('• Compare this output with web context results');

        return 0;
    }
}