<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Models\User;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CheckOverduePaymentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:check-overdue-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for overdue payments and send reminder notifications via email, push and database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('💳 Checking overdue payments...');
            
            // Get overdue customers (negative balance means they owe money)
            $overdueCustomers = $this->getOverdueCustomers();
            
            if ($overdueCustomers->isEmpty()) {
                $this->info('✅ No overdue payments found');
                return 0;
            }

            // Get users who should receive payment alerts
            $users = User::whereIn('role', ['super_admin', 'admin', 'contador'])->get();
            
            if ($users->isEmpty()) {
                $this->error('❌ No users found to send payment notifications');
                return 1;
            }

            $notificationsSent = 0;
            $totalOverdueAmount = $overdueCustomers->sum('balance');
            
            // Send summary notification
            foreach ($users as $user) {
                $customerList = $overdueCustomers->map(function($customer) {
                    return "• {$customer->name}: $" . number_format(abs($customer->balance), 2);
                })->take(5)->implode("\n");
                
                $moreCustomers = $overdueCustomers->count() > 5 ? "\n... y " . ($overdueCustomers->count() - 5) . " clientes más." : "";
                
                $notification = Notification::create([
                    'type' => 'payment_overdue',
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->id,
                    'data' => [
                        'title' => "💰 Cuentas por Cobrar Vencidas",
                        'message' => "Tienes {$overdueCustomers->count()} cliente(s) con pagos vencidos por un total de $" . number_format(abs($totalOverdueAmount), 2) . ":\n\n{$customerList}{$moreCustomers}",
                        'action_url' => route('customers.index'),
                        'action_text' => 'Ver Clientes',
                        'total_overdue' => abs($totalOverdueAmount),
                        'customers_count' => $overdueCustomers->count()
                    ],
                    'priority' => abs($totalOverdueAmount) > 50000 ? 'high' : 'normal',
                    'channels' => ['email', 'push', 'database'],
                    'category' => 'payment',
                    'metadata' => [
                        'overdue_customers' => $overdueCustomers->pluck('id')->toArray(),
                        'total_amount' => abs($totalOverdueAmount),
                        'checked_at' => Carbon::now()->toISOString()
                    ]
                ]);
                
                $notificationsSent++;
            }

            $this->info("✅ Sent {$notificationsSent} overdue payment notifications for {$overdueCustomers->count()} customers");
            $this->info("💰 Total overdue amount: $" . number_format(abs($totalOverdueAmount), 2));
            
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error checking overdue payments: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Get customers with negative balance (overdue payments)
     */
    private function getOverdueCustomers()
    {
        return Customer::select([
                'id', 
                'name', 
                'balance',
                'updated_at'
            ])
            ->where('balance', '<', 0)
            ->orderBy('balance', 'asc') // Most negative (highest debt) first
            ->get();
    }
}