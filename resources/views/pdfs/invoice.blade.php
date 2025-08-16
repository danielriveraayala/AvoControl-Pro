<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura {{ $invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header */
        .invoice-header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 20px;
        }
        
        .company-info {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }
        
        .invoice-meta {
            display: table-cell;
            width: 40%;
            vertical-align: top;
            text-align: right;
        }
        
        .company-logo {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .company-details {
            color: #666;
            line-height: 1.5;
        }
        
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .invoice-number {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .invoice-date {
            color: #666;
        }
        
        /* Customer Information */
        .customer-section {
            margin: 30px 0;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
        }
        
        .customer-title {
            font-size: 14px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        
        .customer-info {
            display: table;
            width: 100%;
        }
        
        .billing-info, .shipping-info {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .info-block {
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: bold;
            color: #333;
        }
        
        .info-value {
            color: #666;
        }
        
        /* Items Table */
        .items-section {
            margin: 30px 0;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .items-table th {
            background: #667eea;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #667eea;
        }
        
        .items-table td {
            padding: 10px 8px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        
        .items-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        /* Totals Section */
        .totals-section {
            display: table;
            width: 100%;
            margin-top: 20px;
        }
        
        .totals-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }
        
        .totals-right {
            display: table-cell;
            width: 40%;
            vertical-align: top;
        }
        
        .totals-table {
            width: 100%;
            margin-left: auto;
        }
        
        .totals-table td {
            padding: 8px 15px;
            border-bottom: 1px solid #eee;
        }
        
        .totals-table .total-label {
            font-weight: bold;
            text-align: right;
        }
        
        .totals-table .total-amount {
            text-align: right;
            font-weight: bold;
        }
        
        .grand-total {
            background: #667eea;
            color: white;
            font-size: 16px;
        }
        
        /* Payment Information */
        .payment-section {
            margin: 30px 0;
            background: #f0f9ff;
            padding: 20px;
            border-radius: 5px;
            border-left: 4px solid #0ea5e9;
        }
        
        .payment-title {
            font-size: 14px;
            font-weight: bold;
            color: #0ea5e9;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        
        .payment-details {
            display: table;
            width: 100%;
        }
        
        .payment-method, .payment-status {
            display: table-cell;
            width: 50%;
        }
        
        /* Notes Section */
        .notes-section {
            margin: 30px 0;
        }
        
        .notes-title {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .notes-list {
            list-style: none;
            padding: 0;
        }
        
        .notes-list li {
            margin-bottom: 5px;
            padding-left: 15px;
            position: relative;
        }
        
        .notes-list li:before {
            content: "•";
            color: #667eea;
            font-weight: bold;
            position: absolute;
            left: 0;
        }
        
        /* Terms Section */
        .terms-section {
            margin: 30px 0;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        
        .terms-title {
            font-size: 12px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        /* Footer */
        .invoice-footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-size: 10px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        
        /* Utilities */
        .mb-10 { margin-bottom: 10px; }
        .mb-20 { margin-bottom: 20px; }
        .mt-20 { margin-top: 20px; }
        
        .font-bold { font-weight: bold; }
        .text-primary { color: #667eea; }
        .text-success { color: #10b981; }
        .text-danger { color: #ef4444; }
        .text-warning { color: #f59e0b; }
        
        /* Print Styles */
        @media print {
            body {
                font-size: 11px;
            }
            
            .invoice-container {
                padding: 0;
            }
            
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="company-info">
                <div class="company-logo">🥑 AvoControl Pro</div>
                <div class="company-name">{{ $company['name'] }}</div>
                <div class="company-details">
                    {{ $company['legal_name'] }}<br>
                    {{ $company['address_line_1'] }}<br>
                    {{ $company['address_line_2'] }}<br>
                    {{ $company['tax_id'] }}<br>
                    📧 {{ $company['email'] }}<br>
                    📞 {{ $company['phone'] }}<br>
                    🌐 {{ $company['website'] }}
                </div>
            </div>
            <div class="invoice-meta">
                <div class="invoice-title">FACTURA</div>
                <div class="invoice-number">No. {{ $invoice_number }}</div>
                <div class="invoice-date">
                    <strong>Fecha:</strong> {{ $invoice_date->format('d/m/Y') }}<br>
                    <strong>Vencimiento:</strong> {{ $due_date->format('d/m/Y') }}
                </div>
            </div>
        </div>
        
        <!-- Customer Information -->
        <div class="customer-section">
            <div class="customer-title">Información del Cliente</div>
            <div class="customer-info">
                <div class="billing-info">
                    <div class="info-block">
                        <div class="info-label">Cliente:</div>
                        <div class="info-value">{{ $customer['name'] }}</div>
                    </div>
                    @if($customer['company'])
                    <div class="info-block">
                        <div class="info-label">Empresa:</div>
                        <div class="info-value">{{ $customer['company'] }}</div>
                    </div>
                    @endif
                    <div class="info-block">
                        <div class="info-label">Email:</div>
                        <div class="info-value">{{ $customer['email'] }}</div>
                    </div>
                </div>
                <div class="shipping-info">
                    <div class="info-block">
                        <div class="info-label">Dirección:</div>
                        <div class="info-value">{{ $customer['address'] }}</div>
                    </div>
                    <div class="info-block">
                        <div class="info-label">RFC/Tax ID:</div>
                        <div class="info-value">{{ $customer['tax_id'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Items -->
        <div class="items-section">
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">Descripción</th>
                        <th style="width: 10%;" class="text-center">Cant.</th>
                        <th style="width: 20%;" class="text-right">Precio Unit.</th>
                        <th style="width: 20%;" class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td>{{ $item['description'] }}</td>
                        <td class="text-center">{{ $item['quantity'] }}</td>
                        <td class="text-right">${{ number_format($item['unit_price'], 2) }} {{ $payment['currency'] }}</td>
                        <td class="text-right">${{ number_format($item['total'], 2) }} {{ $payment['currency'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Totals -->
        <div class="totals-section">
            <div class="totals-left">
                <!-- Payment Information -->
                <div class="payment-section">
                    <div class="payment-title">Información de Pago</div>
                    <div class="payment-details">
                        <div class="payment-method">
                            <div class="info-block">
                                <div class="info-label">Método de Pago:</div>
                                <div class="info-value">{{ $payment['method'] }}</div>
                            </div>
                            <div class="info-block">
                                <div class="info-label">ID Transacción:</div>
                                <div class="info-value">{{ $payment['transaction_id'] }}</div>
                            </div>
                        </div>
                        <div class="payment-status">
                            <div class="info-block">
                                <div class="info-label">Fecha de Pago:</div>
                                <div class="info-value">{{ $payment['payment_date']->format('d/m/Y H:i') }}</div>
                            </div>
                            <div class="info-block">
                                <div class="info-label">Estado:</div>
                                <div class="info-value text-success">{{ ucfirst($payment['status']) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="totals-right">
                <table class="totals-table">
                    <tr>
                        <td class="total-label">Subtotal:</td>
                        <td class="total-amount">${{ number_format($subtotal, 2) }} {{ $payment['currency'] }}</td>
                    </tr>
                    <tr>
                        <td class="total-label">IVA ({{ number_format($tax_rate * 100, 1) }}%):</td>
                        <td class="total-amount">${{ number_format($tax_amount, 2) }} {{ $payment['currency'] }}</td>
                    </tr>
                    <tr class="grand-total">
                        <td class="total-label">TOTAL:</td>
                        <td class="total-amount">${{ number_format($total, 2) }} {{ $payment['currency'] }}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Notes -->
        @if(!empty($notes))
        <div class="notes-section">
            <div class="notes-title">Notas Importantes</div>
            <ul class="notes-list">
                @foreach($notes as $note)
                <li>{{ $note }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        
        <!-- Terms and Conditions -->
        @if(!empty($terms))
        <div class="terms-section">
            <div class="terms-title">Términos y Condiciones</div>
            <ul class="notes-list">
                @foreach($terms as $term)
                <li>{{ $term }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        
        <!-- Footer -->
        <div class="invoice-footer">
            <p><strong>¡Gracias por tu preferencia!</strong></p>
            <p>Esta factura fue generada automáticamente por el sistema AvoControl Pro</p>
            <p>Para cualquier consulta, contacta a nuestro equipo de soporte: {{ $company['email'] }}</p>
            <p style="margin-top: 10px;">
                Documento generado el {{ now()->format('d/m/Y H:i:s') }}
            </p>
        </div>
    </div>
</body>
</html>