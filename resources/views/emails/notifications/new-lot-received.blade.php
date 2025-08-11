@extends('emails.layouts.app')

@section('content')
    <div class="alert alert-success">
        <strong>🚚 Nuevo Lote Recibido</strong><br>
        Se ha registrado la recepción de un nuevo lote de aguacate de alto valor.
    </div>

    <h2>📦 Información del Lote</h2>
    
    <p>Se informa que se ha recibido un nuevo lote de aguacate que supera el umbral de valor establecido:</p>

    <div class="stats-grid">
        <div class="stat-box" style="border-left-color: #28a745;">
            <span class="value">{{ $lot['code'] ?? 'N/A' }}</span>
            <span class="label">Código del Lote</span>
        </div>
        <div class="stat-box" style="border-left-color: #17a2b8;">
            <span class="value">{{ number_format($lot['total_weight'] ?? 0, 0) }} kg</span>
            <span class="label">Peso Total</span>
        </div>
        <div class="stat-box" style="border-left-color: #ffc107;">
            <span class="value">${{ number_format($lot['total_value'] ?? 0, 2) }}</span>
            <span class="label">Valor Total</span>
        </div>
        <div class="stat-box" style="border-left-color: #fd7e14;">
            <span class="value">${{ number_format($lot['price_per_kg'] ?? 0, 2) }}</span>
            <span class="label">Precio por Kg</span>
        </div>
    </div>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Campo</th>
                <th>Información</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Proveedor</strong></td>
                <td>{{ $lot['supplier_name'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Calidad</strong></td>
                <td>
                    @if(isset($lot['quality_color']) && isset($lot['quality_name']))
                        <span style="display: inline-block; width: 12px; height: 12px; background-color: {{ $lot['quality_color'] }}; border-radius: 2px; margin-right: 8px;"></span>
                        {{ $lot['quality_name'] }}
                    @else
                        N/A
                    @endif
                </td>
            </tr>
            <tr>
                <td><strong>Fecha de Recepción</strong></td>
                <td>{{ $lot['received_date'] ?? date('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td><strong>Estado</strong></td>
                <td>
                    <span class="badge badge-success">{{ $lot['status_label'] ?? 'Disponible' }}</span>
                </td>
            </tr>
            <tr>
                <td><strong>Notas</strong></td>
                <td>{{ $lot['notes'] ?? 'Sin observaciones especiales' }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Detalles de Calidad (si aplica) -->
    @if(isset($lot['quality_details']) && !empty($lot['quality_details']))
        <h3>🥑 Especificaciones de Calidad</h3>
        <div class="stats-grid">
            @if(isset($lot['quality_details']['caliber_range']))
                <div class="stat-box">
                    <span class="value">{{ $lot['quality_details']['caliber_range'] }}</span>
                    <span class="label">Rango de Calibre</span>
                </div>
            @endif
            @if(isset($lot['quality_details']['weight_range']))
                <div class="stat-box">
                    <span class="value">{{ $lot['quality_details']['weight_range'] }}g</span>
                    <span class="label">Rango de Peso</span>
                </div>
            @endif
            @if(isset($lot['quality_details']['expected_yield']))
                <div class="stat-box">
                    <span class="value">{{ $lot['quality_details']['expected_yield'] }}%</span>
                    <span class="label">Rendimiento Esperado</span>
                </div>
            @endif
        </div>
    @endif

    <!-- Impacto en Inventario -->
    <h3>📊 Impacto en el Inventario</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Métrica</th>
                <th>Anterior</th>
                <th>Nuevo</th>
                <th>Incremento</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Inventario Total</strong></td>
                <td>{{ number_format($inventory['previous_weight'] ?? 0, 0) }} kg</td>
                <td>{{ number_format($inventory['new_weight'] ?? 0, 0) }} kg</td>
                <td>
                    <span class="priority-normal">
                        +{{ number_format(($inventory['new_weight'] ?? 0) - ($inventory['previous_weight'] ?? 0), 0) }} kg
                    </span>
                </td>
            </tr>
            <tr>
                <td><strong>Valor del Inventario</strong></td>
                <td>${{ number_format($inventory['previous_value'] ?? 0, 2) }}</td>
                <td>${{ number_format($inventory['new_value'] ?? 0, 2) }}</td>
                <td>
                    <span class="priority-normal">
                        +${{ number_format(($inventory['new_value'] ?? 0) - ($inventory['previous_value'] ?? 0), 2) }}
                    </span>
                </td>
            </tr>
            <tr>
                <td><strong>Lotes Activos</strong></td>
                <td>{{ $inventory['previous_lots'] ?? 0 }}</td>
                <td>{{ $inventory['new_lots'] ?? 0 }}</td>
                <td>
                    <span class="priority-normal">+1 lote</span>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Comparación de Precios -->
    @if(isset($price_analysis) && !empty($price_analysis))
        <h3>💰 Análisis de Precios</h3>
        <div class="alert alert-info">
            <strong>📈 Análisis del Mercado:</strong>
            <ul style="margin-bottom: 0; margin-top: 10px;">
                @if(isset($price_analysis['comparison_with_average']))
                    <li>El precio está <strong>{{ $price_analysis['comparison_with_average']['status'] }}</strong> {{ number_format(abs($price_analysis['comparison_with_average']['percentage']), 1) }}% respecto al promedio de la calidad</li>
                @endif
                @if(isset($price_analysis['market_trend']))
                    <li>Tendencia del mercado: <strong>{{ $price_analysis['market_trend'] }}</strong></li>
                @endif
                @if(isset($price_analysis['recommendation']))
                    <li>Recomendación: {{ $price_analysis['recommendation'] }}</li>
                @endif
            </ul>
        </div>
    @endif

    <!-- Próximas Acciones -->
    <h3>🎯 Próximas Acciones Sugeridas</h3>
    <ul>
        <li><strong>Verificar calidad física</strong> del lote recibido contra especificaciones</li>
        <li><strong>Actualizar precios de venta</strong> basados en el costo de adquisición</li>
        <li><strong>Notificar al equipo de ventas</strong> sobre la nueva disponibilidad</li>
        <li><strong>Programar pagos al proveedor</strong> según términos acordados</li>
        @if(isset($lot['requires_special_handling']) && $lot['requires_special_handling'])
            <li><strong>Aplicar manejo especial</strong> según las características del lote</li>
        @endif
    </ul>

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ url('/lots/' . ($lot['id'] ?? '')) }}" class="btn btn-primary">
            👁️ Ver Detalles del Lote
        </a>
        <a href="{{ url('/acopio') }}" class="btn btn-info">
            📦 Gestionar Inventario
        </a>
    </div>

    <div class="alert alert-warning" style="margin-top: 30px;">
        <strong>⚠️ Importante:</strong> Esta notificación se genera automáticamente para lotes con valor superior a ${{ number_format($threshold_value ?? 50000, 0) }}.
        <br>Configure el umbral de notificación desde la sección de Configuración del sistema.
    </div>
@endsection