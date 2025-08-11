<?php

namespace App\Mail\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewLotReceived extends Mailable
{
    use Queueable, SerializesModels;

    public $lot;
    public $inventory;
    public $priceAnalysis;
    public $thresholdValue;

    /**
     * Create a new message instance.
     */
    public function __construct($lotData = [], $inventoryImpact = [], $priceAnalysis = [], $thresholdValue = 50000)
    {
        $this->lot = $lotData;
        $this->inventory = $inventoryImpact;
        $this->priceAnalysis = $priceAnalysis;
        $this->thresholdValue = $thresholdValue;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('🚚 Nuevo Lote de Alto Valor Recibido - ' . ($this->lot['code'] ?? 'N/A') . ' - AvoControl Pro')
                    ->view('emails.notifications.new-lot-received')
                    ->with([
                        'lot' => $this->lot,
                        'inventory' => $this->inventory,
                        'price_analysis' => $this->priceAnalysis,
                        'threshold_value' => $this->thresholdValue,
                        'title' => 'Nuevo Lote Recibido'
                    ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray()
    {
        return [
            'type' => 'new_lot_received',
            'lot_code' => $this->lot['code'] ?? 'N/A',
            'supplier_name' => $this->lot['supplier_name'] ?? 'N/A',
            'total_weight' => $this->lot['total_weight'] ?? 0,
            'total_value' => $this->lot['total_value'] ?? 0,
            'quality_name' => $this->lot['quality_name'] ?? 'N/A',
            'timestamp' => now()->toDateTimeString(),
        ];
    }

    /**
     * Prepare data for the email template
     */
    public static function prepareData($lotModel = null, $thresholdValue = 50000)
    {
        // Mock data for demonstration - replace with real lot data
        if (is_null($lotModel)) {
            $lotData = [
                'id' => 1,
                'code' => 'LOT-' . now()->format('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'supplier_name' => 'Agropecuaria San Miguel S.P.R. de R.L.',
                'total_weight' => 2500,
                'total_value' => 187500.00,
                'price_per_kg' => 75.00,
                'quality_name' => 'Premium Exportación',
                'quality_color' => '#28a745',
                'received_date' => now()->format('d/m/Y H:i'),
                'status_label' => 'Disponible',
                'notes' => 'Lote de alta calidad con calibres 84-120, aguacate de temporada alta',
                'requires_special_handling' => true,
                'quality_details' => [
                    'caliber_range' => '84-120',
                    'weight_range' => '180-350',
                    'expected_yield' => '95'
                ]
            ];
        } else {
            // Convert model to array format for email
            $lotData = [
                'id' => $lotModel->id,
                'code' => $lotModel->code,
                'supplier_name' => $lotModel->supplier->name ?? 'Sin proveedor',
                'total_weight' => $lotModel->total_weight,
                'total_value' => $lotModel->total_cost,
                'price_per_kg' => $lotModel->cost_per_kg,
                'quality_name' => $lotModel->qualityGrade->name ?? 'Sin calidad',
                'quality_color' => $lotModel->qualityGrade->color ?? '#6c757d',
                'received_date' => $lotModel->created_at->format('d/m/Y H:i'),
                'status_label' => $lotModel->status_label,
                'notes' => $lotModel->notes ?? 'Sin observaciones',
                'requires_special_handling' => false
            ];
        }

        // Inventory impact calculation
        $inventoryImpact = [
            'previous_weight' => 15420,
            'new_weight' => 15420 + $lotData['total_weight'],
            'previous_value' => 1250000.00,
            'new_value' => 1250000.00 + $lotData['total_value'],
            'previous_lots' => 32,
            'new_lots' => 33
        ];

        // Price analysis
        $priceAnalysis = [
            'comparison_with_average' => [
                'status' => $lotData['price_per_kg'] > 70 ? 'por encima' : 'por debajo',
                'percentage' => abs((($lotData['price_per_kg'] - 68.50) / 68.50) * 100)
            ],
            'market_trend' => $lotData['price_per_kg'] > 70 ? 'Al alza' : 'Estable',
            'recommendation' => $lotData['price_per_kg'] > 70 
                ? 'Considere precios de venta competitivos para asegurar rotación rápida'
                : 'Excelente oportunidad de compra, considere aumentar margen de ganancia'
        ];

        return [
            'lotData' => $lotData,
            'inventoryImpact' => $inventoryImpact,
            'priceAnalysis' => $priceAnalysis
        ];
    }
}