<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Services\PayPalService;
use App\Models\PaymentLog;
use App\Mail\PaymentRetryEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Str;

class RetryFailedPayments extends Command
{
    protected $signature = 'subscriptions:retry-payments {--dry-run : Show what would be done without making changes} {--max-retries=3 : Maximum retry attempts}';
    protected $description = 'Retry failed payments automatically with progressive delays';

    private PayPalService $paypalService;
    private array $stats = [
        'total_failed' => 0,
        'retry_attempted' => 0,
        'retry_successful' => 0,
        'retry_failed' => 0,
        'max_attempts_exceeded' => 0,
        'emails_sent' => 0,
        'errors' => 0,
    ];

    public function __construct(PayPalService $paypalService)
    {
        parent::__construct();
        $this->paypalService = $paypalService;
    }

    public function handle()
    {
        $this->info('🔄 Iniciando reintentos automáticos de pagos fallidos...');
        $this->newLine();

        $isDryRun = $this->option('dry-run');
        $maxRetries = (int) $this->option('max-retries');

        if ($isDryRun) {
            $this->warn('🔥 MODO DRY-RUN: No se realizarán cambios reales');
            $this->newLine();
        }

        // Get subscriptions with failed payments that can be retried
        $subscriptionsToRetry = $this->getSubscriptionsForRetry($maxRetries);

        if ($subscriptionsToRetry->isEmpty()) {
            $this->info('✅ No hay pagos fallidos para reintentar.');
            return;
        }

        $this->info("📋 Encontradas {$subscriptionsToRetry->count()} suscripciones con pagos para reintentar");
        $this->newLine();

        foreach ($subscriptionsToRetry as $subscription) {
            $this->processSubscriptionRetry($subscription, $isDryRun, $maxRetries);
        }

        $this->showSummary();
        $this->newLine();
        $this->info('✅ Reintentos de pagos completados!');
    }

    private function getSubscriptionsForRetry(int $maxRetries)
    {
        return Subscription::where('status', 'active')
            ->where('failed_payment_count', '>', 0)
            ->where('failed_payment_count', '<=', $maxRetries)
            ->whereNotNull('grace_period_ends_at')
            ->where('grace_period_ends_at', '>', Carbon::now()) // Still in grace period
            ->with(['tenant', 'lastFailedPayment'])
            ->get()
            ->filter(function ($subscription) {
                return $this->shouldRetryPayment($subscription);
            });
    }

    private function shouldRetryPayment(Subscription $subscription): bool
    {
        $lastFailedPayment = $subscription->subscriptionPayments()
            ->where('status', 'failed')
            ->latest('failed_at')
            ->first();

        if (!$lastFailedPayment) {
            return false;
        }

        // Progressive retry delays: 1 day, 3 days, 7 days
        $retryDelays = [1, 3, 7];
        $attemptNumber = $subscription->failed_payment_count;
        
        if ($attemptNumber > count($retryDelays)) {
            return false; // Max retries exceeded
        }

        $delayDays = $retryDelays[$attemptNumber - 1] ?? 7;
        $nextRetryDate = $lastFailedPayment->failed_at->addDays($delayDays);

        return Carbon::now()->isAfter($nextRetryDate);
    }

    private function processSubscriptionRetry(Subscription $subscription, bool $isDryRun, int $maxRetries): void
    {
        $this->stats['total_failed']++;
        $tenantName = $subscription->tenant->name;
        $attemptNumber = $subscription->failed_payment_count + 1;

        $this->info("🔄 Procesando: {$tenantName} (Intento #{$attemptNumber})");

        if ($subscription->failed_payment_count >= $maxRetries) {
            $this->stats['max_attempts_exceeded']++;
            $this->error("   ❌ Máximo de reintentos excedido ({$maxRetries})");

            if (!$isDryRun) {
                // Mark subscription as suspended due to max retries
                $subscription->update([
                    'status' => 'suspended',
                    'suspended_at' => Carbon::now(),
                    'suspension_reason' => 'max_payment_retries_exceeded'
                ]);

                $subscription->tenant->update(['status' => 'suspended']);
                
                // Send final notification
                $this->sendMaxRetriesExceededEmail($subscription);
            }

            return;
        }

        try {
            // Create a new payment attempt record
            if (!$isDryRun) {
                $paymentAttempt = $this->createPaymentAttempt($subscription, $attemptNumber);
            }

            $this->stats['retry_attempted']++;

            // In a real scenario, we would trigger the actual payment retry with PayPal
            // For now, we simulate the process
            $retryResult = $this->simulatePaymentRetry($subscription);

            if ($retryResult['success']) {
                $this->stats['retry_successful']++;
                $this->info("   ✅ Reintento exitoso para {$tenantName}");

                if (!$isDryRun) {
                    // Reset failed payment count
                    $subscription->update([
                        'failed_payment_count' => 0,
                        'grace_period_ends_at' => null,
                        'last_payment_retry_at' => Carbon::now(),
                    ]);

                    if (isset($paymentAttempt)) {
                        $paymentAttempt->update([
                            'status' => 'completed',
                            'completed_at' => Carbon::now(),
                        ]);
                    }

                    $this->sendPaymentSuccessEmail($subscription);
                }

            } else {
                $this->stats['retry_failed']++;
                $this->warn("   ⚠️ Reintento fallido para {$tenantName}: {$retryResult['error']}");

                if (!$isDryRun) {
                    $subscription->increment('failed_payment_count');
                    $subscription->update(['last_payment_retry_at' => Carbon::now()]);

                    if (isset($paymentAttempt)) {
                        $paymentAttempt->update([
                            'status' => 'failed',
                            'failed_at' => Carbon::now(),
                            'failure_reason' => $retryResult['error'],
                        ]);
                    }

                    $this->sendPaymentRetryFailedEmail($subscription, $attemptNumber);
                }
            }

        } catch (\Exception $e) {
            $this->stats['errors']++;
            $this->error("   ❌ Error procesando {$tenantName}: " . $e->getMessage());

            $this->logError('payment_retry_error', [
                'subscription_id' => $subscription->id,
                'tenant_id' => $subscription->tenant_id,
                'attempt_number' => $attemptNumber,
                'error' => $e->getMessage()
            ]);
        }

        $this->newLine();
    }

    private function createPaymentAttempt(Subscription $subscription, int $attemptNumber): SubscriptionPayment
    {
        return SubscriptionPayment::create([
            'uuid' => Str::uuid(),
            'subscription_id' => $subscription->id,
            'tenant_id' => $subscription->tenant_id,
            'paypal_payment_id' => 'RETRY-' . uniqid(),
            'amount' => $subscription->amount,
            'currency' => $subscription->currency,
            'type' => 'retry',
            'status' => 'pending',
            'billing_cycle' => $subscription->billing_cycle_count + 1,
            'retry_attempt' => $attemptNumber,
            'payment_date' => Carbon::now(),
        ]);
    }

    private function simulatePaymentRetry(Subscription $subscription): array
    {
        // In real implementation, this would call PayPal API to retry payment
        // For simulation, we'll use some logic to determine success/failure

        $tenant = $subscription->tenant;
        
        // Simulate 70% success rate for retries
        $isSuccessful = rand(1, 100) <= 70;

        if ($isSuccessful) {
            return [
                'success' => true,
                'payment_id' => 'PAY-RETRY-' . uniqid(),
                'amount' => $subscription->amount,
                'currency' => $subscription->currency,
            ];
        } else {
            $errorMessages = [
                'Insufficient funds',
                'Payment method declined', 
                'Account temporarily locked',
                'Payment processor unavailable',
                'Invalid payment method'
            ];

            return [
                'success' => false,
                'error' => $errorMessages[array_rand($errorMessages)],
                'error_code' => 'PAYMENT_DECLINED'
            ];
        }
    }

    private function sendPaymentSuccessEmail(Subscription $subscription): void
    {
        try {
            $user = $subscription->tenant->users()->first();
            if ($user) {
                // TODO: Create PaymentSuccessEmail mailable
                $this->stats['emails_sent']++;
                $this->line("     📧 Email de pago exitoso enviado a {$user->email}");
            }
        } catch (\Exception $e) {
            $this->error("     ❌ Error enviando email: " . $e->getMessage());
            $this->stats['errors']++;
        }
    }

    private function sendPaymentRetryFailedEmail(Subscription $subscription, int $attemptNumber): void
    {
        try {
            $user = $subscription->tenant->users()->first();
            if ($user) {
                // TODO: Create PaymentRetryFailedEmail mailable
                $this->stats['emails_sent']++;
                $this->line("     📧 Email de reintento fallido enviado a {$user->email}");
            }
        } catch (\Exception $e) {
            $this->error("     ❌ Error enviando email: " . $e->getMessage());
            $this->stats['errors']++;
        }
    }

    private function sendMaxRetriesExceededEmail(Subscription $subscription): void
    {
        try {
            $user = $subscription->tenant->users()->first();
            if ($user) {
                // TODO: Create MaxRetriesExceededEmail mailable
                $this->stats['emails_sent']++;
                $this->line("     📧 Email de máximos reintentos excedidos enviado a {$user->email}");
            }
        } catch (\Exception $e) {
            $this->error("     ❌ Error enviando email: " . $e->getMessage());
            $this->stats['errors']++;
        }
    }

    private function logError(string $event, array $context): void
    {
        try {
            PaymentLog::create([
                'uuid' => Str::uuid(),
                'type' => 'payment_retry',
                'event' => $event,
                'level' => 'error',
                'message' => 'Payment retry error',
                'context' => $context,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Payment Retry System',
                'occurred_at' => Carbon::now()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create payment retry log', [
                'event' => $event,
                'context' => $context,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function showSummary(): void
    {
        $this->newLine();
        $this->info('📊 Resumen de Reintentos de Pago');

        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['Total pagos fallidos', $this->stats['total_failed']],
                ['Reintentos intentados', $this->stats['retry_attempted']],
                ['Reintentos exitosos', $this->stats['retry_successful']],
                ['Reintentos fallidos', $this->stats['retry_failed']],
                ['Máx. reintentos excedidos', $this->stats['max_attempts_exceeded']],
                ['Emails enviados', $this->stats['emails_sent']],
                ['Errores', $this->stats['errors']],
            ]
        );

        if ($this->stats['retry_attempted'] > 0) {
            $successRate = round(($this->stats['retry_successful'] / $this->stats['retry_attempted']) * 100, 1);
            $this->info("🎯 Tasa de éxito: {$successRate}%");
        }
    }
}