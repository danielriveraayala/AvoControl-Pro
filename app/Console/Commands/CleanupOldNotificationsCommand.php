<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use Carbon\Carbon;

class CleanupOldNotificationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:cleanup {--days=30 : Number of days to keep notifications} {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old notifications from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $days = $this->option('days');
            $dryRun = $this->option('dry-run');
            
            $cutoffDate = Carbon::now()->subDays($days);
            
            $this->info("🧹 Cleaning up notifications older than {$days} days (before {$cutoffDate->format('Y-m-d H:i:s')})");
            
            if ($dryRun) {
                $this->info("🔍 DRY RUN MODE - No notifications will actually be deleted");
            }
            
            // Get old notifications
            $oldNotifications = Notification::where('created_at', '<', $cutoffDate);
            $totalCount = $oldNotifications->count();
            
            if ($totalCount === 0) {
                $this->info('✅ No old notifications found to cleanup');
                return 0;
            }
            
            // Show summary by type
            $summaryByType = $oldNotifications->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->get()
                ->pluck('count', 'type')
                ->toArray();
            
            $this->info("📊 Found {$totalCount} notifications to cleanup:");
            foreach ($summaryByType as $type => $count) {
                $this->line("   - {$type}: {$count} notifications");
            }
            
            if ($dryRun) {
                $this->info('✅ Dry run completed - no notifications were deleted');
                return 0;
            }
            
            // Confirm deletion
            if ($totalCount > 100 && !$this->confirm("Are you sure you want to delete {$totalCount} notifications?")) {
                $this->info('❌ Cleanup cancelled');
                return 0;
            }
            
            // Delete old notifications
            $deletedCount = $oldNotifications->delete();
            
            $this->info("✅ Successfully deleted {$deletedCount} old notifications");
            
            // Show remaining notifications count
            $remainingCount = Notification::count();
            $this->info("📊 Remaining notifications in database: {$remainingCount}");
            
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error during cleanup: " . $e->getMessage());
            return 1;
        }
    }
}