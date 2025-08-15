<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Subscription;
use Carbon\Carbon;

class CleanupPhantomUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:cleanup-phantom {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup phantom users who registered but never completed payment within 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Starting phantom users cleanup...');
        
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No users will be actually deleted');
        }
        
        // Find users marked for cleanup where 24 hours have passed
        $phantomUsers = User::where('pending_cleanup_at', '<=', Carbon::now())
            ->whereNotNull('pending_cleanup_at')
            ->get();
            
        if ($phantomUsers->count() === 0) {
            $this->info('✅ No phantom users found for cleanup.');
            return;
        }
        
        $this->info("📋 Found {$phantomUsers->count()} phantom users to clean up:");
        
        // Display table of users to be cleaned
        $tableData = [];
        foreach ($phantomUsers as $user) {
            $hasSubscription = Subscription::where('tenant_id', $user->current_tenant_id ?? 0)->exists();
            $hoursOverdue = Carbon::now()->diffInHours($user->pending_cleanup_at);
            
            $tableData[] = [
                'ID' => $user->id,
                'Email' => $user->email,
                'Name' => $user->name,
                'Created' => $user->created_at->format('Y-m-d H:i'),
                'Cleanup Due' => $user->pending_cleanup_at->format('Y-m-d H:i'),
                'Hours Overdue' => $hoursOverdue,
                'Has Subscription' => $hasSubscription ? 'Yes' : 'No'
            ];
        }
        
        $this->table([
            'ID', 'Email', 'Name', 'Created', 'Cleanup Due', 'Hours Overdue', 'Has Subscription'
        ], $tableData);
        
        // Safety check - don't delete users who somehow got subscriptions
        $usersWithSubscriptions = $phantomUsers->filter(function ($user) {
            return Subscription::where('tenant_id', $user->current_tenant_id ?? 0)->exists();
        });
        
        if ($usersWithSubscriptions->count() > 0) {
            $this->warn("⚠️  Warning: {$usersWithSubscriptions->count()} users have subscriptions and will be SKIPPED:");
            foreach ($usersWithSubscriptions as $user) {
                $this->line("   • {$user->email} (ID: {$user->id})");
            }
        }
        
        // Filter out users with subscriptions
        $usersToDelete = $phantomUsers->filter(function ($user) {
            return !Subscription::where('tenant_id', $user->current_tenant_id ?? 0)->exists();
        });
        
        if ($usersToDelete->count() === 0) {
            $this->info('✅ All phantom users have active subscriptions. No cleanup needed.');
            return;
        }
        
        $this->warn("🗑️  Will delete {$usersToDelete->count()} phantom users without subscriptions.");
        
        if (!$dryRun) {
            if (!$this->confirm('Are you sure you want to proceed with the cleanup?')) {
                $this->info('❌ Cleanup cancelled.');
                return;
            }
        }
        
        $deletedCount = 0;
        $skippedCount = 0;
        
        foreach ($usersToDelete as $user) {
            try {
                if (!$dryRun) {
                    // Delete associated tenant if exists and has no other users
                    if ($user->current_tenant_id) {
                        $tenant = $user->currentTenant;
                        if ($tenant) {
                            $otherUsers = User::where('current_tenant_id', $tenant->id)
                                            ->where('id', '!=', $user->id)
                                            ->count();
                            
                            if ($otherUsers === 0) {
                                // Safe to delete tenant as well
                                $tenant->delete();
                                $this->line("   🏢 Deleted tenant: {$tenant->name}");
                            }
                        }
                    }
                    
                    // Delete the user
                    $user->delete();
                    $this->line("   ✅ Deleted user: {$user->email} (ID: {$user->id})");
                } else {
                    $this->line("   🔍 Would delete: {$user->email} (ID: {$user->id})");
                }
                
                $deletedCount++;
                
            } catch (\Exception $e) {
                $this->error("   ❌ Failed to delete user {$user->email}: {$e->getMessage()}");
                $skippedCount++;
            }
        }
        
        // Summary
        $this->info('');
        $this->info('📊 Cleanup Summary:');
        $this->line("   • Total phantom users found: {$phantomUsers->count()}");
        $this->line("   • Users with subscriptions (skipped): {$usersWithSubscriptions->count()}");
        
        if ($dryRun) {
            $this->line("   • Users that would be deleted: {$deletedCount}");
        } else {
            $this->line("   • Users successfully deleted: {$deletedCount}");
            $this->line("   • Users failed to delete: {$skippedCount}");
        }
        
        if (!$dryRun && $deletedCount > 0) {
            \Log::info('Phantom users cleanup completed', [
                'total_found' => $phantomUsers->count(),
                'deleted' => $deletedCount,
                'skipped_with_subscriptions' => $usersWithSubscriptions->count(),
                'failed' => $skippedCount
            ]);
        }
        
        $this->info('✨ Phantom users cleanup completed!');
    }
}
