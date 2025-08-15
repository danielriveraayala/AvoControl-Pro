<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Services\PayPalService;
use Carbon\Carbon;

class SyncSubscriptionWithPayPal extends Command
{
    protected $signature = 'subscription:sync-paypal 
                            {--subscription-id= : Specific subscription ID to sync}
                            {--all : Sync all active subscriptions}
                            {--dry-run : Run without making changes}';

    protected $description = 'Synchronize subscription status with PayPal to detect refunds and status changes';

    protected $paypalService;

    public function __construct(PayPalService $paypalService)
    {
        parent::__construct();
        $this->paypalService = $paypalService;
    }

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $subscriptionId = $this->option('subscription-id');
        $syncAll = $this->option('all');

        if (!$subscriptionId && !$syncAll) {
            $this->error('Please specify either --subscription-id or --all');
            return 1;
        }

        $this->info('Starting PayPal subscription synchronization...');
        if ($isDryRun) {
            $this->warn('Running in DRY-RUN mode - no changes will be made');
        }

        // Get subscriptions to sync
        $query = Subscription::where('status', 'active')
                            ->whereNotNull('paypal_subscription_id');

        if ($subscriptionId) {
            $query->where('id', $subscriptionId);
        }

        $subscriptions = $query->get();

        if ($subscriptions->isEmpty()) {
            $this->warn('No active subscriptions found to sync');
            return 0;
        }

        $this->info("Found {$subscriptions->count()} subscription(s) to sync");
        $this->newLine();

        $synced = 0;
        $suspended = 0;
        $errors = 0;

        foreach ($subscriptions as $subscription) {
            $this->info("Checking subscription #{$subscription->id} - PayPal ID: {$subscription->paypal_subscription_id}");
            
            // Skip test subscriptions
            if (str_starts_with($subscription->paypal_subscription_id, 'TEST-')) {
                $this->warn("  ⏩ Skipping test subscription");
                continue;
            }

            try {
                // Get subscription details from PayPal
                $response = $this->paypalService->getSubscription($subscription->paypal_subscription_id);
                
                if (!$response['success']) {
                    $this->error("  ❌ Failed to get PayPal subscription: " . ($response['error'] ?? 'Unknown error'));
                    $errors++;
                    continue;
                }

                $paypalData = $response['data'];
                $paypalStatus = strtolower($paypalData['status'] ?? '');
                
                $this->info("  📊 PayPal Status: {$paypalStatus}");
                $this->info("  📊 Local Status: {$subscription->status}");

                // Check for recent transactions including refunds
                $transactionsResponse = $this->paypalService->getSubscriptionTransactions(
                    $subscription->paypal_subscription_id,
                    Carbon::now()->subDays(30)->toIso8601String(),
                    Carbon::now()->toIso8601String()
                );

                if ($transactionsResponse['success'] && isset($transactionsResponse['data']['transactions'])) {
                    $transactions = $transactionsResponse['data']['transactions'];
                    $refundFound = false;
                    
                    foreach ($transactions as $transaction) {
                        if (in_array($transaction['status'] ?? '', ['REFUNDED', 'PARTIALLY_REFUNDED', 'REVERSED'])) {
                            $refundFound = true;
                            $refundAmount = $transaction['amount_with_breakdown']['gross_amount']['value'] ?? 0;
                            $refundDate = Carbon::parse($transaction['time'] ?? now());
                            
                            $this->warn("  💸 REFUND DETECTED: \${$refundAmount} on {$refundDate->format('Y-m-d H:i:s')}");
                            
                            if (!$isDryRun) {
                                // Suspend the subscription due to refund
                                $subscription->update([
                                    'status' => 'suspended',
                                    'suspended_at' => Carbon::now(),
                                    'suspension_reason' => "Payment refunded on {$refundDate->format('Y-m-d')}: \${$refundAmount}",
                                    'suspended_by' => 'paypal-sync-command'
                                ]);
                                
                                $this->info("  ✅ Subscription suspended due to refund");
                                $suspended++;
                            } else {
                                $this->warn("  [DRY-RUN] Would suspend subscription due to refund");
                            }
                            break;
                        }
                    }
                }

                // Sync status if different and no refund found
                if (!isset($refundFound) || !$refundFound) {
                    $localStatus = $this->mapPayPalStatusToLocal($paypalStatus);
                    
                    if ($localStatus !== $subscription->status) {
                        $this->warn("  ⚠️ Status mismatch detected");
                        
                        if (!$isDryRun) {
                            $subscription->update([
                                'status' => $localStatus,
                                'updated_at' => Carbon::now()
                            ]);
                            $this->info("  ✅ Status updated from {$subscription->status} to {$localStatus}");
                        } else {
                            $this->warn("  [DRY-RUN] Would update status from {$subscription->status} to {$localStatus}");
                        }
                    } else {
                        $this->info("  ✅ Status is synchronized");
                    }
                }

                $synced++;
                
            } catch (\Exception $e) {
                $this->error("  ❌ Error: " . $e->getMessage());
                $errors++;
            }
            
            $this->newLine();
        }

        // Summary
        $this->newLine();
        $this->info('=== SYNCHRONIZATION SUMMARY ===');
        $this->info("Subscriptions checked: {$subscriptions->count()}");
        $this->info("Successfully synced: {$synced}");
        $this->info("Suspended (refunds): {$suspended}");
        if ($errors > 0) {
            $this->error("Errors encountered: {$errors}");
        }
        
        if ($isDryRun) {
            $this->warn('This was a DRY-RUN - no changes were made');
        }

        return 0;
    }

    private function mapPayPalStatusToLocal(string $paypalStatus): string
    {
        $mapping = [
            'active' => 'active',
            'approved' => 'active',
            'suspended' => 'suspended',
            'cancelled' => 'cancelled',
            'expired' => 'expired',
            'approval_pending' => 'pending',
        ];

        return $mapping[strtolower($paypalStatus)] ?? 'pending';
    }
}