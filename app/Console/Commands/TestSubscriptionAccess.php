<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Subscription;

class TestSubscriptionAccess extends Command
{
    protected $signature = 'subscription:test-access {--email=}';
    protected $description = 'Test subscription access restrictions for a user';

    public function handle()
    {
        $this->info('🔐 Testing Subscription Access Control...');
        $this->newLine();

        $email = $this->option('email') ?: 'danielriveraayala@kreativos.pro';
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("❌ User not found: $email");
            return 1;
        }

        $this->info("👤 Testing access for: {$user->name} ({$user->email})");
        $this->line("   Role: " . ($user->getPrimaryRole()?->name ?? 'No role'));
        $this->line("   Current Tenant ID: " . ($user->current_tenant_id ?? 'None'));

        if (!$user->current_tenant_id) {
            $this->warn("⚠️  User has no current tenant - would be redirected to tenant selection");
            return 0;
        }

        // Check subscription
        $subscription = Subscription::where('tenant_id', $user->current_tenant_id)->latest()->first();

        if (!$subscription) {
            $this->warn("⚠️  No subscription found for tenant - would be redirected to subscription registration");
            return 0;
        }

        $this->newLine();
        $this->info("📋 Subscription Details:");
        $this->line("   ID: {$subscription->id}");
        $this->line("   Plan: {$subscription->plan}");
        $this->line("   Status: {$subscription->status}");
        $this->line("   PayPal ID: {$subscription->paypal_subscription_id}");

        if ($subscription->suspended_at) {
            $this->line("   Suspended At: {$subscription->suspended_at}");
            $this->line("   Suspension Reason: " . ($subscription->suspension_reason ?? 'N/A'));
        }

        if ($subscription->cancelled_at) {
            $this->line("   Cancelled At: {$subscription->cancelled_at}");
            $this->line("   Cancellation Reason: " . ($subscription->cancellation_reason ?? 'N/A'));
        }

        $this->newLine();
        $this->info("🔍 Access Control Analysis:");

        // Check super admin bypass
        if ($user->isSuperAdmin()) {
            $this->info("✅ Super Admin - Access GRANTED (bypasses all subscription checks)");
            return 0;
        }

        // Check subscription status
        switch ($subscription->status) {
            case 'active':
                if ($subscription->isInGracePeriod()) {
                    $daysLeft = $subscription->daysUntilSuspension();
                    $this->warn("⚠️  Access GRANTED with warning - In grace period ($daysLeft days left)");
                } elseif ($subscription->is_trial && $subscription->trial_ends_at) {
                    $daysLeft = $subscription->trial_ends_at->diffInDays(now());
                    if ($daysLeft <= 3) {
                        $this->warn("⚠️  Access GRANTED with warning - Trial ending in $daysLeft days");
                    } else {
                        $this->info("✅ Access GRANTED - Active subscription");
                    }
                } else {
                    $this->info("✅ Access GRANTED - Active subscription");
                }
                break;

            case 'suspended':
                if ($subscription->canBeReactivated()) {
                    $this->error("❌ Access BLOCKED - Suspended subscription (can be reactivated)");
                    $this->line("   Would show: Suspension page with reactivation options");
                } else {
                    $this->error("❌ Access BLOCKED - Permanently suspended subscription");
                    $this->line("   Would show: Suspension page (no reactivation)");
                }
                break;

            case 'cancelled':
                $this->error("❌ Access BLOCKED - Cancelled subscription");
                $this->line("   Would show: Cancellation page with new subscription options");
                break;

            default:
                $this->error("❌ Access BLOCKED - Unknown subscription status: {$subscription->status}");
                break;
        }

        // Check tenant status
        $tenant = $subscription->tenant;
        $this->newLine();
        $this->info("🏢 Tenant Status:");
        $this->line("   Name: {$tenant->name}");
        $this->line("   Status: {$tenant->status}");

        if ($tenant->status !== 'active') {
            $this->error("❌ Tenant is not active - Would also block access");
        }

        // Show recent payments
        $this->newLine();
        $this->info("💰 Recent Payment Activity:");
        $recentPayments = $subscription->payments()->orderBy('created_at', 'desc')->limit(3)->get();

        if ($recentPayments->count() > 0) {
            foreach ($recentPayments as $payment) {
                $statusIcon = $payment->status === 'completed' ? '✅' : 
                            ($payment->status === 'failed' ? '❌' : 
                            ($payment->status === 'refunded' ? '🔄' : '⏳'));
                
                $this->line("   $statusIcon {$payment->type} - {$payment->amount} {$payment->currency} - {$payment->status} - {$payment->created_at->format('d/m/Y H:i')}");
            }
        } else {
            $this->line("   No payment records found");
        }

        $this->newLine();
        $this->info("🎯 Result Summary:");
        
        if ($user->isSuperAdmin()) {
            $this->info("✅ User would have FULL ACCESS (Super Admin)");
        } elseif ($subscription->status === 'active') {
            $this->info("✅ User would have NORMAL ACCESS");
        } else {
            $this->error("❌ User would be BLOCKED and see suspension/cancellation page");
        }

        return 0;
    }
}