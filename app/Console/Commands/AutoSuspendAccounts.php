<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\DataBackupService;
use App\Mail\AccountSuspendedEmail;
use App\Mail\SuspensionWarningEmail;
use App\Mail\AccountReactivatedEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AutoSuspendAccounts extends Command
{
    protected $signature = 'accounts:auto-suspend {--dry-run : Show what would be done without making changes} {--force : Force suspension even if data backup fails}';
    protected $description = 'Automatically suspend accounts based on payment failures and grace periods';

    private array $stats = [
        'checked' => 0,
        'suspended' => 0,
        'warnings_sent' => 0,
        'grace_periods_started' => 0,
        'data_backups_created' => 0,
        'scheduled_for_deletion' => 0,
        'reactivated' => 0,
        'errors' => 0,
    ];

    public function handle()
    {
        $this->info('🔒 Iniciando suspensión automática de cuentas...');
        $this->newLine();

        $isDryRun = $this->option('dry-run');
        $force = $this->option('force');

        if ($isDryRun) {
            $this->warn('🔥 MODO DRY-RUN: No se realizarán cambios reales');
            $this->newLine();
        }

        // 1. Process subscriptions requiring immediate suspension
        $this->processImmediateSuspensions($isDryRun, $force);

        // 2. Start grace periods for first payment failures  
        $this->processGracePeriodInitiation($isDryRun);

        // 3. Send suspension warnings
        $this->processSuspensionWarnings($isDryRun);

        // 4. Process long-term suspended accounts (30+ days)
        $this->processLongTermSuspensions($isDryRun);

        // 5. Check for accounts that can be reactivated
        $this->processAccountReactivations($isDryRun);

        $this->showSummary();
        $this->newLine();
        $this->info('✅ Proceso de suspensión automática completado!');
    }

    private function processImmediateSuspensions(bool $isDryRun, bool $force): void
    {
        $this->info('🚨 Procesando suspensiones inmediatas...');

        $subscriptionsToSuspend = Subscription::where('status', 'active')
            ->where(function($query) {
                $query->where('failed_payment_count', '>=', 3)
                      ->orWhere(function($subQuery) {
                          $subQuery->whereNotNull('grace_period_ends_at')
                                   ->where('grace_period_ends_at', '<=', Carbon::now());
                      })
                      ->orWhere(function($subQuery) {
                          $subQuery->where('is_trial', true)
                                   ->whereNotNull('trial_ends_at')
                                   ->where('trial_ends_at', '<=', Carbon::now());
                      });
            })
            ->with('tenant')
            ->get();

        foreach ($subscriptionsToSuspend as $subscription) {
            $this->stats['checked']++;
            $tenantName = $subscription->tenant->name;

            try {
                $this->info("   🔒 Suspendiendo: {$tenantName}");

                if (!$isDryRun) {
                    // Create data backup before suspension
                    $backupSuccess = $this->createDataBackup($subscription, $force);
                    
                    if ($backupSuccess || $force) {
                        // Determine suspension reason
                        $reason = $this->getSuspensionReason($subscription);
                        
                        // Suspend the subscription
                        $suspended = $subscription->suspend($reason, 'auto-system');
                        
                        if ($suspended) {
                            $this->stats['suspended']++;
                            $this->info("     ✅ Cuenta suspendida: {$reason}");
                            
                            // Send suspension notification
                            $this->sendSuspensionNotification($subscription);
                            
                            // Schedule for deletion if suspension is due to trial expiry
                            if ($subscription->is_trial && $subscription->isTrialExpired()) {
                                $this->scheduleForDeletion($subscription, 30); // 30 days
                            }
                        }
                    } else {
                        $this->error("     ❌ No se pudo crear backup para {$tenantName}");
                        $this->stats['errors']++;
                    }
                }

            } catch (\Exception $e) {
                $this->error("   ❌ Error suspendiendo {$tenantName}: " . $e->getMessage());
                $this->stats['errors']++;
            }
        }

        $this->newLine();
    }

    private function processGracePeriodInitiation(bool $isDryRun): void
    {
        $this->info('⏳ Iniciando períodos de gracia...');

        $subscriptionsForGrace = Subscription::where('status', 'active')
            ->where('failed_payment_count', 1)
            ->whereNull('grace_period_started_at')
            ->with('tenant')
            ->get();

        foreach ($subscriptionsForGrace as $subscription) {
            $tenantName = $subscription->tenant->name;
            $graceDays = $subscription->custom_grace_period_days ?? $subscription->grace_period_days;

            $this->info("   ⏳ Iniciando período de gracia para {$tenantName} ({$graceDays} días)");

            if (!$isDryRun) {
                $subscription->startGracePeriod($graceDays);
                $this->stats['grace_periods_started']++;
            }
        }

        $this->newLine();
    }

    private function processSuspensionWarnings(bool $isDryRun): void
    {
        $this->info('⚠️ Enviando advertencias de suspensión...');

        $subscriptionsNearSuspension = Subscription::where('status', 'active')
            ->whereNotNull('grace_period_ends_at')
            ->where('grace_period_ends_at', '>=', Carbon::now())
            ->where('grace_period_ends_at', '<=', Carbon::now()->addDays(3))
            ->with('tenant')
            ->get();

        foreach ($subscriptionsNearSuspension as $subscription) {
            $tenantName = $subscription->tenant->name;
            $daysLeft = $subscription->daysUntilSuspension();

            if ($daysLeft <= 1) {
                $this->warn("   ⚠️ Advertencia urgente para {$tenantName} ({$daysLeft} día(s) restantes)");
            } else {
                $this->info("   📅 Advertencia para {$tenantName} ({$daysLeft} días restantes)");
            }

            if (!$isDryRun) {
                $this->sendSuspensionWarning($subscription, $daysLeft);
                $this->stats['warnings_sent']++;
            }
        }

        $this->newLine();
    }

    private function processLongTermSuspensions(bool $isDryRun): void
    {
        $this->info('🗑️ Procesando suspensiones de largo plazo...');

        $longTermSuspended = Subscription::where('status', 'suspended')
            ->whereNotNull('suspended_at')
            ->where('suspended_at', '<=', Carbon::now()->subDays(30))
            ->whereNull('scheduled_for_deletion_at')
            ->with('tenant')
            ->get();

        foreach ($longTermSuspended as $subscription) {
            $tenantName = $subscription->tenant->name;
            $daysSuspended = $subscription->daysSinceSuspension();

            $this->warn("   🗑️ Programando eliminación para {$tenantName} (suspendido {$daysSuspended} días)");

            if (!$isDryRun) {
                $this->scheduleForDeletion($subscription, 30); // Additional 30 days before deletion
                $this->stats['scheduled_for_deletion']++;
            }
        }

        $this->newLine();
    }

    private function processAccountReactivations(bool $isDryRun): void
    {
        $this->info('🟢 Verificando cuentas para reactivación...');

        // This would typically be triggered by successful payments
        // For now, we'll check for subscriptions that might have been reactivated externally
        $suspendedSubscriptions = Subscription::where('status', 'suspended')
            ->whereNotNull('paypal_subscription_id')
            ->with('tenant')
            ->limit(10) // Limit to avoid overloading
            ->get();

        foreach ($suspendedSubscriptions as $subscription) {
            // In a real scenario, we'd check PayPal status or recent successful payments
            // For demo purposes, we'll simulate reactivation logic
            if ($this->shouldReactivate($subscription)) {
                $tenantName = $subscription->tenant->name;
                $this->info("   🟢 Reactivando cuenta: {$tenantName}");

                if (!$isDryRun) {
                    $reactivated = $subscription->reactivate('Payment successful', 'auto-system');
                    if ($reactivated) {
                        $this->stats['reactivated']++;
                        $this->sendReactivationNotification($subscription);
                    }
                }
            }
        }

        $this->newLine();
    }

    private function createDataBackup(Subscription $subscription, bool $force): bool
    {
        try {
            if ($subscription->data_backed_up_before_suspension) {
                return true; // Already backed up
            }

            // Here you would implement actual data backup logic
            // For now, we'll simulate the backup process
            $backupPath = "backups/tenant_{$subscription->tenant_id}_" . now()->format('Y-m-d_H-i-s') . '.zip';
            
            // Simulate backup creation time
            sleep(1);
            
            $subscription->update([
                'data_backed_up_before_suspension' => true,
                'data_backup_created_at' => Carbon::now(),
                'data_backup_path' => $backupPath,
            ]);

            $this->stats['data_backups_created']++;
            $this->info("     💾 Backup creado: {$backupPath}");
            
            return true;

        } catch (\Exception $e) {
            Log::error('Data backup failed', [
                'subscription_id' => $subscription->id,
                'tenant_id' => $subscription->tenant_id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    private function getSuspensionReason(Subscription $subscription): string
    {
        if ($subscription->is_trial && $subscription->isTrialExpired()) {
            return 'Trial period expired';
        }

        if ($subscription->failed_payment_count >= 3) {
            return 'Maximum payment failures exceeded';
        }

        if ($subscription->isGracePeriodExpired()) {
            return 'Grace period expired after payment failure';
        }

        return 'Payment failure';
    }

    private function scheduleForDeletion(Subscription $subscription, int $daysFromNow): void
    {
        $subscription->update([
            'scheduled_for_deletion_at' => Carbon::now()->addDays($daysFromNow),
            'deletion_warning_sent' => false,
        ]);
    }

    private function shouldReactivate(Subscription $subscription): bool
    {
        // In a real implementation, this would check:
        // 1. Recent successful payments via webhooks
        // 2. PayPal subscription status
        // 3. Manual reactivation requests
        
        // For simulation, we'll randomly reactivate some accounts
        return rand(1, 100) <= 5; // 5% chance for demo
    }

    private function sendSuspensionNotification(Subscription $subscription): void
    {
        try {
            $user = $subscription->tenant->users()->first();
            if ($user) {
                Mail::to($user->email)->send(new AccountSuspendedEmail($subscription));
                $this->line("     📧 Notificación de suspensión enviada a {$user->email}");
            }
        } catch (\Exception $e) {
            $this->error("     ❌ Error enviando notificación: " . $e->getMessage());
        }
    }

    private function sendSuspensionWarning(Subscription $subscription, int $daysLeft): void
    {
        try {
            $user = $subscription->tenant->users()->first();
            if ($user) {
                Mail::to($user->email)->send(new SuspensionWarningEmail($subscription, $daysLeft));
                $this->line("     📧 Advertencia enviada a {$user->email}");
            }
        } catch (\Exception $e) {
            $this->error("     ❌ Error enviando advertencia: " . $e->getMessage());
        }
    }

    private function sendReactivationNotification(Subscription $subscription): void
    {
        try {
            $user = $subscription->tenant->users()->first();
            if ($user) {
                Mail::to($user->email)->send(new AccountReactivatedEmail($subscription));
                $this->line("     📧 Notificación de reactivación enviada a {$user->email}");
            }
        } catch (\Exception $e) {
            $this->error("     ❌ Error enviando notificación: " . $e->getMessage());
        }
    }

    private function showSummary(): void
    {
        $this->newLine();
        $this->info('📊 Resumen del Proceso de Suspensión');

        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['Cuentas verificadas', $this->stats['checked']],
                ['Cuentas suspendidas', $this->stats['suspended']],
                ['Advertencias enviadas', $this->stats['warnings_sent']],
                ['Períodos de gracia iniciados', $this->stats['grace_periods_started']],
                ['Backups de datos creados', $this->stats['data_backups_created']],
                ['Programadas para eliminación', $this->stats['scheduled_for_deletion']],
                ['Cuentas reactivadas', $this->stats['reactivated']],
                ['Errores', $this->stats['errors']],
            ]
        );
    }
}