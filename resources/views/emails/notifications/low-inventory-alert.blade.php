@extends('emails.layouts.app')

@section('content')
    <div class="alert alert-warning">
        <strong>⚠️ Alerta de Inventario Bajo</strong><br>
        Se han detectado calidades con inventario por debajo del límite mínimo establecido.
    </div>

    <h2>📦 Estado del Inventario</h2>
    
    <p>Estimado usuario del sistema,</p>
    
    <p>El sistema ha detectado que las siguientes calidades de aguacate tienen inventario por debajo del límite mínimo:</p>
    
    @if(!empty($lowStockQualities))
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Calidad</th>
                    <th>Inventario Actual</th>
                    <th>Límite Mínimo</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lowStockQualities as $quality)
                    <tr>
                        <td>
                            <span style="display: inline-block; width: 12px; height: 12px; background-color: {{ $quality['color'] ?? '#6c757d' }}; border-radius: 2px; margin-right: 8px;"></span>
                            <strong>{{ $quality['name'] }}</strong>
                        </td>
                        <td>{{ number_format($quality['current_stock'], 0) }} kg</td>
                        <td>{{ number_format($quality['min_limit'], 0) }} kg</td>
                        <td>
                            @if($quality['current_stock'] <= $quality['critical_limit'])
                                <span class="priority-critical">🔴 Crítico</span>
                            @elseif($quality['current_stock'] <= $quality['min_limit'])
                                <span class="priority-high">🟡 Bajo</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="stats-grid">
        <div class="stat-box">
            <span class="value">{{ $summary['total_affected'] ?? 0 }}</span>
            <span class="label">Calidades Afectadas</span>
        </div>
        <div class="stat-box">
            <span class="value">{{ number_format($summary['total_shortage'] ?? 0) }} kg</span>
            <span class="label">Déficit Total</span>
        </div>
        <div class="stat-box">
            <span class="value">{{ $summary['critical_count'] ?? 0 }}</span>
            <span class="label">En Estado Crítico</span>
        </div>
    </div>

    <h3>🎯 Acciones Recomendadas</h3>
    <ul>
        <li><strong>Revisar próximas compras programadas</strong> para estas calidades</li>
        <li><strong>Contactar proveedores</strong> para acelerar entregas pendientes</li>
        <li><strong>Evaluar ajustar precios</strong> para reducir la demanda temporalmente</li>
        <li><strong>Notificar al equipo de ventas</strong> sobre las limitaciones de inventario</li>
    </ul>

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ url('/dashboard') }}" class="btn btn-primary">
            📊 Ver Dashboard Completo
        </a>
        <a href="{{ url('/acopio') }}" class="btn btn-info">
            📦 Gestionar Inventario
        </a>
    </div>

    <div class="alert alert-info" style="margin-top: 30px;">
        <strong>💡 Nota:</strong> Esta alerta se genera automáticamente cada {{ $frequency ?? '4 horas' }} durante el horario laboral (8:00 AM - 6:00 PM).
        <br>Puede configurar los límites de inventario desde la sección de Configuración del sistema.
    </div>
@endsection