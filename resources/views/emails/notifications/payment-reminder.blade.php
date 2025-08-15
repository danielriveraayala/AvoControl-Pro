@extends('emails.layouts.app')

@section('content')
    <div class="alert alert-warning">
        <strong>💰 Recordatorio de Pagos Pendientes</strong><br>
        Se han detectado pagos vencidos que requieren atención inmediata.
    </div>

    <h2>📋 Pagos Pendientes</h2>
    
    <p>Estimado administrador,</p>
    
    <p>El sistema ha identificado los siguientes pagos que están vencidos o próximos a vencer:</p>

    @if(!empty($overduePayments) && count($overduePayments) > 0)
        <h3 style="color: #dc3545;">🔴 Pagos Vencidos</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Referencia</th>
                    <th>Monto</th>
                    <th>Fecha Vencimiento</th>
                    <th>Días Vencido</th>
                </tr>
            </thead>
            <tbody>
                @foreach($overduePayments as $payment)
                    <tr>
                        <td>
                            @if($payment['type'] === 'customer')
                                <span style="color: #28a745;">📈 Cliente</span>
                            @else
                                <span style="color: #dc3545;">📉 Proveedor</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $payment['reference'] }}</strong><br>
                            <small style="color: #6c757d;">{{ $payment['entity_name'] }}</small>
                        </td>
                        <td>
                            <strong>${{ number_format($payment['amount'], 2) }}</strong>
                        </td>
                        <td>{{ $payment['due_date'] }}</td>
                        <td>
                            <span class="priority-critical">{{ $payment['days_overdue'] }} días</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(!empty($upcomingPayments) && count($upcomingPayments) > 0)
        <h3 style="color: #fd7e14;">🟡 Próximos a Vencer (5 días)</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Referencia</th>
                    <th>Monto</th>
                    <th>Fecha Vencimiento</th>
                    <th>Días Restantes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($upcomingPayments as $payment)
                    <tr>
                        <td>
                            @if($payment['type'] === 'customer')
                                <span style="color: #28a745;">📈 Cliente</span>
                            @else
                                <span style="color: #dc3545;">📉 Proveedor</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $payment['reference'] }}</strong><br>
                            <small style="color: #6c757d;">{{ $payment['entity_name'] }}</small>
                        </td>
                        <td>
                            <strong>${{ number_format($payment['amount'], 2) }}</strong>
                        </td>
                        <td>{{ $payment['due_date'] }}</td>
                        <td>
                            <span class="priority-high">{{ $payment['days_remaining'] }} días</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="stats-grid">
        <div class="stat-box">
            <span class="value">${{ number_format($summary['total_overdue'] ?? 0, 2) }}</span>
            <span class="label">Total Vencido</span>
        </div>
        <div class="stat-box">
            <span class="value">{{ $summary['overdue_count'] ?? 0 }}</span>
            <span class="label">Pagos Vencidos</span>
        </div>
        <div class="stat-box">
            <span class="value">${{ number_format($summary['upcoming_total'] ?? 0, 2) }}</span>
            <span class="label">Por Vencer</span>
        </div>
        <div class="stat-box">
            <span class="value">{{ $summary['upcoming_count'] ?? 0 }}</span>
            <span class="label">Próximos</span>
        </div>
    </div>

    <h3>🎯 Acciones Urgentes Requeridas</h3>
    
    @if(!empty($criticalActions))
        <div class="alert alert-danger">
            <strong>⚠️ Atención Inmediata:</strong>
            <ul style="margin-bottom: 0; padding-left: 20px;">
                @foreach($criticalActions as $action)
                    <li>{{ $action }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <ul>
        <li><strong>Revisar pagos vencidos</strong> y contactar a clientes/proveedores</li>
        <li><strong>Programar llamadas de cobranza</strong> para cuentas por cobrar vencidas</li>
        <li><strong>Autorizar pagos pendientes</strong> a proveedores según flujo de caja</li>
        <li><strong>Actualizar fechas de pago</strong> acordadas con contrapartes</li>
        <li><strong>Generar reportes de antigüedad</strong> de saldos para análisis</li>
    </ul>

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ url('/payments') }}" class="btn btn-primary">
            💰 Gestionar Pagos
        </a>
        <a href="{{ url('/payments/cash-flow') }}" class="btn btn-info">
            📊 Flujo de Caja
        </a>
    </div>

    <div class="alert alert-info" style="margin-top: 30px;">
        <strong>⏰ Recordatorio:</strong> Esta alerta se envía diariamente a las {{ $notification_time ?? '9:00 AM' }}.
        <br>Configure los límites y frecuencias desde la sección de Configuración del sistema.
    </div>
@endsection