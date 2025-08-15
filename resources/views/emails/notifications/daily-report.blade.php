@extends('emails.layouts.app')

@section('content')
    <div class="alert alert-success">
        <strong>📊 Reporte Diario de Operaciones</strong><br>
        Resumen completo de las actividades del {{ $date ?? date('d/m/Y') }}
    </div>

    <h2>📈 Resumen del Día</h2>
    
    <p>Buenos días,</p>
    
    <p>Le presentamos el resumen de las operaciones realizadas el día de hoy en el centro de acopio:</p>

    <div class="stats-grid">
        <div class="stat-box" style="border-left-color: #28a745;">
            <span class="value">${{ number_format($summary['total_sales'] ?? 0, 2) }}</span>
            <span class="label">Ventas Totales</span>
        </div>
        <div class="stat-box" style="border-left-color: #17a2b8;">
            <span class="value">{{ $summary['sales_count'] ?? 0 }}</span>
            <span class="label">Transacciones</span>
        </div>
        <div class="stat-box" style="border-left-color: #fd7e14;">
            <span class="value">{{ number_format($summary['total_weight'] ?? 0, 0) }} kg</span>
            <span class="label">Aguacate Vendido</span>
        </div>
        <div class="stat-box" style="border-left-color: #6f42c1;">
            <span class="value">{{ $summary['new_customers'] ?? 0 }}</span>
            <span class="label">Clientes Nuevos</span>
        </div>
    </div>

    <!-- Ventas por Calidad -->
    @if(!empty($salesByQuality) && count($salesByQuality) > 0)
        <h3>🥑 Ventas por Calidad</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Calidad</th>
                    <th>Cantidad (kg)</th>
                    <th>Ventas ($)</th>
                    <th>Precio Promedio</th>
                    <th>% del Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($salesByQuality as $quality)
                    <tr>
                        <td>
                            <span style="display: inline-block; width: 12px; height: 12px; background-color: {{ $quality['color'] ?? '#6c757d' }}; border-radius: 2px; margin-right: 8px;"></span>
                            <strong>{{ $quality['name'] }}</strong>
                        </td>
                        <td>{{ number_format($quality['weight'], 0) }} kg</td>
                        <td>${{ number_format($quality['sales'], 2) }}</td>
                        <td>${{ number_format($quality['avg_price'], 2) }}/kg</td>
                        <td>{{ number_format($quality['percentage'], 1) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Top Clientes del Día -->
    @if(!empty($topCustomers) && count($topCustomers) > 0)
        <h3>🏆 Top 5 Clientes del Día</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Compras</th>
                    <th>Cantidad (kg)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topCustomers as $index => $customer)
                    <tr>
                        <td><strong>{{ $index + 1 }}</strong></td>
                        <td>{{ $customer['name'] }}</td>
                        <td>${{ number_format($customer['total_purchases'], 2) }}</td>
                        <td>{{ number_format($customer['total_weight'], 0) }} kg</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Estado del Inventario -->
    <h3>📦 Estado del Inventario</h3>
    <div class="stats-grid">
        <div class="stat-box" style="border-left-color: #28a745;">
            <span class="value">{{ number_format($inventory['available_weight'] ?? 0, 0) }} kg</span>
            <span class="label">Disponible</span>
        </div>
        <div class="stat-box" style="border-left-color: #ffc107;">
            <span class="value">{{ $inventory['active_lots'] ?? 0 }}</span>
            <span class="label">Lotes Activos</span>
        </div>
        <div class="stat-box" style="border-left-color: #dc3545;">
            <span class="value">{{ $inventory['low_stock_count'] ?? 0 }}</span>
            <span class="label">Stock Bajo</span>
        </div>
        <div class="stat-box" style="border-left-color: #17a2b8;">
            <span class="value">${{ number_format($inventory['total_value'] ?? 0, 2) }}</span>
            <span class="label">Valor Total</span>
        </div>
    </div>

    <!-- Actividad Financiera -->
    <h3>💰 Actividad Financiera</h3>
    <div class="stats-grid">
        <div class="stat-box" style="border-left-color: #28a745;">
            <span class="value">${{ number_format($finance['payments_received'] ?? 0, 2) }}</span>
            <span class="label">Cobros del Día</span>
        </div>
        <div class="stat-box" style="border-left-color: #dc3545;">
            <span class="value">${{ number_format($finance['payments_made'] ?? 0, 2) }}</span>
            <span class="label">Pagos Realizados</span>
        </div>
        <div class="stat-box" style="border-left-color: #17a2b8;">
            <span class="value">${{ number_format($finance['net_cash_flow'] ?? 0, 2) }}</span>
            <span class="label">Flujo Neto</span>
        </div>
        <div class="stat-box" style="border-left-color: #6f42c1;">
            <span class="value">${{ number_format($finance['pending_collections'] ?? 0, 2) }}</span>
            <span class="label">Por Cobrar</span>
        </div>
    </div>

    <!-- Alertas y Recordatorios -->
    @if(!empty($alerts) && count($alerts) > 0)
        <div class="alert alert-warning">
            <strong>⚠️ Alertas y Recordatorios:</strong>
            <ul style="margin-bottom: 0; margin-top: 10px;">
                @foreach($alerts as $alert)
                    <li>{{ $alert }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Comparación con Ayer -->
    @if(!empty($comparison))
        <h3>📊 Comparación con Ayer</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Métrica</th>
                    <th>Hoy</th>
                    <th>Ayer</th>
                    <th>Variación</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Ventas Totales</strong></td>
                    <td>${{ number_format($comparison['sales_today'] ?? 0, 2) }}</td>
                    <td>${{ number_format($comparison['sales_yesterday'] ?? 0, 2) }}</td>
                    <td>
                        @php
                            $variance = ($comparison['sales_variance'] ?? 0);
                        @endphp
                        <span class="{{ $variance >= 0 ? 'priority-normal' : 'priority-high' }}">
                            {{ $variance >= 0 ? '↗' : '↘' }} {{ number_format(abs($variance), 1) }}%
                        </span>
                    </td>
                </tr>
                <tr>
                    <td><strong>Cantidad Vendida</strong></td>
                    <td>{{ number_format($comparison['weight_today'] ?? 0, 0) }} kg</td>
                    <td>{{ number_format($comparison['weight_yesterday'] ?? 0, 0) }} kg</td>
                    <td>
                        @php
                            $variance = ($comparison['weight_variance'] ?? 0);
                        @endphp
                        <span class="{{ $variance >= 0 ? 'priority-normal' : 'priority-high' }}">
                            {{ $variance >= 0 ? '↗' : '↘' }} {{ number_format(abs($variance), 1) }}%
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    @endif

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ url('/dashboard') }}" class="btn btn-primary">
            📊 Ver Dashboard Completo
        </a>
        <a href="{{ url('/reportes/rentabilidad') }}" class="btn btn-info">
            📈 Reporte de Rentabilidad
        </a>
    </div>

    <div class="alert alert-info" style="margin-top: 30px;">
        <strong>📧 Información:</strong> Este reporte se genera automáticamente todos los días a las {{ $report_time ?? '8:00 AM' }}.
        <br>Para personalizar el contenido y horarios, visite la sección de Configuración del sistema.
    </div>
@endsection