<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Services\PayPalService;
use Carbon\Carbon;

class SimulateRefundWebhook extends Command
{
    protected $signature = 'paypal:simulate-refund {--subscription-id=} {--amount=} {--reason=}';
    protected $description = 'Simulate a PayPal refund webhook for testing';

    private PayPalService $paypalService;

    public function __construct(PayPalService $paypalService)
    {
        parent::__construct();
        $this->paypalService = $paypalService;
    }

    public function handle()
    {
        $this->info('🔄 Simulando webhook de reembolso de PayPal...');
        $this->newLine();

        // Get parameters
        $subscriptionId = $this->option('subscription-id');
        $amount = $this->option('amount') ?? '1.00';
        $reason = $this->option('reason') ?? 'Refund requested by customer';

        // If no subscription ID provided, get the most recent active one
        if (!$subscriptionId) {
            $subscription = Subscription::where('status', 'active')->latest()->first();
            if (!$subscription) {
                $this->error('❌ No active subscriptions found. Please provide --subscription-id');
                return 1;
            }
            $subscriptionId = $subscription->paypal_subscription_id;
        } else {
            $subscription = Subscription::where('paypal_subscription_id', $subscriptionId)
                ->orWhere('id', $subscriptionId)
                ->first();
        }

        if (!$subscription) {
            $this->error("❌ Subscription not found: $subscriptionId");
            return 1;
        }

        $this->info("📋 Subscription found:");
        $this->line("   ID: {$subscription->id}");
        $this->line("   PayPal ID: {$subscription->paypal_subscription_id}");
        $this->line("   Plan: {$subscription->plan}");
        $this->line("   Status: {$subscription->status}");
        $this->line("   Tenant: {$subscription->tenant->name}");
        $this->newLine();

        // Find a payment to refund
        $payment = SubscriptionPayment::where('subscription_id', $subscription->id)
            ->where('status', 'completed')
            ->latest()
            ->first();

        if (!$payment) {
            // Create a mock payment for testing
            $payment = SubscriptionPayment::create([
                'uuid' => \Str::uuid(),
                'subscription_id' => $subscription->id,
                'tenant_id' => $subscription->tenant_id,
                'paypal_payment_id' => 'MOCK-PAYMENT-' . strtoupper(\Str::random(8)),
                'amount' => $amount,
                'currency' => 'USD',
                'type' => 'initial',
                'status' => 'completed',
                'payment_date' => Carbon::now(),
                'completed_at' => Carbon::now(),
                'paypal_response' => ['mock' => true]
            ]);
            
            $this->info("🔧 Created mock payment for testing: {$payment->paypal_payment_id}");
        }

        // Create refund webhook data
        $webhookData = [
            'id' => 'WH-' . strtoupper(\Str::random(17)),
            'event_version' => '1.0',
            'create_time' => Carbon::now()->toISOString(),
            'resource_type' => 'refund',
            'event_type' => 'PAYMENT.CAPTURE.REFUNDED',
            'summary' => 'Payment refunded',
            'resource' => [
                'id' => $payment->paypal_payment_id,
                'amount' => [
                    'value' => $amount,
                    'currency_code' => 'USD'
                ],
                'status' => 'COMPLETED',
                'note_to_payer' => $reason,
                'create_time' => Carbon::now()->toISOString(),
                'update_time' => Carbon::now()->toISOString(),
                'links' => []
            ]
        ];

        $this->info('📄 Webhook data prepared:');
        $this->line('   Event Type: PAYMENT.CAPTURE.REFUNDED');
        $this->line("   Payment ID: {$payment->paypal_payment_id}");
        $this->line("   Refund Amount: $amount USD");
        $this->line("   Reason: $reason");
        $this->newLine();

        // Process the webhook
        $this->info('⚡ Processing refund webhook...');
        try {
            $result = $this->paypalService->processWebhook($webhookData);
            
            if ($result['success']) {
                $this->info("✅ Webhook processed successfully!");
                $this->line("   Message: {$result['message']}");
                
                // Check subscription status
                $subscription->refresh();
                $this->newLine();
                $this->info('📊 Updated subscription status:');
                $this->line("   Status: {$subscription->status}");
                $this->line("   Suspended At: " . ($subscription->suspended_at ? $subscription->suspended_at->format('d/m/Y H:i:s') : 'N/A'));
                $this->line("   Suspension Reason: " . ($subscription->suspension_reason ?? 'N/A'));
                
                // Check tenant status
                $subscription->tenant->refresh();
                $this->line("   Tenant Status: {$subscription->tenant->status}");
                
                // Show refund record
                $refundRecord = SubscriptionPayment::where('subscription_id', $subscription->id)
                    ->where('type', 'refund')
                    ->latest()
                    ->first();
                    
                if ($refundRecord) {
                    $this->newLine();
                    $this->info('💰 Refund record created:');
                    $this->line("   ID: {$refundRecord->id}");
                    $this->line("   Amount: {$refundRecord->amount}");
                    $this->line("   Status: {$refundRecord->status}");
                }
                
            } else {
                $this->error("❌ Webhook processing failed!");
                $this->line("   Error: {$result['error']}");
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Exception during webhook processing:");
            $this->line("   " . $e->getMessage());
            return 1;
        }

        $this->newLine();
        $this->info('🎉 Refund simulation completed successfully!');
        $this->line('You can now test accessing the system with this user to see the suspension message.');
        
        return 0;
    }
}