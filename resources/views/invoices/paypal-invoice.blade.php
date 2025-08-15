<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Factura PayPal - {{ $invoiceData['invoice_id'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            border-bottom: 2px solid #0070ba;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #0070ba;
            margin-bottom: 10px;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }
        .invoice-info {
            margin-bottom: 30px;
        }
        .invoice-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-info td {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .invoice-info td:first-child {
            font-weight: bold;
            width: 200px;
        }
        .amount-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
        .amount {
            font-size: 36px;
            font-weight: bold;
            color: #0070ba;
            margin: 10px 0;
        }
        .status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status.paid {
            background-color: #28a745;
            color: white;
        }
        .status.pending {
            background-color: #ffc107;
            color: #333;
        }
        .footer {
            border-top: 2px solid #0070ba;
            padding-top: 20px;
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .environment-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .environment-badge.live {
            background-color: #28a745;
            color: white;
        }
        .environment-badge.sandbox {
            background-color: #ff6b35;
            color: white;
        }
    </style>
</head>
<body>
    @if(isset($invoiceData['is_mock_data']) && $invoiceData['is_mock_data'])
    <div class="environment-badge {{ $invoiceData['environment'] }}">
        MOCK DATA
    </div>
    @endif

    <div class="header">
        <div class="logo">{{ $invoiceData['company_name'] }}</div>
        <div style="font-size: 14px; color: #666;">
            Sistema de Gestión de Centros de Acopio de Aguacate
        </div>
    </div>

    <div class="invoice-title">FACTURA</div>

    <div class="invoice-info">
        <table>
            <tr>
                <td>Número de Factura:</td>
                <td>{{ $invoiceData['invoice_id'] }}</td>
            </tr>
            <tr>
                <td>Fecha de Emisión:</td>
                <td>{{ $invoiceData['date'] }}</td>
            </tr>
            <tr>
                <td>Suscripción ID:</td>
                <td>{{ $invoiceData['subscription_id'] }}</td>
            </tr>
            <tr>
                <td>Plan Contratado:</td>
                <td>{{ $invoiceData['plan'] }}</td>
            </tr>
            <tr>
                <td>Cliente:</td>
                <td>{{ $invoiceData['customer_name'] }}</td>
            </tr>
            <tr>
                <td>Email:</td>
                <td>{{ $invoiceData['customer_email'] }}</td>
            </tr>
            <tr>
                <td>Método de Pago:</td>
                <td>{{ $invoiceData['payment_method'] }}</td>
            </tr>
            <tr>
                <td>Estado:</td>
                <td>
                    <span class="status {{ strtolower($invoiceData['status']) }}">
                        {{ $invoiceData['status'] === 'PAID' ? 'PAGADO' : $invoiceData['status'] }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <div class="amount-section">
        <div style="font-size: 18px; margin-bottom: 10px;">MONTO TOTAL</div>
        <div class="amount">
            ${{ number_format($invoiceData['amount'], 2) }} {{ $invoiceData['currency'] }}
        </div>
        <div style="font-size: 14px; color: #666; margin-top: 10px;">
            Suscripción mensual a {{ $invoiceData['company_name'] }}
        </div>
    </div>

    <div style="margin: 30px 0; padding: 15px; background-color: #e3f2fd; border-radius: 8px;">
        <h4 style="margin: 0 0 10px 0; color: #1976d2;">Detalles del Servicio</h4>
        <p style="margin: 5px 0; font-size: 14px;">
            <strong>Plan {{ $invoiceData['plan'] }}</strong> - Acceso completo al sistema AvoControl Pro
        </p>
        <p style="margin: 5px 0; font-size: 14px;">
            • Gestión de lotes de aguacate<br>
            • Control de ventas y pagos<br>
            • Reportes de rentabilidad<br>
            • Soporte técnico incluido
        </p>
    </div>

    <div class="footer">
        <p>
            <strong>{{ $invoiceData['company_name'] }}</strong><br>
            Sistema de Gestión para Centros de Acopio de Aguacate<br>
            Email: avocontrol@kreativos.pro | Web: https://dev.avocontrol.pro
        </p>
        <p style="margin-top: 15px; font-size: 11px;">
            Esta factura fue generada automáticamente por el sistema el {{ now()->format('d/m/Y H:i:s') }}.
        </p>
    </div>
</body>
</html>