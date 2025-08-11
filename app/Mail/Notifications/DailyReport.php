<?php

namespace App\Mail\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DailyReport extends Mailable
{
    use Queueable, SerializesModels;

    public $date;
    public $summary;
    public $salesByQuality;
    public $topCustomers;
    public $inventory;
    public $finance;
    public $alerts;
    public $comparison;
    public $reportTime;

    /**
     * Create a new message instance.
     */
    public function __construct($reportData = [], $date = null, $reportTime = '8:00 AM')
    {
        $this->date = $date ?? now()->format('d/m/Y');
        $this->summary = $reportData['summary'] ?? [];
        $this->salesByQuality = $reportData['salesByQuality'] ?? [];
        $this->topCustomers = $reportData['topCustomers'] ?? [];
        $this->inventory = $reportData['inventory'] ?? [];
        $this->finance = $reportData['finance'] ?? [];
        $this->alerts = $reportData['alerts'] ?? [];
        $this->comparison = $reportData['comparison'] ?? [];
        $this->reportTime = $reportTime;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('📊 Reporte Diario de Operaciones - ' . $this->date . ' - AvoControl Pro')
                    ->view('emails.notifications.daily-report')
                    ->with([
                        'date' => $this->date,
                        'summary' => $this->summary,
                        'salesByQuality' => $this->salesByQuality,
                        'topCustomers' => $this->topCustomers,
                        'inventory' => $this->inventory,
                        'finance' => $this->finance,
                        'alerts' => $this->alerts,
                        'comparison' => $this->comparison,
                        'report_time' => $this->reportTime,
                        'title' => 'Reporte Diario de Operaciones'
                    ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray()
    {
        return [
            'type' => 'daily_report',
            'date' => $this->date,
            'total_sales' => $this->summary['total_sales'] ?? 0,
            'sales_count' => $this->summary['sales_count'] ?? 0,
            'total_weight' => $this->summary['total_weight'] ?? 0,
            'new_customers' => $this->summary['new_customers'] ?? 0,
            'timestamp' => now()->toDateTimeString(),
        ];
    }

    /**
     * Prepare data for the email template
     */
    public static function prepareData($date = null)
    {
        $date = $date ?? now()->format('d/m/Y');
        
        // Mock data for demonstration - replace with real business logic
        $reportData = [
            'summary' => [
                'total_sales' => 245000.00,
                'sales_count' => 18,
                'total_weight' => 3850,
                'new_customers' => 2
            ],
            'salesByQuality' => [
                [
                    'name' => 'Premium Exportación',
                    'color' => '#28a745',
                    'weight' => 1250,
                    'sales' => 95000.00,
                    'avg_price' => 76.00,
                    'percentage' => 38.8
                ],
                [
                    'name' => 'Nacional Primera',
                    'color' => '#17a2b8',
                    'weight' => 1480,
                    'sales' => 89000.00,
                    'avg_price' => 60.14,
                    'percentage' => 36.3
                ],
                [
                    'name' => 'Nacional Segunda',
                    'color' => '#ffc107',
                    'weight' => 850,
                    'sales' => 42500.00,
                    'avg_price' => 50.00,
                    'percentage' => 17.3
                ],
                [
                    'name' => 'Industrial',
                    'color' => '#6c757d',
                    'weight' => 270,
                    'sales' => 18500.00,
                    'avg_price' => 68.52,
                    'percentage' => 7.6
                ]
            ],
            'topCustomers' => [
                [
                    'name' => 'Exportadora del Pacífico S.A.',
                    'total_purchases' => 85000.00,
                    'total_weight' => 1120
                ],
                [
                    'name' => 'Comercializadora Norte',
                    'total_purchases' => 62000.00,
                    'total_weight' => 980
                ],
                [
                    'name' => 'Frutas Premium Internacional',
                    'total_purchases' => 45000.00,
                    'total_weight' => 720
                ],
                [
                    'name' => 'Distribuidora Central',
                    'total_purchases' => 32000.00,
                    'total_weight' => 650
                ],
                [
                    'name' => 'Agroindustrial del Valle',
                    'total_purchases' => 21000.00,
                    'total_weight' => 380
                ]
            ],
            'inventory' => [
                'available_weight' => 12450,
                'active_lots' => 28,
                'low_stock_count' => 3,
                'total_value' => 890000.00
            ],
            'finance' => [
                'payments_received' => 125000.00,
                'payments_made' => 85000.00,
                'net_cash_flow' => 40000.00,
                'pending_collections' => 320000.00
            ],
            'alerts' => [
                'Inventario bajo detectado en 3 calidades (Premium, Nacional Primera, Industrial)',
                '2 pagos de proveedores próximos a vencer en los próximos 3 días',
                'Meta mensual de ventas alcanzada al 78% (faltando 8 días del mes)'
            ],
            'comparison' => [
                'sales_today' => 245000.00,
                'sales_yesterday' => 198000.00,
                'sales_variance' => 23.7, // positive = increase
                'weight_today' => 3850,
                'weight_yesterday' => 3200,
                'weight_variance' => 20.3
            ]
        ];

        return $reportData;
    }
}