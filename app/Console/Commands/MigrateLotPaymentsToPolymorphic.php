<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LotPayment;
use App\Models\Payment;
use App\Models\Lot;
use Illuminate\Support\Facades\DB;

class MigrateLotPaymentsToPolymorphic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:migrate-lot-payments {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate lot_payments to polymorphic payments table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        } else {
            $this->warn('⚠️  LIVE MODE - This will migrate data permanently');
            if (!$this->confirm('Do you want to continue?')) {
                $this->info('Migration cancelled.');
                return;
            }
        }

        // Get all lot payments
        $lotPayments = LotPayment::all();
        
        if ($lotPayments->isEmpty()) {
            $this->info('No lot payments found to migrate.');
            return;
        }

        $this->info("Found {$lotPayments->count()} lot payments to migrate...");
        
        $migrated = 0;
        $errors = 0;

        DB::transaction(function () use ($lotPayments, $dryRun, &$migrated, &$errors) {
            foreach ($lotPayments as $lotPayment) {
                try {
                    // Check if lot exists
                    $lot = Lot::find($lotPayment->lot_id);
                    if (!$lot) {
                        $this->error("Lot ID {$lotPayment->lot_id} not found for payment {$lotPayment->id}");
                        $errors++;
                        continue;
                    }

                    // Generate payment code
                    $paymentCode = 'MIG-' . str_pad($lotPayment->id, 6, '0', STR_PAD_LEFT);

                    // Map payment type to method
                    $methodMap = [
                        'efectivo' => 'cash',
                        'transferencia' => 'transfer', 
                        'cheque' => 'check',
                        'deposito' => 'transfer',
                        'otro' => 'cash'
                    ];
                    $paymentMethod = $methodMap[$lotPayment->payment_type] ?? 'cash';

                    $migrationData = [
                        'payment_code' => $paymentCode,
                        'type' => 'expense',
                        'concept' => 'lot_purchase',
                        'payable_type' => Lot::class,
                        'payable_id' => $lotPayment->lot_id,
                        'amount' => $lotPayment->amount,
                        'payment_date' => $lotPayment->payment_date,
                        'payment_method' => $paymentMethod,
                        'reference' => null,
                        'notes' => $lotPayment->notes,
                        'created_by' => $lotPayment->paid_by ?? 1,
                        'created_at' => $lotPayment->created_at,
                        'updated_at' => $lotPayment->updated_at
                    ];

                    if (!$dryRun) {
                        // Check if already migrated
                        $existing = Payment::where('payable_type', Lot::class)
                                          ->where('payable_id', $lotPayment->lot_id)
                                          ->where('amount', $lotPayment->amount)
                                          ->where('payment_date', $lotPayment->payment_date)
                                          ->first();
                        
                        if ($existing) {
                            $this->warn("Payment already exists for lot {$lotPayment->lot_id}, amount {$lotPayment->amount} - skipping");
                            continue;
                        }

                        Payment::create($migrationData);
                    }

                    $migrated++;
                    $this->line("✅ Migrated payment {$lotPayment->id} -> Lot {$lotPayment->lot_id} (\${$lotPayment->amount})");

                } catch (\Exception $e) {
                    $this->error("Failed to migrate payment {$lotPayment->id}: " . $e->getMessage());
                    $errors++;
                }
            }
        });

        $this->newLine();
        $this->info("🎉 Migration Summary:");
        $this->info("✅ Successfully migrated: {$migrated}");
        if ($errors > 0) {
            $this->error("❌ Errors: {$errors}");
        }
        
        if ($dryRun) {
            $this->info('🔍 This was a dry run - no actual changes made');
            $this->info('Run without --dry-run to perform actual migration');
        } else {
            $this->info('✅ Migration completed successfully!');
            $this->warn('⚠️  Remember to update lot payment statuses after migration');
        }
    }
}
