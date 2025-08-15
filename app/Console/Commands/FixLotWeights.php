<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lot;
use App\Models\SaleLotAllocation;
use Illuminate\Support\Facades\DB;

class FixLotWeights extends Command
{
    protected $signature = 'lots:fix-weights {--dry-run : Run without making changes}';
    protected $description = 'Fix inconsistent lot weight_sold and weight_available fields';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('Running in DRY RUN mode - no changes will be made');
        }

        $lots = Lot::where('status', '!=', 'cancelled')->get();
        $this->info("Checking {$lots->count()} lots for weight inconsistencies...");

        $fixed = 0;
        $issues = [];

        foreach ($lots as $lot) {
            // Calculate actual sold weight from allocations
            $actualSoldWeight = SaleLotAllocation::where('lot_id', $lot->id)->sum('allocated_weight');
            $calculatedAvailable = $lot->total_weight - $actualSoldWeight;
            
            $currentSold = $lot->weight_sold ?: 0;
            $currentAvailable = $lot->weight_available ?: $lot->total_weight;

            if (abs($currentSold - $actualSoldWeight) > 0.01 || abs($currentAvailable - $calculatedAvailable) > 0.01) {
                $this->warn("Lot {$lot->lot_code} (ID: {$lot->id}) has inconsistent weights:");
                $this->line("  Current: sold={$currentSold}kg, available={$currentAvailable}kg");
                $this->line("  Should be: sold={$actualSoldWeight}kg, available={$calculatedAvailable}kg");
                
                if (!$isDryRun) {
                    $lot->weight_sold = $actualSoldWeight;
                    $lot->weight_available = $calculatedAvailable;
                    $lot->save();
                    $this->info("  ✓ Fixed");
                }
                
                $fixed++;
                $issues[] = [
                    'lot_code' => $lot->lot_code,
                    'current_sold' => $currentSold,
                    'actual_sold' => $actualSoldWeight,
                    'current_available' => $currentAvailable,
                    'calculated_available' => $calculatedAvailable
                ];
            }
        }

        if ($isDryRun) {
            $this->info("\nDRY RUN COMPLETE:");
            $this->info("Found {$fixed} lots with weight inconsistencies");
            $this->info("Run without --dry-run to apply fixes");
        } else {
            $this->info("\nFIX COMPLETE:");
            $this->info("Fixed {$fixed} lots with inconsistent weights");
        }

        return 0;
    }
}