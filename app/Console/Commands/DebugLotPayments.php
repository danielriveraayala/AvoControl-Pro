<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lot;

class DebugLotPayments extends Command
{
    protected $signature = 'debug:lot-payments {lot_id}';
    protected $description = 'Debug lot payment information';

    public function handle()
    {
        $lotId = $this->argument('lot_id');
        $lot = Lot::with(['payments.createdBy', 'supplier'])->find($lotId);
        
        if (!$lot) {
            $this->error("Lot {$lotId} not found");
            return 1;
        }

        $this->info("=== LOT {$lot->lot_code} DEBUG ===");
        $this->info("Total Purchase Cost: $" . number_format($lot->total_purchase_cost, 2));
        $this->info("DB Amount Paid: $" . number_format($lot->amount_paid ?? 0, 2));
        $this->info("DB Amount Owed: $" . number_format($lot->amount_owed ?? 0, 2));
        $this->info("Payment Status: " . $lot->payment_status);
        
        $calculatedPaid = $lot->payments->sum('amount');
        $calculatedOwed = $lot->total_purchase_cost - $calculatedPaid;
        
        $this->info("Calculated Paid: $" . number_format($calculatedPaid, 2));
        $this->info("Calculated Owed: $" . number_format($calculatedOwed, 2));
        $this->info("Payments Count: " . $lot->payments->count());
        
        if ($lot->payments->count() > 0) {
            $this->info("\n=== PAYMENTS ===");
            foreach ($lot->payments as $payment) {
                $this->line("- Code: {$payment->payment_code}, Date: {$payment->payment_date->format('Y-m-d H:i')}, Amount: $" . number_format($payment->amount, 2) . ", Method: {$payment->payment_method}");
            }
        }
        
        return 0;
    }
}