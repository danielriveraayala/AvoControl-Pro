<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Factura - {{ $sale->sale_code }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            border-bottom: 2px solid #28a745;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-info {
            float: left;
            width: 50%;
        }
        .invoice-info {
            float: right;
            width: 45%;
            text-align: right;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 5px;
        }
        .invoice-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .clearfix {
            clear: both;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #28a745;
            border-bottom: 1px solid #28a745;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .customer-info, .sale-info {
            float: left;
            width: 48%;
        }
        .sale-info {
            float: right;
        }
        .info-table {
            width: 100%;
        }
        .info-table td {
            padding: 3px 5px;
            vertical-align: top;
        }
        .info-table .label {
            font-weight: bold;
            width: 30%;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .items-table th {
            background-color: #28a745;
            color: white;
            font-weight: bold;
        }
        .items-table .number {
            text-align: right;
        }
        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .summary {
            float: right;
            width: 300px;
            margin-top: 20px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .summary-table .label {
            text-align: left;
            font-weight: bold;
        }
        .summary-table .value {
            text-align: right;
        }
        .total-final {
            background-color: #28a745;
            color: white;
            font-size: 16px;
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
            font-size: 10px;
            color: #666;
            text-align: center;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-confirmed {
            background-color: #007bff;
            color: white;
        }
        .status-delivered {
            background-color: #28a745;
            color: white;
        }
        .status-draft {
            background-color: #6c757d;
            color: white;
        }
        .status-cancelled {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-info">
            <div class="company-name">AvoControl Pro</div>
            <div>Sistema de Gestión de Aguacate</div>
            <div>Uruapan, Michoacán</div>
            <div>RFC: ACO240809XX0</div>
        </div>
        <div class="invoice-info">
            <div class="invoice-title">FACTURA</div>
            <div><strong>No. {{ $sale->invoice_number ?: $sale->sale_code }}</strong></div>
            <div>Fecha: {{ $sale->sale_date->format('d/m/Y') }}</div>
            <div>
                Estado: 
                <span class="status-badge status-{{ $sale->status }}">
                    {{ ucfirst($sale->status) }}
                </span>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>

    <!-- Customer and Sale Information -->
    <div class="section">
        <div class="customer-info">
            <div class="section-title">DATOS DEL CLIENTE</div>
            <table class="info-table">
                <tr>
                    <td class="label">Nombre:</td>
                    <td>{{ $sale->customer->name }}</td>
                </tr>
                <tr>
                    <td class="label">Contacto:</td>
                    <td>{{ $sale->customer->contact_person }}</td>
                </tr>
                @if($sale->customer->phone)
                <tr>
                    <td class="label">Teléfono:</td>
                    <td>{{ $sale->customer->phone }}</td>
                </tr>
                @endif
                @if($sale->customer->email)
                <tr>
                    <td class="label">Email:</td>
                    <td>{{ $sale->customer->email }}</td>
                </tr>
                @endif
                @if($sale->customer->address)
                <tr>
                    <td class="label">Dirección:</td>
                    <td>{{ $sale->customer->address }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label">Tipo:</td>
                    <td>{{ ucfirst($sale->customer->customer_type ?: 'N/A') }}</td>
                </tr>
            </table>
        </div>
        
        <div class="sale-info">
            <div class="section-title">DATOS DE LA VENTA</div>
            <table class="info-table">
                <tr>
                    <td class="label">Código:</td>
                    <td>{{ $sale->sale_code }}</td>
                </tr>
                <tr>
                    <td class="label">Fecha Venta:</td>
                    <td>{{ $sale->sale_date->format('d/m/Y') }}</td>
                </tr>
                @if($sale->delivery_date)
                <tr>
                    <td class="label">Fecha Entrega:</td>
                    <td>{{ $sale->delivery_date->format('d/m/Y') }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label">Estado Pago:</td>
                    <td>
                        @switch($sale->payment_status)
                            @case('pending')
                                Pendiente
                                @break
                            @case('partial')
                                Parcial
                                @break
                            @case('paid')
                                Pagado
                                @break
                            @default
                                {{ ucfirst($sale->payment_status) }}
                        @endswitch
                    </td>
                </tr>
                <tr>
                    <td class="label">Creado por:</td>
                    <td>{{ $sale->createdBy->name ?? 'Sistema' }}</td>
                </tr>
            </table>
        </div>
        <div class="clearfix"></div>
    </div>

    <!-- Items -->
    <div class="section">
        <div class="section-title">DETALLE DE PRODUCTOS</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Calidad</th>
                    <th class="number">Peso (kg)</th>
                    <th class="number">Precio/kg</th>
                    <th class="number">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->saleItems as $item)
                <tr>
                    <td>{{ $item->quality_grade }}</td>
                    <td class="number">{{ number_format($item->weight, 2) }}</td>
                    <td class="number">${{ number_format($item->price_per_kg, 2) }}</td>
                    <td class="number">${{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td><strong>TOTALES</strong></td>
                    <td class="number"><strong>{{ number_format($sale->total_weight, 2) }} kg</strong></td>
                    <td class="number"><strong>${{ number_format($sale->total_amount / $sale->total_weight, 2) }}/kg</strong></td>
                    <td class="number"><strong>${{ number_format($sale->total_amount, 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Summary -->
    <div class="summary">
        <table class="summary-table">
            <tr>
                <td class="label">Subtotal:</td>
                <td class="value">${{ number_format($sale->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td class="label">IVA (0%):</td>
                <td class="value">$0.00</td>
            </tr>
            <tr class="total-final">
                <td class="label">TOTAL:</td>
                <td class="value">${{ number_format($sale->total_amount, 2) }}</td>
            </tr>
        </table>
    </div>
    <div class="clearfix"></div>

    <!-- Payment History -->
    @if($sale->payments->count() > 0)
    <div class="section">
        <div class="section-title">HISTORIAL DE PAGOS</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Método</th>
                    <th>Referencia</th>
                    <th class="number">Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->payments as $payment)
                <tr>
                    <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                    <td>{{ ucfirst($payment->payment_method) }}</td>
                    <td>{{ $payment->reference_number ?: '-' }}</td>
                    <td class="number">${{ number_format($payment->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #d4edda;">
                    <td colspan="3"><strong>Total Pagado:</strong></td>
                    <td class="number"><strong>${{ number_format($sale->payments->sum('amount'), 2) }}</strong></td>
                </tr>
                <tr style="background-color: #f8d7da;">
                    <td colspan="3"><strong>Saldo Pendiente:</strong></td>
                    <td class="number"><strong>${{ number_format($sale->total_amount - $sale->payments->sum('amount'), 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    <!-- Lot Allocations -->
    @if($sale->saleItems->flatMap->allocations->count() > 0)
    <div class="section">
        <div class="section-title">TRAZABILIDAD - LOTES UTILIZADOS</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Código Lote</th>
                    <th>Proveedor</th>
                    <th>Calidad</th>
                    <th class="number">Peso Utilizado (kg)</th>
                    <th>Fecha Asignación</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->saleItems as $item)
                    @foreach($item->allocations as $allocation)
                    <tr>
                        <td>{{ $allocation->lot->lot_code }}</td>
                        <td>{{ $allocation->lot->supplier->name ?? 'N/A' }}</td>
                        <td>{{ $allocation->lot->qualityGrade->name ?? 'N/A' }}</td>
                        <td class="number">{{ number_format($allocation->weight_allocated, 2) }}</td>
                        <td>{{ $allocation->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Notes -->
    @if($sale->notes)
    <div class="section">
        <div class="section-title">OBSERVACIONES</div>
        <p>{{ $sale->notes }}</p>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p><strong>AvoControl Pro</strong> - Sistema de Gestión de Aguacate</p>
        <p>Documento generado el {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Esta es una representación impresa de un documento electrónico</p>
    </div>
</body>
</html>