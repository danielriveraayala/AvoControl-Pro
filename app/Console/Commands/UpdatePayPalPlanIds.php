<?php

namespace App\Console\Commands;

use App\Models\SubscriptionPlan;
use Illuminate\Console\Command;

class UpdatePayPalPlanIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plans:update-paypal-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update subscription plans with PayPal Plan IDs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $plans = [
            ['key' => 'basic', 'paypal_plan_id' => env('PAYPAL_BASIC_PLAN_ID')],
            ['key' => 'premium', 'paypal_plan_id' => env('PAYPAL_PREMIUM_PLAN_ID')],
            ['key' => 'enterprise', 'paypal_plan_id' => env('PAYPAL_ENTERPRISE_PLAN_ID')],
            ['key' => 'corporate', 'paypal_plan_id' => env('PAYPAL_CORPORATE_PLAN_ID')],
        ];

        $updated = 0;
        
        foreach ($plans as $planData) {
            if (empty($planData['paypal_plan_id'])) {
                $this->warn("Skipping {$planData['key']} - no PayPal Plan ID in environment");
                continue;
            }
            
            $plan = SubscriptionPlan::where('key', $planData['key'])->first();
            
            if ($plan) {
                $plan->paypal_plan_id = $planData['paypal_plan_id'];
                $plan->save();
                $this->info("Updated {$plan->key} with PayPal ID: {$planData['paypal_plan_id']}");
                $updated++;
            } else {
                $this->error("Plan with key '{$planData['key']}' not found");
            }
        }
        
        $this->info("Updated {$updated} plans with PayPal IDs");
        
        return 0;
    }
}