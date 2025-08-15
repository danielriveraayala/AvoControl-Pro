<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SubscriptionPlan;
use App\Services\PayPalService;
use Illuminate\Support\Facades\Log;

class DebugPayPalSync extends Command
{
    protected $signature = 'paypal:debug-sync {plan_key}';
    protected $description = 'Debug PayPal plan synchronization to check prices';

    public function handle()
    {
        $planKey = $this->argument('plan_key');
        $plan = SubscriptionPlan::where('key', $planKey)->first();
        
        if (!$plan) {
            $this->error("Plan with key '{$planKey}' not found");
            return Command::FAILURE;
        }
        
        $this->info("=== Debugging Plan: {$plan->name} ===");
        $this->info("Monthly Price: $" . number_format($plan->price, 2));
        $this->info("Annual Price: $" . number_format($plan->annual_price ?: 0, 2));
        $this->newLine();
        
        // Test what would be sent for monthly plan
        $this->info("=== MONTHLY PLAN DATA ===");
        $monthlyPrice = $plan->price;
        $this->info("Price to be sent: $" . number_format($monthlyPrice, 2));
        $this->info("Billing Cycle: MONTH");
        $this->info("Plan Name: {$plan->name} (Mensual)");
        
        // Show exact payload
        $monthlyPayload = [
            'price' => number_format($monthlyPrice, 2, '.', ''),
            'currency' => $plan->currency,
            'interval_unit' => 'MONTH',
            'interval_count' => 1
        ];
        $this->info("PayPal Payload: " . json_encode($monthlyPayload, JSON_PRETTY_PRINT));
        $this->newLine();
        
        // Test what would be sent for annual plan
        if ($plan->annual_price && $plan->annual_price > 0) {
            $this->info("=== ANNUAL PLAN DATA ===");
            $annualPrice = $plan->annual_price;
            $this->info("Price to be sent: $" . number_format($annualPrice, 2));
            $this->info("Billing Cycle: YEAR");
            $this->info("Plan Name: {$plan->name} (Anual)");
            
            // Show exact payload
            $annualPayload = [
                'price' => number_format($annualPrice, 2, '.', ''),
                'currency' => $plan->currency,
                'interval_unit' => 'YEAR',
                'interval_count' => 1
            ];
            $this->info("PayPal Payload: " . json_encode($annualPayload, JSON_PRETTY_PRINT));
        } else {
            $this->warn("No annual pricing configured for this plan");
        }
        
        $this->newLine();
        
        // Test actual sync (dry run)
        $this->info("=== TESTING ACTUAL SYNC (DRY RUN) ===");
        
        $paypalService = new PayPalService();
        
        // Hook into the service to see what's being sent
        $this->info("Testing monthly sync...");
        $monthlyData = $this->simulateSync($plan, 'monthly');
        $this->info("Monthly price that would be sent: $" . $monthlyData['price']);
        
        if ($plan->annual_price > 0) {
            $this->info("Testing annual sync...");
            $annualData = $this->simulateSync($plan, 'yearly');
            $this->info("Annual price that would be sent: $" . $annualData['price']);
        }
        
        return Command::SUCCESS;
    }
    
    private function simulateSync($plan, $billingCycle)
    {
        // Simulate what createSubscriptionPlan does
        $price = $billingCycle === 'yearly' && $plan->annual_price ? $plan->annual_price : $plan->price;
        $planName = $plan->name . ($billingCycle === 'yearly' ? ' (Anual)' : ' (Mensual)');
        
        return [
            'price' => number_format($price, 2, '.', ''),
            'name' => $planName,
            'interval_unit' => $billingCycle === 'yearly' ? 'YEAR' : 'MONTH'
        ];
    }
}