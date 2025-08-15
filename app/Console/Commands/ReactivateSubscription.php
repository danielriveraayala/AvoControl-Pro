<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Subscription;

class ReactivateSubscription extends Command
{
    protected $signature = 'subscription:reactivate {--email=} {--reason=}';
    protected $description = 'Reactivate a suspended subscription';

    public function handle()
    {
        $this->info('🔄 Reactivating Subscription...');
        $this->newLine();

        $email = $this->option('email') ?: 'danielriveraayala@kreativos.pro';
        $reason = $this->option('reason') ?: 'Manual reactivation via command';

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("❌ User not found: $email");
            return 1;
        }

        $this->info("👤 Reactivating subscription for: {$user->name} ({$user->email})");

        if (!$user->current_tenant_id) {
            $this->error("❌ User has no current tenant");
            return 1;
        }

        $subscription = Subscription::where('tenant_id', $user->current_tenant_id)->latest()->first();

        if (!$subscription) {
            $this->error("❌ No subscription found for user's tenant");
            return 1;
        }

        $this->info("📋 Current subscription status:");
        $this->line("   ID: {$subscription->id}");
        $this->line("   Status: {$subscription->status}");
        $this->line("   Plan: {$subscription->plan}");

        if ($subscription->status === 'active') {
            $this->warn("⚠️  Subscription is already active!");
            return 0;
        }

        if (!$subscription->canBeReactivated() && $subscription->status === 'suspended') {
            $this->error("❌ Subscription cannot be reactivated (suspended too long)");
            return 1;
        }

        // Reactivate the subscription
        try {
            $reactivated = $subscription->reactivate($reason, 'command-admin');

            if ($reactivated) {
                $this->info("✅ Subscription reactivated successfully!");
                $this->line("   New Status: active");
                $this->line("   Reactivated At: " . $subscription->fresh()->reactivated_at);
                $this->line("   Reason: $reason");

                // Check tenant status
                $tenant = $subscription->tenant;
                $tenant->refresh();
                $this->line("   Tenant Status: " . $tenant->status);

                $this->newLine();
                $this->info("🎉 User can now access the system normally!");
            } else {
                $this->error("❌ Failed to reactivate subscription");
                return 1;
            }

        } catch (\Exception $e) {
            $this->error("❌ Error reactivating subscription: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}