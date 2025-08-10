@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Panel de Control')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    <!-- Alertas de Déficit de Inventario -->
    @if(isset($alertasDeficit) && count($alertasDeficit) > 0)
    <div class="row">
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-ban"></i> ¡ALERTA CRÍTICA DE INVENTARIO!</h5>
                <p class="mb-2">Se detectaron ventas comprometidas sin inventario suficiente:</p>
                <ul>
                    @foreach($alertasDeficit as $alerta)
                    <li>
                        <strong>{{ $alerta['calidad'] }}:</strong> 
                        Déficit de <strong>{{ number_format($alerta['deficit'], 2) }} kg</strong>
                        (Disponible: {{ number_format($alerta['disponible'], 2) }} kg, 
                        Vendido: {{ number_format($alerta['comprometido'], 2) }} kg)
                    </li>
                    @endforeach
                </ul>
                <div class="mt-3">
                    <a href="{{ route('lots.create') }}" class="btn btn-sm btn-success">
                        <i class="fas fa-plus"></i> Registrar Lotes
                    </a>
                    <a href="{{ route('sales.index') }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-eye"></i> Revisar Ventas
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Alertas de Poco Inventario -->
    @if(isset($alertasPocaExistencia) && count($alertasPocaExistencia) > 0)
    <div class="row">
        <div class="col-12">
            <div class="alert alert-warning alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-exclamation-triangle"></i> ⚠️ INVENTARIO BAJO</h5>
                <p class="mb-2">Las siguientes calidades tienen inventario bajo (≤20% del total):</p>
                <ul>
                    @foreach($alertasPocaExistencia as $alerta)
                    <li>
                        <strong>{{ $alerta['calidad'] }}:</strong> 
                        Solo queda el <strong>{{ $alerta['porcentaje_disponible'] }}%</strong> del inventario total
                        ({{ number_format($alerta['disponible'], 2) }} kg de {{ number_format($alerta['total'], 2) }} kg)
                    </li>
                    @endforeach
                </ul>
                <div class="mt-3">
                    <a href="{{ route('lots.create') }}" class="btn btn-sm btn-success">
                        <i class="fas fa-plus"></i> Registrar Lotes
                    </a>
                    <a href="{{ route('acopio.index') }}" class="btn btn-sm btn-info">
                        <i class="fas fa-boxes"></i> Ver Inventario
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Métricas Principales -->
    <div class="row">
        <!-- Inventario Total -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($metrics['inventory']['total_weight'] ?? 0, 0) }} kg</h3>
                    <p>Inventario Disponible</p>
                </div>
                <div class="icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <a href="{{ route('lots.index') }}" class="small-box-footer">
                    Ver lotes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Valor del Inventario -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>${{ number_format($metrics['inventory']['total_value'] ?? 0, 0) }}</h3>
                    <p>Valor del Inventario</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <a href="{{ route('lots.index') }}" class="small-box-footer">
                    Más info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Ventas del Mes -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>${{ number_format($metrics['financial']['total_sales_value'] ?? 0, 0) }}</h3>
                    <p>Ventas del Mes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <a href="{{ route('sales.index') }}" class="small-box-footer">
                    Ver ventas <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Cuentas por Cobrar -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>${{ number_format($metrics['financial']['accounts_receivable'] ?? 0, 0) }}</h3>
                    <p>Por Cobrar</p>
                </div>
                <div class="icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <a href="{{ route('customers.index') }}" class="small-box-footer">
                    Ver clientes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Resumen de Acopio por Calidad -->
    @if(isset($acopioSummary) && $acopioSummary->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-layer-group"></i>
                        Resumen de Acopio por Calidad
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('acopio.index') }}" class="btn btn-tool">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="row">
                        @foreach($acopioSummary as $acopio)
                            <div class="col-lg-3 col-6">
                                <div class="text-center p-3">
                                    @php
                                        $qualityName = $acopio->qualityGrade ? $acopio->qualityGrade->name : 'Sin calidad';
                                        $qualityColor = $acopio->qualityGrade ? $acopio->qualityGrade->color : '#6c757d';
                                    @endphp
                                    
                                    <h5 class="badge badge-lg mb-2" style="background-color: {{ $qualityColor }}; color: white;">{{ $qualityName }}</h5>
                                    
                                    <div class="text-sm">
                                        <strong class="d-block">{{ number_format($acopio->peso_disponible, 2) }} kg</strong>
                                        <small class="text-muted">disponible</small>
                                    </div>
                                    
                                    <div class="text-sm mt-2">
                                        <span class="text-muted">{{ $acopio->total_lotes }} lotes</span>
                                        <br><small class="text-success">${{ number_format($acopio->inversion_total, 0) }}</small>
                                    </div>
                                    
                                    <div class="progress mt-2" style="height: 6px;">
                                        @php
                                            $percentage = $acopio->peso_total > 0 ? ($acopio->peso_vendido / $acopio->peso_total) * 100 : 0;
                                        @endphp
                                        <div class="progress-bar" style="width: {{ $percentage }}%; background-color: {{ $qualityColor }};"></div>
                                    </div>
                                    <small class="text-muted">{{ number_format($percentage, 1) }}% vendido</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Segunda fila de métricas -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-truck"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Lotes Activos</span>
                    <span class="info-box-number">{{ $metrics['inventory']['active_lots'] ?? 0 }}</span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-chart-line"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Ventas del Mes</span>
                    <span class="info-box-number">{{ $metrics['operations']['total_sales'] ?? 0 }}</span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Clientes Activos</span>
                    <span class="info-box-number">{{ $metrics['operations']['active_customers'] ?? 0 }}</span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-danger"><i class="fas fa-percentage"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Margen Utilidad</span>
                    <span class="info-box-number">{{ number_format($metrics['financial']['profit_margin'] ?? 0, 1) }}%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos y Tablas -->
    <div class="row">
        <!-- Distribución por Calidad -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-1"></i>
                        Distribución por Calidad
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="qualityChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>

        <!-- Alertas del Sistema -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Alertas del Sistema
                    </h3>
                </div>
                <div class="card-body">
                    @if(isset($metrics['alerts']) && count($metrics['alerts']) > 0)
                        @foreach($metrics['alerts'] as $alert)
                        <div class="alert alert-{{ $alert['type'] == 'danger' ? 'danger' : ($alert['type'] == 'warning' ? 'warning' : 'info') }} alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h5><i class="icon fas fa-{{ $alert['type'] == 'danger' ? 'ban' : ($alert['type'] == 'warning' ? 'exclamation-triangle' : 'info') }}"></i> 
                                {{ ucfirst($alert['priority']) }}!</h5>
                            {{ $alert['message'] }}
                        </div>
                        @endforeach
                    @else
                        <div class="alert alert-success">
                            <h5><i class="icon fas fa-check"></i> ¡Perfecto!</h5>
                            No hay alertas en este momento. Todo funciona correctamente.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Ventas Recientes -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shopping-cart mr-1"></i>
                        Ventas Recientes
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('sales.index') }}" class="btn btn-tool">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Peso (kg)</th>
                                <th>Monto</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSales ?? [] as $sale)
                            <tr>
                                <td>
                                    <a href="#" class="text-primary">{{ $sale->sale_code ?? $sale->invoice_number }}</a>
                                </td>
                                <td>{{ $sale->customer->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $sale->sale_date->format('d/m/Y') }}</span>
                                </td>
                                <td>{{ number_format($sale->total_weight ?? 0, 2) }} kg</td>
                                <td>
                                    <strong>${{ number_format($sale->total_amount, 2) }}</strong>
                                </td>
                                <td>
                                    @switch($sale->status)
                                        @case('delivered')
                                            <span class="badge badge-success">Entregado</span>
                                            @break
                                        @case('pending')
                                            <span class="badge badge-warning">Pendiente</span>
                                            @break
                                        @default
                                            <span class="badge badge-secondary">{{ ucfirst($sale->status) }}</span>
                                    @endswitch
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-shopping-cart fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">No hay ventas recientes</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt mr-1"></i>
                        Acciones Rápidas
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-6">
                            <a href="{{ route('lots.create') }}" class="btn btn-app bg-success">
                                <i class="fas fa-plus"></i> Nuevo Lote
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="{{ route('sales.create') }}" class="btn btn-app bg-primary">
                                <i class="fas fa-shopping-cart"></i> Nueva Venta
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="{{ route('customers.create') }}" class="btn btn-app bg-info">
                                <i class="fas fa-user-plus"></i> Nuevo Cliente
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="{{ route('payments.create') }}" class="btn btn-app bg-warning">
                                <i class="fas fa-money-bill-wave"></i> Nuevo Pago
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(function () {
    // Quality Distribution Chart
    const qualityData = @json($metrics['inventory']['quality_distribution'] ?? []);
    
    if (qualityData.length > 0) {
        const ctx = document.getElementById('qualityChart').getContext('2d');
        
        // Extract labels, data and colors from the structured array
        const labels = qualityData.map(item => item.name);
        const data = qualityData.map(item => item.weight);
        const colors = qualityData.map(item => item.color);
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'bottom'
                }
            }
        });
    }

});
</script>
@endpush