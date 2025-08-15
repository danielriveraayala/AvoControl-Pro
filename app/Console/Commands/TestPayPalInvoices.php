<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Http\Controllers\ConfigurationController;

class TestPayPalInvoices extends Command
{
    protected $signature = 'paypal:test-invoices {--user-email=}';
    protected $description = 'Test PayPal invoice retrieval and download functionality';

    public function handle()
    {
        $this->info('🔍 Testing PayPal Invoice System...');
        $this->newLine();

        $userEmail = $this->option('user-email');
        
        if ($userEmail) {
            $user = \App\Models\User::where('email', $userEmail)->first();
            if (!$user) {
                $this->error("❌ User not found: $userEmail");
                return 1;
            }
        } else {
            // Get any user with active subscription
            $subscription = Subscription::with('user')->where('status', 'active')->first();
            if (!$subscription) {
                $this->error('❌ No active subscriptions found');
                return 1;
            }
            $user = $subscription->user;
        }

        $this->info("👤 Testing with user: {$user->email}");
        
        // Get user's current tenant
        if (!$user->current_tenant_id) {
            $this->error('❌ User has no current tenant');
            return 1;
        }

        // Get subscription
        $subscription = Subscription::where('tenant_id', $user->current_tenant_id)
            ->where('status', 'active')
            ->first();

        if (!$subscription) {
            $this->error('❌ No active subscription found for user');
            return 1;
        }

        $this->info("📋 Subscription found:");
        $this->line("   ID: {$subscription->id}");
        $this->line("   Plan: {$subscription->plan}");
        $this->line("   PayPal ID: {$subscription->paypal_subscription_id}");
        $this->line("   Amount: $" . $subscription->amount . " " . ($subscription->currency ?? 'USD'));
        $this->newLine();

        // Test invoice retrieval
        $this->info('📄 Testing invoice retrieval...');
        
        // Simulate authenticated user
        auth()->login($user);
        
        try {
            // Use reflection to call private method
            $controller = new ConfigurationController();
            $reflection = new \ReflectionClass($controller);
            $method = $reflection->getMethod('getPayPalInvoices');
            $method->setAccessible(true);
            
            $invoices = $method->invoke($controller, $subscription->paypal_subscription_id);
            
            if (empty($invoices)) {
                $this->warn('⚠️  No invoices returned');
            } else {
                $this->info("✅ Found " . count($invoices) . " invoice(s):");
                foreach ($invoices as $invoice) {
                    $this->line("   - ID: {$invoice['id']}");
                    $this->line("     Amount: {$invoice['total_amount']['value']} {$invoice['total_amount']['currency_code']}");
                    $this->line("     Status: {$invoice['status']}");
                    $this->line("     Date: {$invoice['create_time']}");
                    $this->newLine();
                }
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Error retrieving invoices: " . $e->getMessage());
            return 1;
        }

        // Test invoice download
        if (!empty($invoices)) {
            $this->info('📥 Testing invoice download...');
            $firstInvoice = $invoices[0];
            
            try {
                // Test download method
                $method = $reflection->getMethod('downloadInvoice');
                $method->setAccessible(true);
                
                $response = $method->invoke($controller, $firstInvoice['id']);
                
                if ($response instanceof \Illuminate\Http\Response) {
                    $this->info("✅ Invoice download successful");
                    $this->line("   Content-Type: " . $response->headers->get('Content-Type'));
                    $this->line("   Content-Disposition: " . $response->headers->get('Content-Disposition'));
                    
                    // Save test PDF
                    $filename = storage_path("app/test_invoice_{$firstInvoice['id']}.pdf");
                    file_put_contents($filename, $response->getContent());
                    $this->line("   Saved test PDF to: $filename");
                } else {
                    $this->warn("⚠️  Unexpected response type: " . get_class($response));
                }
                
            } catch (\Exception $e) {
                $this->error("❌ Error downloading invoice: " . $e->getMessage());
            }
        }

        // Test PayPal API connectivity
        $this->newLine();
        $this->info('🔗 Testing PayPal API connectivity...');
        
        $environment = env('PAYPAL_ENVIRONMENT', 'sandbox');
        $clientId = env('PAYPAL_' . strtoupper($environment) . '_CLIENT_ID');
        $clientSecret = env('PAYPAL_' . strtoupper($environment) . '_CLIENT_SECRET');
        
        $this->line("   Environment: $environment");
        $this->line("   Client ID: " . substr($clientId, 0, 10) . '...');
        
        if (!$clientId || !$clientSecret) {
            $this->error('❌ PayPal credentials not configured');
            return 1;
        }

        try {
            // Test access token
            $method = $reflection->getMethod('getPayPalAccessToken');
            $method->setAccessible(true);
            
            $accessToken = $method->invoke($controller, $environment, $clientId, $clientSecret);
            
            if ($accessToken) {
                $this->info("✅ PayPal API connection successful");
                $this->line("   Token: " . substr($accessToken, 0, 20) . '...');
            } else {
                $this->error('❌ Failed to get PayPal access token');
            }
            
        } catch (\Exception $e) {
            $this->error("❌ PayPal API error: " . $e->getMessage());
        }

        $this->newLine();
        $this->info('🎉 PayPal invoice test completed!');
        
        return 0;
    }
}