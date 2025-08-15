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
                                $qualityGrade = \App\Models\QualityGrade::where('name', $lot->quality_grade)->first();
                                $qualityColor = $qualityGrade ? $qualityGrade->color : '#6c757d';
                            @endphp
                            <span class="badge" style="background-color: {{ $qualityColor }}; color: white;">{{ $lot->quality_grade }}</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="column">
            <div class="section">
                <h3>Información de Inventario</h3>
                <table>
                    <tr>
                        <td><strong>Peso Total:</strong></td>
                        <td>{{ number_format($lot->total_weight, 2) }} kg</td>
                    </tr>
                    <tr>
                        <td><strong>Contribuye al Acopio:</strong></td>
                        <td>
                            @php
                                $qualityGrade = \App\Models\QualityGrade::where('name', $lot->quality_grade)->first();
                                $qualityColor = $qualityGrade ? $qualityGrade->color : '#6c757d';
                            @endphp
                            <span class="badge" style="background-color: {{ $qualityColor }}; color: white;">{{ $lot->quality_grade }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <small style="color: #6c757d;">
                                Este lote forma parte del inventario total para ventas por calidad
                            </small>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="section">
        <h3>Información de Compra</h3>
        <table>
            <tr>
                <td><strong>Precio Compra/kg:</strong></td>
                <td>${{ number_format($lot->purchase_price_per_kg, 2) }}</td>
                <td><strong>Costo Total Compra:</strong></td>
                <td><strong>${{ number_format($lot->total_purchase_cost, 2) }}</strong></td>
            </tr>
            <tr>
                <td colspan="4">
                    <small style="color: #6c757d;">
                        Las ganancias se calculan por acopio total, no por lote individual
                    </small>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Estado de Pagos al Proveedor</h3>
        <table>
            <tr>
                <td><strong>Total a Pagar:</strong></td>
                <td>${{ number_format($lot->total_purchase_cost, 2) }}</td>
                <td><strong>Monto Pagado:</strong></td>
                <td class="text-success"><strong>${{ number_format($lot->amount_paid ?? 0, 2) }}</strong></td>
            </tr>
            <tr>
                <td><strong>Saldo Pendiente:</strong></td>
                <td class="{{ ($lot->amount_owed ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                    <strong>${{ number_format($lot->amount_owed ?? 0, 2) }}</strong>
                </td>
                <td><strong>Estado del Pago:</strong></td>
                <td>
                    @php
                        $paymentStatusClass = match($lot->payment_status ?? 'pending') {
                            'paid' => 'success',
                            'partial' => 'warning',
                            'pending' => 'danger',
                            default => 'secondary'
                        };
                        $paymentStatusText = match($lot->payment_status ?? 'pending') {
                            'paid' => 'Pagado Completo',
                            'partial' => 'Pago Parcial',
                            'pending' => 'Pendiente',
                            default => 'Desconocido'
                        };
                    @endphp
                    <span class="badge badge-{{ $paymentStatusClass }}">{{ $paymentStatusText }}</span>
                </td>
            </tr>
        </table>
    </div>

    @if($lot->payments && $lot->payments->count() > 0)
    <div class="section">
        <h3>Historial de Pagos</h3>
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Fecha</th>
                    <th>Monto</th>
                    <th>Método</th>
                    <th>Referencia</th>
                    <th>Registrado por</th>
                    <th>Notas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lot->payments->sortBy(['payment_date', 'created_at']) as $payment)
                <tr>
                    <td><small>{{ $payment->payment_code }}</small></td>
                    <td>{{ $payment->payment_date->format('d/m/Y H:i') }}</td>
                    <td class="text-success"><strong>${{ number_format($payment->amount, 2) }}</strong></td>
                    <td>
                        @php
                            $methodLabels = [
                                'cash' => 'Efectivo',
                                'transfer' => 'Transferencia',
                                'check' => 'Cheque',
                                'card' => 'Tarjeta',
                                'credit' => 'Crédito'
                            ];
                            $methodClasses = [
                                'cash' => 'success',
                                'transfer' => 'info',
                                'check' => 'warning',
                                'card' => 'primary',
                                'credit' => 'secondary'
                            ];
                        @endphp
                        <span class="badge badge-{{ $methodClasses[$payment->payment_method] ?? 'secondary' }}">
                            {{ $methodLabels[$payment->payment_method] ?? ucfirst($payment->payment_method) }}
                        </span>
                    </td>
                    <td>{{ $payment->reference ?: '-' }}</td>
                    <td>{{ $payment->createdBy ? $payment->createdBy->name : 'Sistema' }}</td>
                    <td>{{ $payment->notes ?: '-' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="font-weight: bold; background-color: #f8f9fa;">
                    <td><strong>Total:</strong></td>
                    <td><strong>{{ $lot->payments->count() }} pagos</strong></td>
                    <td class="text-success"><strong>${{ number_format($lot->payments->sum('amount'), 2) }}</strong></td>
                    <td colspan="4">
                        <strong>Saldo Restante: 
                            <span class="{{ ($lot->amount_owed ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                ${{ number_format($lot->amount_owed ?? 0, 2) }}
                            </span>
                        </strong>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    @else
    <div class="section">
        <h3>Historial de Pagos</h3>
        <p style="color: #6c757d; font-style: italic; text-align: center; padding: 20px;">
            💳 No hay pagos registrados para este lote
        </p>
    </div>
    @endif

    @if($lot->notes)
    <div class="section">
        <h3>Notas</h3>
        <div class="notes-box">
            {{ $lot->notes }}
        </div>
    </div>
    @endif


    <div class="footer">
        <p>AvoControl Pro - Sistema de Gestión de Aguacates</p>
        <p>Reporte generado automáticamente el {{ now()->format('d/m/Y') }} a las {{ now()->format('H:i') }}</p>
    </div>
</body>
</html>