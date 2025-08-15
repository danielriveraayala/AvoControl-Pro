<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\PayPalService;
use App\Models\PaymentLog;
use App\Mail\TrialReminderEmail;
use App\Mail\SubscriptionExpiringEmail;
use App\Mail\TrialExpiredEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Str;

class MonitorSubscriptions extends Command
{
    protected $signature = 'subscriptions:monitor {--dry-run : Show what would be done without making changes}';
    protected $description = 'Monitor subscription statuses and send expiration alerts';

    private PayPalService $paypalService;
    private array $stats = [
        'checked' => 0,
        'expiring_soon' => 0,
        'expired' => 0,
        'payment_failed' => 0,
        'suspended' => 0,
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
        $this->info('🔍 Iniciando monitoreo de suscripciones...');
        $this->newLine();

        $isDryRun = $this->option('dry-run');
        if ($isDryRun) {
            $this->warn('🔥 MODO DRY-RUN: No se realizarán cambios reales');
            $this->newLine();
        }

        // 1. Check active subscriptions status
        $this->checkActiveSubscriptions($isDryRun);

        // 2. Check trial subscriptions expiring soon  
        $this->checkTrialExpirations($isDryRun);

        // 3. Check paid subscriptions expiring soon
        $this->checkSubscriptionExpirations($isDryRun);

        // 4. Process failed payments
        $this->processFailedPayments($isDryRun);

        // 5. Check suspended subscriptions
        $this->checkSuspendedSubscriptions($isDryRun);

        // 6. Generate summary
        $this->showSummary();

        $this->newLine();
        $this->info('✅ Monitoreo de suscripciones completado!');
    }

    private function checkActiveSubscriptions(bool $isDryRun = false): void
    {
        $this->info('🔄 Verificando estado de suscripciones activas...');

        $activeSubscriptions = Subscription::where('status', 'active')
            ->whereNotNull('paypal_subscription_id')
            ->get();

        foreach ($activeSubscriptions as $subscription) {
            $this->stats['checked']++;
            
            try {
                // Get current status from PayPal
                $paypalResult = $this->paypalService->getSubscription($subscription->paypal_subscription_id);
                
                if ($paypalResult['success']) {
                    $paypalData = $paypalResult['data'];
                    $paypalStatus = $paypalData['status'];
                    
                    // Check if status changed
                    if ($this->mapPayPalStatus($paypalStatus) !== $subscription->status) {
                        $this->warn("   Status change detected: {$subscription->tenant->name} - {$subscription->status} → {$paypalStatus}");
                        
                        if (!$isDryRun) {
                            $subscription->update([
                                'status' => $this->mapPayPalStatus($paypalStatus),
                                'paypal_metadata' => $paypalData,
                            ]);

                            // Update tenant status if needed
                            if ($paypalStatus === 'SUSPENDED' || $paypalStatus === 'CANCELLED') {
                                $subscription->tenant->update(['status' => 'suspended']);
                            }
                        }
                    } else {
                        $this->line("   ✓ {$subscription->tenant->name}: Status OK ({$subscription->status})");
                    }
                } else {
                    $this->error("   ❌ Error checking {$subscription->tenant->name}: " . $paypalResult['error']);
                    $this->stats['errors']++;
                }

            } catch (\Exception $e) {
                $this->error("   ❌ Exception checking {$subscription->tenant->name}: " . $e->getMessage());
                $this->stats['errors']++;
                
                $this->logError('subscription_check_failed', [
                    'subscription_id' => $subscription->id,
                    'tenant_id' => $subscription->tenant_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->newLine();
    }

    private function checkTrialExpirations(bool $isDryRun = false): void
    {
        $this->info('⏰ Verificando vencimientos de trial...');

        // Trials expiring in 3 days
        $trialsExpiring3Days = Subscription::where('is_trial', true)
            ->where('status', 'active')
            ->whereBetween('trial_ends_at', [
                Carbon::now()->addDays(3)->startOfDay(),
                Carbon::now()->addDays(3)->endOfDay()
            ])
            ->with('tenant')
            ->get();

        // Trials expiring in 1 day  
        $trialsExpiring1Day = Subscription::where('is_trial', true)
            ->where('status', 'active')
            ->whereBetween('trial_ends_at', [
                Carbon::now()->addDay()->startOfDay(),
                Carbon::now()->addDay()->endOfDay()
            ])
            ->with('tenant')
            ->get();

        // Trials expiring today
        $trialsExpiringToday = Subscription::where('is_trial', true)
            ->where('status', 'active')
            ->whereBetween('trial_ends_at', [
                Carbon::now()->startOfDay(),
                Carbon::now()->endOfDay()
            ])
            ->with('tenant')
            ->get();

        // Send 3-day reminders
        foreach ($trialsExpiring3Days as $subscription) {
            $this->stats['expiring_soon']++;
            $this->warn("   📅 Trial expira en 3 días: {$subscription->tenant->name}");
            
            if (!$isDryRun) {
                $this->sendTrialReminderEmail($subscription, 3);
            }
        }

        // Send 1-day reminders  
        foreach ($trialsExpiring1Day as $subscription) {
            $this->stats['expiring_soon']++;
            $this->error("   ⚠️ Trial expira mañana: {$subscription->tenant->name}");
            
            if (!$isDryRun) {
                $this->sendTrialReminderEmail($subscription, 1);
            }
        }

        // Expire today's trials
        foreach ($trialsExpiringToday as $subscription) {
            $this->stats['expired']++;
            $this->error("   🚨 Trial expira HOY: {$subscription->tenant->name}");
            
            if (!$isDryRun) {
                $subscription->update([
                    'status' => 'expired',
                    'expired_at' => Carbon::now()
                ]);
                
                $subscription->tenant->update(['status' => 'suspended']);
                $this->sendTrialExpiredEmail($subscription);
            }
        }

        $this->newLine();
    }

    private function checkSubscriptionExpirations(bool $isDryRun = false): void
    {
        $this->info('💳 Verificando vencimientos de suscripciones pagadas...');

        // Paid subscriptions expiring soon
        $subscriptionsExpiring = Subscription::where('is_trial', false)
            ->where('status', 'active')
            ->whereNotNull('next_billing_date')
            ->where('next_billing_date', '<=', Carbon::now()->addDays(3))
            ->with('tenant')
            ->get();

        foreach ($subscriptionsExpiring as $subscription) {
            $daysUntilExpiry = Carbon::now()->diffInDays($subscription->next_billing_date, false);
            
            if ($daysUntilExpiry <= 1) {
                $this->error("   🚨 Suscripción vence en {$daysUntilExpiry} día(s): {$subscription->tenant->name}");
            } else {
                $this->warn("   📅 Suscripción vence en {$daysUntilExpiry} días: {$subscription->tenant->name}");
            }
            
            $this->stats['expiring_soon']++;
            
            if (!$isDryRun && $daysUntilExpiry <= 3) {
                $this->sendSubscriptionExpiringEmail($subscription, (int)$daysUntilExpiry);
            }
        }

        $this->newLine();
    }

    private function processFailedPayments(bool $isDryRun = false): void
    {
        $this->info('💸 Procesando pagos fallidos...');

        $subscriptionsWithFailedPayments = Subscription::where('failed_payment_count', '>', 0)
            ->where('status', 'active')
            ->whereNotNull('grace_period_ends_at')
            ->with('tenant')
            ->get();

        foreach ($subscriptionsWithFailedPayments as $subscription) {
            $this->stats['payment_failed']++;
            
            $gracePeriodEnded = Carbon::now()->isAfter($subscription->grace_period_ends_at);
            $daysRemaining = Carbon::now()->diffInDays($subscription->grace_period_ends_at, false);
            
            if ($gracePeriodEnded) {
                $this->error("   🚨 Período de gracia terminado: {$subscription->tenant->name} - Suspendiendo...");
                
                if (!$isDryRun) {
                    $subscription->update(['status' => 'suspended']);
                    $subscription->tenant->update(['status' => 'suspended']);
                    
                    // Try to suspend in PayPal
                    $this->paypalService->suspendSubscription(
                        $subscription->paypal_subscription_id,
                        'Payment failures exceeded grace period'
                    );
                }
            } else {
                $this->warn("   ⏳ Período de gracia: {$subscription->tenant->name} - {$daysRemaining} días restantes");
            }
        }

        $this->newLine();
    }

    private function checkSuspendedSubscriptions(bool $isDryRun = false): void
    {
        $this->info('⛔ Verificando suscripciones suspendidas...');

        $suspendedSubscriptions = Subscription::where('status', 'suspended')
            ->with('tenant')
            ->get();

        foreach ($suspendedSubscriptions as $subscription) {
            $this->stats['suspended']++;
            $this->line("   🔴 Suspendida: {$subscription->tenant->name}");
            
            // Check if it was reactivated in PayPal
            if ($subscription->paypal_subscription_id) {
                $paypalResult = $this->paypalService->getSubscription($subscription->paypal_subscription_id);
                
                if ($paypalResult['success'] && $paypalResult['data']['status'] === 'ACTIVE') {
                    $this->info("   🟢 Reactivada en PayPal: {$subscription->tenant->name}");
                    
                    if (!$isDryRun) {
                        $subscription->update([
                            'status' => 'active',
                            'suspended_at' => null,
                            'failed_payment_count' => 0,
                            'grace_period_ends_at' => null,
                        ]);
                        
                        $subscription->tenant->update(['status' => 'active']);
                    }
                }
            }
        }

        $this->newLine();
    }

    private function sendTrialReminderEmail(Subscription $subscription, int $daysLeft): void
    {
        try {
            $user = $subscription->tenant->users()->first();
            if ($user) {
                Mail::to($user->email)->send(new TrialReminderEmail($subscription, $daysLeft));
                $this->stats['emails_sent']++;
                $this->line("     📧 Email de recordatorio enviado a {$user->email}");
            }
        } catch (\Exception $e) {
            $this->error("     ❌ Error enviando email: " . $e->getMessage());
            $this->stats['errors']++;
        }
    }

    private function sendTrialExpiredEmail(Subscription $subscription): void
    {
        try {
            $user = $subscription->tenant->users()->first();
            if ($user) {
                Mail::to($user->email)->send(new TrialExpiredEmail($subscription));
                $this->stats['emails_sent']++;
                $this->line("     📧 Email de trial expirado enviado a {$user->email}");
            }
        } catch (\Exception $e) {
            $this->error("     ❌ Error enviando email: " . $e->getMessage());
            $this->stats['errors']++;
        }
    }

    private function sendSubscriptionExpiringEmail(Subscription $subscription, int $daysLeft): void
    {
        try {
            $user = $subscription->tenant->users()->first();
            if ($user) {
                Mail::to($user->email)->send(new SubscriptionExpiringEmail($subscription, $daysLeft));
                $this->stats['emails_sent']++;
                $this->line("     📧 Email de suscripción expirando enviado a {$user->email}");
            }
        } catch (\Exception $e) {
            $this->error("     ❌ Error enviando email: " . $e->getMessage());
            $this->stats['errors']++;
        }
    }

    private function mapPayPalStatus(string $paypalStatus): string
    {
        return match($paypalStatus) {
            'APPROVAL_PENDING' => 'pending',
            'APPROVED' => 'approved', 
            'ACTIVE' => 'active',
            'SUSPENDED' => 'suspended',
            'CANCELLED' => 'cancelled',
            'EXPIRED' => 'expired',
            default => 'unknown'
        };
    }

    private function logError(string $event, array $context): void
    {
        try {
            PaymentLog::create([
                'uuid' => Str::uuid(),
                'type' => 'monitoring',
                'event' => $event,
                'level' => 'error',
                'message' => 'Subscription monitoring error',
                'context' => $context,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Subscription Monitor',
                'occurred_at' => Carbon::now()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create monitoring log', [
                'event' => $event,
                'context' => $context,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function showSummary(): void
    {
        $this->newLine();
        $this->info('📊 Resumen del Monitoreo');
        
        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['Suscripciones verificadas', $this->stats['checked']],
                ['Expirando pronto', $this->stats['expiring_soon']], 
                ['Expiradas hoy', $this->stats['expired']],
                ['Con pagos fallidos', $this->stats['payment_failed']],
                ['Suspendidas', $this->stats['suspended']],
                ['Emails enviados', $this->stats['emails_sent']],
                ['Errores', $this->stats['errors']],
            ]
        );
    }
}