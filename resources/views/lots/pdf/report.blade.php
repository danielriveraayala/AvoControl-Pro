<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte Lote {{ $lot->lot_code }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
        }
        .section {
            margin-bottom: 20px;
        }
        .section h3 {
            color: #007bff;
            border-bottom: 1px solid #007bff;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            color: white;
            font-size: 12px;
        }
        .badge-success { background-color: #28a745; }
        .badge-warning { background-color: #ffc107; }
        .badge-info { background-color: #17a2b8; }
        .badge-danger { background-color: #dc3545; }
        .badge-secondary { background-color: #6c757d; }
        .text-success { color: #28a745; }
        .text-danger { color: #dc3545; }
        .progress {
            background-color: #e9ecef;
            height: 20px;
            position: relative;
            border-radius: 3px;
        }
        .progress-bar {
            background-color: #28a745;
            height: 100%;
            border-radius: 3px;
        }
        .notes-box {
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 3px;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #666;
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .two-column {
            display: table;
            width: 100%;
        }
        .column {
            display: table-cell;
            width: 50%;
            padding-right: 10px;
        }
        .column:last-child {
            padding-right: 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🥑 AvoControl Pro</h1>
        <h2>Reporte del Lote {{ $lot->lot_code }}</h2>
        <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="two-column">
        <div class="column">
            <div class="section">
                <h3>Información General</h3>
                <table>
                    <tr>
                        <td><strong>Código:</strong></td>
                        <td>{{ $lot->lot_code }}</td>
                    </tr>
                    <tr>
                        <td><strong>Proveedor:</strong></td>
                        <td>
                            @if($lot->supplier)
                                {{ $lot->supplier->name }}
                                @if($lot->supplier->city)
                                    <br><small style="color: #6c757d;">{{ $lot->supplier->city }}</small>
                                @endif
                            @else
                                <span style="color: #6c757d; font-style: italic;">🕶️ Anónimo</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Fecha de Compra:</strong></td>
                        <td>{{ $lot->harvest_date->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Calidad:</strong></td>
                        <td>
                            @php
                                $badgeClass = match($lot->quality_grade) {
                                    'Primera' => 'success',
                                    'Segunda' => 'warning',
                                    'Tercera' => 'info',
                                    'Industrial' => 'secondary',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge badge-{{ $badgeClass }}">{{ $lot->quality_grade }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Estado:</strong></td>
                        <td>
                            @php
                                $statusClass = match($lot->status) {
                                    'disponible' => 'success',
                                    'vendido_parcial' => 'warning',
                                    'vendido' => 'info',
                                    'cancelado' => 'danger',
                                    'active' => 'success',
                                    'partial' => 'warning',
                                    'sold' => 'info',
                                    default => 'secondary'
                                };
                                $statusText = match($lot->status) {
                                    'disponible' => 'Disponible',
                                    'vendido_parcial' => 'Vendido Parcial',
                                    'vendido' => 'Vendido',
                                    'cancelado' => 'Cancelado',
                                    'active' => 'Activo',
                                    'partial' => 'Parcial',
                                    'sold' => 'Vendido',
                                    default => $lot->status
                                };
                            @endphp
                            <span class="badge badge-{{ $statusClass }}">{{ $statusText }}</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="column">
            <div class="section">
                <h3>Métricas de Peso</h3>
                <table>
                    <tr>
                        <td><strong>Peso Total:</strong></td>
                        <td>{{ number_format($metrics['weight_metrics']['total'], 2) }} kg</td>
                    </tr>
                    <tr>
                        <td><strong>Peso Vendido:</strong></td>
                        <td>{{ number_format($metrics['weight_metrics']['sold'], 2) }} kg</td>
                    </tr>
                    <tr>
                        <td><strong>Peso Disponible:</strong></td>
                        <td>{{ number_format($metrics['weight_metrics']['available'], 2) }} kg</td>
                    </tr>
                    <tr>
                        <td><strong>% Vendido:</strong></td>
                        <td>{{ number_format($metrics['weight_metrics']['sold_percentage'], 1) }}%</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="section">
        <h3>Métricas Financieras</h3>
        <table>
            <tr>
                <td><strong>Precio Compra/kg:</strong></td>
                <td>${{ number_format($lot->purchase_price_per_kg, 2) }}</td>
                <td><strong>Costo Total Compra:</strong></td>
                <td>${{ number_format($metrics['financial_metrics']['purchase_cost'], 2) }}</td>
            </tr>
            <tr>
                <td><strong>Ingresos Ventas:</strong></td>
                <td>${{ number_format($metrics['financial_metrics']['revenue'], 2) }}</td>
                <td><strong>Ganancia:</strong></td>
                <td class="{{ $metrics['financial_metrics']['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                    <strong>${{ number_format($metrics['financial_metrics']['profit'], 2) }}</strong>
                </td>
            </tr>
            @php
                $metadata = is_array($lot->metadata) ? $lot->metadata : json_decode($lot->metadata ?? '{}', true);
            @endphp
            @if(isset($metadata['precio_venta_sugerido']) && $metadata['precio_venta_sugerido'])
            <tr>
                <td><strong>Precio Venta Sugerido:</strong></td>
                <td>${{ number_format($metadata['precio_venta_sugerido'], 2) }}</td>
                <td colspan="2"></td>
            </tr>
            @endif
        </table>
    </div>

    @php
        $notes = $metadata['notas'] ?? null;
    @endphp
    @if($notes)
    <div class="section">
        <h3>Notas</h3>
        <div class="notes-box">
            {{ $notes }}
        </div>
    </div>
    @endif

    @if($lot->saleItems->count() > 0)
    <div class="section">
        <h3>Historial de Ventas</h3>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Peso Vendido</th>
                    <th>Precio/kg</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lot->saleItems as $item)
                <tr>
                    <td>{{ $item->sale->sale_date->format('d/m/Y') }}</td>
                    <td>{{ $item->sale->customer->name }}</td>
                    <td>{{ number_format($item->weight, 2) }} kg</td>
                    <td>${{ number_format($item->price_per_kg, 2) }}</td>
                    <td>${{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="font-weight: bold; background-color: #f8f9fa;">
                    <td colspan="2"><strong>Totales:</strong></td>
                    <td><strong>{{ number_format($lot->saleItems->sum('weight'), 2) }} kg</strong></td>
                    <td>-</td>
                    <td><strong>${{ number_format($lot->saleItems->sum('subtotal'), 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>AvoControl Pro - Sistema de Gestión de Aguacates</p>
        <p>Reporte generado automáticamente el {{ now()->format('d/m/Y') }} a las {{ now()->format('H:i') }}</p>
    </div>
</body>
</html>