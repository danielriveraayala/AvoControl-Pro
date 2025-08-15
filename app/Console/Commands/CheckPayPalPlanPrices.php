<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SubscriptionPlan;
use App\Services\PayPalService;

class CheckPayPalPlanPrices extends Command
{
    protected $signature = 'paypal:check-prices {plan_key}';
    protected $description = 'Check actual prices of plans in PayPal';

    public function handle()
    {
        $planKey = $this->argument('plan_key');
        $plan = SubscriptionPlan::where('key', $planKey)->first();
        
        if (!$plan) {
            $this->error("Plan with key '{$planKey}' not found");
            return Command::FAILURE;
        }
        
        $this->info("=== Checking PayPal Plan Prices for: {$plan->name} ===");
        $this->info("Local DB Monthly Price: $" . number_format($plan->price, 2));
        $this->info("Local DB Annual Price: $" . number_format($plan->annual_price ?: 0, 2));
        $this->newLine();
        
        $paypalService = new PayPalService();
        
        // Check monthly plan
        if ($plan->paypal_plan_id) {
            $this->info("Checking Monthly Plan: {$plan->paypal_plan_id}");
            $result = $paypalService->getPlanDetails($plan->paypal_plan_id);
            
            if ($result['success']) {
                $billingCycles = $result['data']['billing_cycles'] ?? [];
                foreach ($billingCycles as $cycle) {
                    if ($cycle['tenure_type'] === 'REGULAR') {
                        $price = $cycle['pricing_scheme']['fixed_price']['value'] ?? 'N/A';
                        $currency = $cycle['pricing_scheme']['fixed_price']['currency_code'] ?? 'USD';
                        $interval = $cycle['frequency']['interval_unit'] ?? 'N/A';
                        $this->info("  PayPal Price: \${$price} {$currency} per {$interval}");
                        
                        if ($price != $plan->price) {
                            $this->warn("  ⚠️ Price mismatch! Expected: $" . number_format($plan->price, 2));
                        } else {
                            $this->info("  ✅ Price matches!");
                        }
                    }
                }
            } else {
                $this->error("  Failed to get plan details: " . $result['error']);
            }
        } else {
            $this->warn("  No monthly PayPal plan ID");
        }
        
        $this->newLine();
        
        // Check annual plan
        if ($plan->paypal_annual_plan_id) {
            $this->info("Checking Annual Plan: {$plan->paypal_annual_plan_id}");
            $result = $paypalService->getPlanDetails($plan->paypal_annual_plan_id);
            
            if ($result['success']) {
                $billingCycles = $result['data']['billing_cycles'] ?? [];
                foreach ($billingCycles as $cycle) {
                    if ($cycle['tenure_type'] === 'REGULAR') {
                        $price = $cycle['pricing_scheme']['fixed_price']['value'] ?? 'N/A';
                        $currency = $cycle['pricing_scheme']['fixed_price']['currency_code'] ?? 'USD';
                        $interval = $cycle['frequency']['interval_unit'] ?? 'N/A';
                        $this->info("  PayPal Price: \${$price} {$currency} per {$interval}");
                        
                        $expectedPrice = number_format($plan->annual_price, 2, '.', '');
                        if ($price != $expectedPrice) {
                            $this->warn("  ⚠️ Price mismatch! Expected: $" . $expectedPrice);
                            $this->warn("  The annual plan in PayPal has the wrong price!");
                        } else {
                            $this->info("  ✅ Price matches!");
                        }
                    }
                }
            } else {
                $this->error("  Failed to get plan details: " . $result['error']);
            }
        } else {
            $this->warn("  No annual PayPal plan ID");
        }
        
        $this->newLine();
        $this->info("=== Recommendation ===");
        $this->info("If prices don't match, you should:");
        $this->info("1. Unsync the plan from PayPal (delete button in /developer/plans)");
        $this->info("2. Re-sync the plan to create new plans with correct prices");
        
        return Command::SUCCESS;
    }
}