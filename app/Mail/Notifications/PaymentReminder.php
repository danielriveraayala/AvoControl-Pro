<?php

namespace App\Mail\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $overduePayments;
    public $upcomingPayments;
    public $summary;
    public $criticalActions;
    public $notificationTime;

    /**
     * Create a new message instance.
     */
    public function __construct($overduePayments = [], $upcomingPayments = [], $summary = [], $criticalActions = [], $notificationTime = '9:00 AM')
    {
        $this->overduePayments = $overduePayments;
        $this->upcomingPayments = $upcomingPayments;
        $this->summary = $summary;
        $this->criticalActions = $criticalActions;
        $this->notificationTime = $notificationTime;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('💰 Recordatorio de Pagos Pendientes - AvoControl Pro')
                    ->view('emails.notifications.payment-reminder')
                    ->with([
                        'overduePayments' => $this->overduePayments,
                        'upcomingPayments' => $this->upcomingPayments,
                        'summary' => $this->summary,
                        'criticalActions' => $this->criticalActions,
                        'notification_time' => $this->notificationTime,
                        'title' => 'Recordatorio de Pagos Pendientes'
                    ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray()
    {
        return [
            'type' => 'payment_reminder',
            'overdue_count' => count($this->overduePayments),
            'upcoming_count' => count($this->upcomingPayments),
            'total_overdue_amount' => $this->summary['total_overdue'] ?? 0,
            'total_upcoming_amount' => $this->summary['upcoming_total'] ?? 0,
            'timestamp' => now()->toDateTimeString(),
        ];
    }

    /**
     * Prepare data for the email template
     */
    public static function prepareData($paymentsData = [])
    {
        $overduePayments = [];
        $upcomingPayments = [];
        $summary = [
            'total_overdue' => 0,
            'overdue_count' => 0,
            'upcoming_total' => 0,
            'upcoming_count' => 0
        ];
        $criticalActions = [];

        // Mock data for demonstration - replace with real payment logic
        if (empty($paymentsData)) {
            $paymentsData = [
                // Overdue payments
                [
                    'type' => 'customer',
                    'reference' => 'VEN-2024-001',
                    'entity_name' => 'Exportadora del Pacífico S.A.',
                    'amount' => 85000.00,
                    'due_date' => '15/07/2024',
                    'days_overdue' => 12,
                    'status' => 'overdue'
                ],
                [
                    'type' => 'supplier',
                    'reference' => 'LOT-2024-045',
                    'entity_name' => 'Agropecuaria San Miguel',
                    'amount' => 45000.00,
                    'due_date' => '20/07/2024',
                    'days_overdue' => 7,
                    'status' => 'overdue'
                ],
                // Upcoming payments
                [
                    'type' => 'customer',
                    'reference' => 'VEN-2024-008',
                    'entity_name' => 'Comercializadora Norte',
                    'amount' => 62000.00,
                    'due_date' => '02/08/2024',
                    'days_remaining' => 3,
                    'status' => 'upcoming'
                ],
                [
                    'type' => 'supplier',
                    'reference' => 'LOT-2024-052',
                    'entity_name' => 'Productores Unidos del Valle',
                    'amount' => 38000.00,
                    'due_date' => '04/08/2024',
                    'days_remaining' => 5,
                    'status' => 'upcoming'
                ]
            ];
        }

        // Process payments data
        foreach ($paymentsData as $payment) {
            if ($payment['status'] === 'overdue') {
                $overduePayments[] = $payment;
                $summary['total_overdue'] += $payment['amount'];
                $summary['overdue_count']++;
                
                // Add critical actions for high-value overdue payments
                if ($payment['amount'] > 50000) {
                    $criticalActions[] = "Contactar urgentemente a {$payment['entity_name']} - {$payment['reference']} por $" . number_format($payment['amount'], 2);
                }
            } elseif ($payment['status'] === 'upcoming') {
                $upcomingPayments[] = $payment;
                $summary['upcoming_total'] += $payment['amount'];
                $summary['upcoming_count']++;
            }
        }

        return [
            'overduePayments' => $overduePayments,
            'upcomingPayments' => $upcomingPayments,
            'summary' => $summary,
            'criticalActions' => $criticalActions
        ];
    }
}