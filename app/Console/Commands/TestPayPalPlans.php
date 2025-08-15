<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SubscriptionPlan;
use App\Services\PayPalService;

class TestPayPalPlans extends Command
{
    protected $signature = 'paypal:test-plans';
    protected $description = 'Test PayPal plan synchronization status';

    public function handle()
    {
        $this->info('=== PayPal Plan Synchronization Test ===');
        $this->newLine();
        
        // Check PayPal configuration
        $mode = config('services.paypal.mode', 'sandbox');
        $clientId = config('services.paypal.client_id');
        
        $this->info("PayPal Mode: " . ($mode ?: 'sandbox (default)'));
        $this->info("Client ID: " . substr($clientId, 0, 20) . '...');
        $this->newLine();
        
        // Test PayPal connection
        $paypalService = new PayPalService();
        $this->info('Testing PayPal connection...');
        $result = $paypalService->testConnection();
        
        if ($result['success']) {
            $this->info('✅ PayPal connection successful!');
        } else {
            $this->error('❌ PayPal connection failed: ' . $result['error']);
        }
        $this->newLine();
        
        // Check plans
        $this->info('=== Subscription Plans Status ===');
        $plans = SubscriptionPlan::all();
        
        $headers = ['Plan', 'Price', 'Annual Price', 'Monthly Sync', 'Annual Sync'];
        $rows = [];
        
        foreach ($plans as $plan) {
            $rows[] = [
                $plan->name,
                '$' . number_format($plan->price, 2),
                $plan->annual_price ? '$' . number_format($plan->annual_price, 2) : 'N/A',
                $plan->paypal_plan_id ? '✅ ' . substr($plan->paypal_plan_id, 0, 15) . '...' : '❌ Not synced',
                $plan->paypal_annual_plan_id ? '✅ ' . substr($plan->paypal_annual_plan_id, 0, 15) . '...' : ($plan->annual_price ? '❌ Not synced' : 'N/A')
            ];
        }
        
        $this->table($headers, $rows);
        $this->newLine();
        
        // Summary
        $totalPlans = $plans->count();
        $monthlySync = $plans->whereNotNull('paypal_plan_id')->count();
        $annualPlans = $plans->where('annual_price', '>', 0)->count();
        $annualSync = $plans->whereNotNull('paypal_annual_plan_id')->count();
        
        $this->info('=== Summary ===');
        $this->info("Total Plans: $totalPlans");
        $this->info("Monthly Plans Synced: $monthlySync / $totalPlans");
        $this->info("Annual Plans Synced: $annualSync / $annualPlans");
        
        if ($monthlySync < $totalPlans || $annualSync < $annualPlans) {
            $this->newLine();
            $this->warn('⚠️  Some plans are not fully synchronized with PayPal.');
            $this->warn('   Go to /developer/plans to sync missing plans.');
        } else {
            $this->newLine();
            $this->info('✅ All plans are fully synchronized with PayPal!');
        }
        
        return Command::SUCCESS;
    }
}