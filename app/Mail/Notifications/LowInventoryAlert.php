<?php

namespace App\Mail\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LowInventoryAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $lowStockQualities;
    public $summary;
    public $frequency;

    /**
     * Create a new message instance.
     */
    public function __construct($lowStockQualities = [], $summary = [], $frequency = '4 horas')
    {
        $this->lowStockQualities = $lowStockQualities;
        $this->summary = $summary;
        $this->frequency = $frequency;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('🚨 Alerta de Inventario Bajo - AvoControl Pro')
                    ->view('emails.notifications.low-inventory-alert')
                    ->with([
                        'lowStockQualities' => $this->lowStockQualities,
                        'summary' => $this->summary,
                        'frequency' => $this->frequency,
                        'title' => 'Alerta de Inventario Bajo'
                    ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray()
    {
        return [
            'type' => 'low_inventory_alert',
            'affected_qualities' => count($this->lowStockQualities),
            'total_shortage' => $this->summary['total_shortage'] ?? 0,
            'critical_count' => $this->summary['critical_count'] ?? 0,
            'timestamp' => now()->toDateTimeString(),
        ];
    }

    /**
     * Prepare data for the email template
     */
    public static function prepareData($inventoryData)
    {
        $lowStockQualities = [];
        $summary = [
            'total_affected' => 0,
            'total_shortage' => 0,
            'critical_count' => 0
        ];

        // Mock data for demonstration - replace with real inventory logic
        if (empty($inventoryData)) {
            $inventoryData = [
                [
                    'name' => 'Premium Exportación',
                    'color' => '#28a745',
                    'current_stock' => 45,
                    'min_limit' => 100,
                    'critical_limit' => 50
                ],
                [
                    'name' => 'Nacional Primera',
                    'color' => '#17a2b8',
                    'current_stock' => 25,
                    'min_limit' => 80,
                    'critical_limit' => 30
                ],
                [
                    'name' => 'Industrial',
                    'color' => '#ffc107',
                    'current_stock' => 65,
                    'min_limit' => 120,
                    'critical_limit' => 40
                ]
            ];
        }

        foreach ($inventoryData as $quality) {
            if ($quality['current_stock'] <= $quality['min_limit']) {
                $lowStockQualities[] = $quality;
                $summary['total_affected']++;
                $summary['total_shortage'] += max(0, $quality['min_limit'] - $quality['current_stock']);
                
                if ($quality['current_stock'] <= $quality['critical_limit']) {
                    $summary['critical_count']++;
                }
            }
        }

        return [
            'lowStockQualities' => $lowStockQualities,
            'summary' => $summary
        ];
    }
}