<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lot;

class UpdateLotPaymentAmounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lots:update-payment-amounts {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update amount_paid and amount_owed columns for all lots based on polymorphic payments';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('Running in DRY RUN mode - no changes will be made');
        }

        $lots = Lot::with('payments')->get();
        $this->info("Processing {$lots->count()} lots...");

        $updated = 0;
        $issues = [];

        foreach ($lots as $lot) {
            $totalPaid = $lot->payments->sum('amount');
            $calculatedOwed = $lot->total_purchase_cost - $totalPaid;
            
            $currentPaid = $lot->amount_paid ?: 0;
            $currentOwed = $lot->amount_owed ?: $lot->total_purchase_cost;

            if (abs($currentPaid - $totalPaid) > 0.01 || abs($currentOwed - $calculatedOwed) > 0.01) {
                $this->warn("Lot {$lot->lot_code} needs update:");
                $this->line("  Current: paid=" . number_format($currentPaid, 2) . ", owed=" . number_format($currentOwed, 2));
                $this->line("  Should be: paid=" . number_format($totalPaid, 2) . ", owed=" . number_format($calculatedOwed, 2));
                
                if (!$isDryRun) {
                    $lot->updatePaymentAmounts();
                    $this->info("  ✓ Updated");
                }
                
                $updated++;
            }
        }

        if ($isDryRun) {
            $this->info("\nDRY RUN COMPLETE:");
            $this->info("Found {$updated} lots that need updates");
            $this->info("Run without --dry-run to apply changes");
        } else {
            $this->info("\nUPDATE COMPLETE:");
            $this->info("Updated {$updated} lots");
        }

        return 0;
    }
}