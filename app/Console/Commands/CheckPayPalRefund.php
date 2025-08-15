<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Services\PayPalService;
use Carbon\Carbon;

class CheckPayPalRefund extends Command
{
    protected $signature = 'paypal:check-refund 
                            {subscription-id : The subscription ID to check}
                            {--force : Force check even if subscription is not in PayPal}';

    protected $description = 'Check PayPal for refunds and process them automatically';

    protected $paypalService;

    public function __construct(PayPalService $paypalService)
    {
        parent::__construct();
        $this->paypalService = $paypalService;
    }

    public function handle()
    {
        $subscriptionId = $this->argument('subscription-id');
        $force = $this->option('force');
        
        $subscription = Subscription::find($subscriptionId);
        
        if (!$subscription) {
            $this->error("Subscription #{$subscriptionId} not found");
            return 1;
        }
        
        $this->info("Checking subscription #{$subscriptionId}");
        $this->info("PayPal ID: {$subscription->paypal_subscription_id}");
        $this->info("Current Status: {$subscription->status}");
        $this->info("Plan: {$subscription->plan} - \${$subscription->amount}");
        $this->newLine();
        
        // Check if it's a test subscription
        if (str_starts_with($subscription->paypal_subscription_id, 'TEST-') || 
            str_starts_with($subscription->paypal_subscription_id, 'MOCK-')) {
            $this->warn("This appears to be a test subscription");
            if (!$force) {
                $this->info("Use --force to process anyway");
                return 0;
            }
        }
        
        // Try to get subscription from PayPal
        $this->info("Fetching subscription details from PayPal...");
        $response = $this->paypalService->getSubscription($subscription->paypal_subscription_id);
        
        if (!$response['success']) {
            $this->error("Failed to get PayPal subscription: " . ($response['error'] ?? 'Unknown error'));
            
            // If subscription not found in PayPal and we have a refund record, process it
            if (strpos($response['error'] ?? '', 'RESOURCE_NOT_FOUND') !== false || $force) {
                $this->warn("Subscription not found in PayPal - checking for local refund records...");
                
                // Check if we have a refund payment record
                $refundPayment = $subscription->payments()
                    ->where('type', 'refund')
                    ->where('amount', '<', 0)
                    ->first();
                    
                if ($refundPayment) {
                    $this->info("Found refund record:");
                    $this->info("  - Amount: \${$refundPayment->amount}");
                    $this->info("  - Date: {$refundPayment->payment_date}");
                    $this->info("  - Reason: {$refundPayment->failure_reason}");
                    
                    if ($subscription->status === 'active') {
                        $this->warn("Subscription is still active but has a refund - suspending...");
                        
                        $subscription->update([
                            'status' => 'suspended',
                            'suspended_at' => Carbon::now(),
                            'suspension_reason' => "Payment refunded: " . ($refundPayment->failure_reason ?? 'Refund processed'),
                            'suspended_by' => 'paypal-refund-check'
                        ]);
                        
                        $this->info("✅ Subscription suspended due to refund");
                        
                        // Also suspend the tenant if exists
                        if ($subscription->tenant) {
                            $subscription->tenant->update([
                                'status' => 'suspended'
                            ]);
                            $this->info("✅ Tenant also suspended");
                        }
                    } else {
                        $this->info("Subscription already in {$subscription->status} status");
                    }
                } else {
                    $this->info("No refund records found locally");
                }
            }
            return 1;
        }
        
        // Check PayPal subscription status
        $paypalData = $response['data'];
        $paypalStatus = strtolower($paypalData['status'] ?? '');
        
        $this->info("PayPal Status: {$paypalStatus}");
        
        // Check for transactions in the last 30 days
        $this->info("Checking for recent transactions...");
        $transactionsResponse = $this->paypalService->getSubscriptionTransactions(
            $subscription->paypal_subscription_id,
            Carbon::now()->subDays(30)->toIso8601String(),
            Carbon::now()->toIso8601String()
        );
        
        if ($transactionsResponse['success'] && isset($transactionsResponse['data']['transactions'])) {
            $transactions = $transactionsResponse['data']['transactions'];
            $this->info("Found " . count($transactions) . " transaction(s)");
            
            foreach ($transactions as $transaction) {
                $status = $transaction['status'] ?? '';
                $amount = $transaction['amount_with_breakdown']['gross_amount']['value'] ?? 0;
                $date = Carbon::parse($transaction['time'] ?? now());
                
                $this->info("  - {$date->format('Y-m-d H:i:s')}: \${$amount} - Status: {$status}");
                
                if (in_array($status, ['REFUNDED', 'PARTIALLY_REFUNDED', 'REVERSED'])) {
                    $this->warn("    ⚠️ REFUND DETECTED!");
                    
                    if ($subscription->status === 'active') {
                        $subscription->update([
                            'status' => 'suspended',
                            'suspended_at' => Carbon::now(),
                            'suspension_reason' => "Payment refunded on {$date->format('Y-m-d')}: \${$amount}",
                            'suspended_by' => 'paypal-refund-check'
                        ]);
                        
                        $this->info("✅ Subscription suspended due to refund");
                        
                        // Also suspend the tenant
                        if ($subscription->tenant) {
                            $subscription->tenant->update([
                                'status' => 'suspended'
                            ]);
                            $this->info("✅ Tenant also suspended");
                        }
                    }
                    break;
                }
            }
        } else {
            $this->warn("Could not retrieve transactions");
        }
        
        $this->newLine();
        $this->info("Final subscription status: {$subscription->fresh()->status}");
        
        return 0;
    }
}