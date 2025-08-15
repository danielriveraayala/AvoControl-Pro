<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Services\PayPalService;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class MonitorPayPalTransactions extends Command
{
    protected $signature = 'paypal:monitor-transactions 
                            {--hours=1 : Check transactions from the last N hours}
                            {--all : Check all active subscriptions}
                            {--dry-run : Run without making changes}';

    protected $description = 'Monitor PayPal transactions for refunds, chargebacks, and payment status changes';

    protected $paypalService;

    public function __construct(PayPalService $paypalService)
    {
        parent::__construct();
        $this->paypalService = $paypalService;
    }

    public function handle()
    {
        $hours = $this->option('hours');
        $checkAll = $this->option('all');
        $isDryRun = $this->option('dry-run');
        
        $startTime = Carbon::now()->subHours($hours);
        $endTime = Carbon::now();
        
        $this->info('🔍 PayPal Transaction Monitor Started');
        $this->info("Checking transactions from: {$startTime->format('Y-m-d H:i:s')} to {$endTime->format('Y-m-d H:i:s')}");
        if ($isDryRun) {
            $this->warn('Running in DRY-RUN mode - no changes will be made');
        }
        $this->newLine();
        
        // Get subscriptions to monitor
        $query = Subscription::whereIn('status', ['active', 'pending'])
                            ->whereNotNull('paypal_subscription_id')
                            ->where(function($q) {
                                // Exclude test subscriptions
                                $q->where('paypal_subscription_id', 'NOT LIKE', 'TEST-%')
                                  ->where('paypal_subscription_id', 'NOT LIKE', 'MOCK-%');
                            });
        
        // If not checking all, only check recently updated or with failed payments
        if (!$checkAll) {
            $query->where(function($q) use ($hours) {
                $q->where('updated_at', '>=', Carbon::now()->subHours($hours * 2))
                  ->orWhere('failed_payment_count', '>', 0)
                  ->orWhereNotNull('grace_period_ends_at');
            });
        }
        
        $subscriptions = $query->get();
        
        if ($subscriptions->isEmpty()) {
            $this->info('No subscriptions to monitor');
            return 0;
        }
        
        $this->info("Found {$subscriptions->count()} subscription(s) to monitor");
        $this->newLine();
        
        $stats = [
            'checked' => 0,
            'refunds_detected' => 0,
            'chargebacks_detected' => 0,
            'payments_detected' => 0,
            'suspensions' => 0,
            'errors' => 0,
        ];
        
        $progressBar = $this->output->createProgressBar($subscriptions->count());
        $progressBar->start();
        
        foreach ($subscriptions as $subscription) {
            $progressBar->advance();
            $stats['checked']++;
            
            try {
                // Get subscription details from PayPal
                $response = $this->paypalService->getSubscription($subscription->paypal_subscription_id);
                
                if (!$response['success']) {
                    // If subscription not found, check for local refund records
                    if (strpos($response['error'] ?? '', 'RESOURCE_NOT_FOUND') !== false) {
                        $this->checkLocalRefunds($subscription, $isDryRun, $stats);
                    } else {
                        $stats['errors']++;
                        Log::warning('PayPal subscription check failed', [
                            'subscription_id' => $subscription->id,
                            'error' => $response['error'] ?? 'Unknown error'
                        ]);
                    }
                    continue;
                }
                
                // Update subscription status from PayPal
                $paypalData = $response['data'];
                $paypalStatus = strtolower($paypalData['status'] ?? '');
                $localStatus = $this->mapPayPalStatusToLocal($paypalStatus);
                
                if ($localStatus !== $subscription->status && !$isDryRun) {
                    $subscription->update(['status' => $localStatus]);
                    Log::info('Subscription status synced with PayPal', [
                        'subscription_id' => $subscription->id,
                        'old_status' => $subscription->status,
                        'new_status' => $localStatus
                    ]);
                }
                
                // Check for recent transactions
                $transactionsResponse = $this->paypalService->getSubscriptionTransactions(
                    $subscription->paypal_subscription_id,
                    $startTime->toIso8601String(),
                    $endTime->toIso8601String()
                );
                
                if ($transactionsResponse['success'] && isset($transactionsResponse['data']['transactions'])) {
                    $this->processTransactions(
                        $subscription,
                        $transactionsResponse['data']['transactions'],
                        $isDryRun,
                        $stats
                    );
                }
                
            } catch (\Exception $e) {
                $stats['errors']++;
                Log::error('Error monitoring PayPal subscription', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        // Display summary
        $this->info('=== MONITORING SUMMARY ===');
        $this->info("Subscriptions checked: {$stats['checked']}");
        
        if ($stats['refunds_detected'] > 0) {
            $this->warn("🔴 Refunds detected: {$stats['refunds_detected']}");
        }
        
        if ($stats['chargebacks_detected'] > 0) {
            $this->warn("🔴 Chargebacks detected: {$stats['chargebacks_detected']}");
        }
        
        if ($stats['payments_detected'] > 0) {
            $this->info("✅ New payments detected: {$stats['payments_detected']}");
        }
        
        if ($stats['suspensions'] > 0) {
            $this->warn("⚠️ Subscriptions suspended: {$stats['suspensions']}");
        }
        
        if ($stats['errors'] > 0) {
            $this->error("❌ Errors encountered: {$stats['errors']}");
        }
        
        if ($isDryRun) {
            $this->warn('This was a DRY-RUN - no changes were made');
        }
        
        // Log summary
        Log::info('PayPal transaction monitoring completed', $stats);
        
        return 0;
    }
    
    private function processTransactions($subscription, array $transactions, bool $isDryRun, array &$stats)
    {
        foreach ($transactions as $transaction) {
            $transactionId = $transaction['id'] ?? null;
            $status = $transaction['status'] ?? '';
            $amount = $transaction['amount_with_breakdown']['gross_amount']['value'] ?? 0;
            $currency = $transaction['amount_with_breakdown']['gross_amount']['currency_code'] ?? 'USD';
            $transactionTime = Carbon::parse($transaction['time'] ?? now());
            
            // Check if we already processed this transaction
            $existingPayment = SubscriptionPayment::where('paypal_payment_id', $transactionId)
                                                  ->orWhere('paypal_payment_id', 'LIKE', '%' . $transactionId . '%')
                                                  ->first();
            
            if ($existingPayment) {
                continue; // Already processed
            }
            
            // Process based on transaction status
            switch (strtoupper($status)) {
                case 'COMPLETED':
                    if (!$isDryRun) {
                        SubscriptionPayment::create([
                            'uuid' => Str::uuid(),
                            'subscription_id' => $subscription->id,
                            'tenant_id' => $subscription->tenant_id,
                            'paypal_payment_id' => $transactionId,
                            'amount' => $amount,
                            'currency' => $currency,
                            'type' => 'recurring',
                            'status' => 'completed',
                            'payment_date' => $transactionTime,
                            'completed_at' => $transactionTime,
                            'paypal_response' => $transaction
                        ]);
                        
                        // Reset failed payment count
                        $subscription->update([
                            'failed_payment_count' => 0,
                            'grace_period_ends_at' => null
                        ]);
                    }
                    $stats['payments_detected']++;
                    Log::info('New payment detected', [
                        'subscription_id' => $subscription->id,
                        'amount' => $amount,
                        'transaction_id' => $transactionId
                    ]);
                    break;
                    
                case 'REFUNDED':
                case 'PARTIALLY_REFUNDED':
                    if (!$isDryRun) {
                        SubscriptionPayment::create([
                            'uuid' => Str::uuid(),
                            'subscription_id' => $subscription->id,
                            'tenant_id' => $subscription->tenant_id,
                            'paypal_payment_id' => 'REFUND-' . Str::random(10) . '-' . $transactionId,
                            'amount' => -abs($amount),
                            'currency' => $currency,
                            'type' => 'refund',
                            'status' => 'refunded',
                            'payment_date' => $transactionTime,
                            'completed_at' => $transactionTime,
                            'failure_reason' => 'Payment refunded',
                            'paypal_response' => $transaction
                        ]);
                        
                        // Suspend subscription
                        if ($subscription->status === 'active') {
                            $subscription->update([
                                'status' => 'suspended',
                                'suspended_at' => Carbon::now(),
                                'suspension_reason' => "Payment refunded on {$transactionTime->format('Y-m-d')}: \${$amount}",
                                'suspended_by' => 'paypal-monitor'
                            ]);
                            
                            // Suspend tenant
                            if ($subscription->tenant) {
                                $subscription->tenant->update(['status' => 'suspended']);
                            }
                            
                            $stats['suspensions']++;
                        }
                    }
                    $stats['refunds_detected']++;
                    Log::warning('Refund detected', [
                        'subscription_id' => $subscription->id,
                        'amount' => $amount,
                        'transaction_id' => $transactionId
                    ]);
                    break;
                    
                case 'REVERSED':
                case 'CHARGEBACK':
                    if (!$isDryRun) {
                        SubscriptionPayment::create([
                            'uuid' => Str::uuid(),
                            'subscription_id' => $subscription->id,
                            'tenant_id' => $subscription->tenant_id,
                            'paypal_payment_id' => 'CHARGEBACK-' . Str::random(10) . '-' . $transactionId,
                            'amount' => -abs($amount),
                            'currency' => $currency,
                            'type' => 'chargeback',
                            'status' => 'reversed',
                            'payment_date' => $transactionTime,
                            'failed_at' => $transactionTime,
                            'failure_reason' => 'Payment reversed/chargeback',
                            'paypal_response' => $transaction
                        ]);
                        
                        // Immediately suspend subscription
                        if ($subscription->status === 'active') {
                            $subscription->update([
                                'status' => 'suspended',
                                'suspended_at' => Carbon::now(),
                                'suspension_reason' => "Chargeback on {$transactionTime->format('Y-m-d')}: \${$amount}",
                                'suspended_by' => 'paypal-monitor'
                            ]);
                            
                            // Suspend tenant
                            if ($subscription->tenant) {
                                $subscription->tenant->update(['status' => 'suspended']);
                            }
                            
                            $stats['suspensions']++;
                        }
                    }
                    $stats['chargebacks_detected']++;
                    Log::error('Chargeback detected', [
                        'subscription_id' => $subscription->id,
                        'amount' => $amount,
                        'transaction_id' => $transactionId
                    ]);
                    break;
            }
        }
    }
    
    private function checkLocalRefunds($subscription, bool $isDryRun, array &$stats)
    {
        // Check for local refund records that haven't suspended the subscription
        $refundPayment = $subscription->payments()
            ->where('type', 'refund')
            ->where('amount', '<', 0)
            ->latest()
            ->first();
            
        if ($refundPayment && $subscription->status === 'active') {
            if (!$isDryRun) {
                $subscription->update([
                    'status' => 'suspended',
                    'suspended_at' => Carbon::now(),
                    'suspension_reason' => "Payment refunded: " . ($refundPayment->failure_reason ?? 'Refund processed'),
                    'suspended_by' => 'paypal-monitor'
                ]);
                
                // Suspend tenant
                if ($subscription->tenant) {
                    $subscription->tenant->update(['status' => 'suspended']);
                }
            }
            
            $stats['refunds_detected']++;
            $stats['suspensions']++;
            
            Log::warning('Local refund processed', [
                'subscription_id' => $subscription->id,
                'refund_amount' => $refundPayment->amount
            ]);
        }
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