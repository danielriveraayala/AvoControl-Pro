@extends('layouts.admin')

@section('title', 'Análisis de Proveedores')
@section('page-title', 'Análisis de Proveedores')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Análisis de Proveedores</li>
@endsection

@section('content')
<div class="content">
    <div class="container-fluid">

        <!-- Nota informativa -->
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Nota:</strong> Este análisis muestra métricas y estadísticas de los proveedores basadas en las compras de lotes realizadas.
                </div>
            </div>
        </div>

        <!-- Métricas Principales -->
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="info-box" data-metric="total-suppliers">
                    <span class="info-box-icon bg-success elevation-1">
                        <i class="fas fa-users"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">
                            Total Proveedores
                            <i class="fas fa-question-circle text-muted ml-1"
                               data-toggle="tooltip"
                               data-placement="top"
                               title="Número de proveedores que han suministrado lotes en el período seleccionado."></i>
                        </span>
                        <span class="info-box-number">
                            {{ $supplierRanking->count() }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="info-box" data-metric="total-purchases">
                    <span class="info-box-icon bg-info elevation-1">
                        <i class="fas fa-shopping-basket"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">
                            Total Compras
                            <i class="fas fa-question-circle text-muted ml-1"
                               data-toggle="tooltip"
                               data-placement="top"
                               title="Valor total de las compras realizadas a proveedores en el período."></i>
                        </span>
                        <span class="info-box-number">
                            ${{ number_format($supplierRanking->sum('total_amount'), 2) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="info-box" data-metric="avg-price">
                    <span class="info-box-icon bg-warning elevation-1">
                        <i class="fas fa-tag"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">
                            Precio Promedio/Kg
                            <i class="fas fa-question-circle text-muted ml-1"
                               data-toggle="tooltip"
                               data-placement="top"
                               title="Precio promedio por kilogramo pagado a los proveedores."></i>
                        </span>
                        <span class="info-box-number">
                            ${{ number_format($supplierRanking->avg('avg_price') ?? 0, 2) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="info-box" data-metric="pending-payments">
                    <span class="info-box-icon bg-danger elevation-1">
                        <i class="fas fa-credit-card"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">
                            Pagos Pendientes
                            <i class="fas fa-question-circle text-muted ml-1"
                               data-toggle="tooltip"
                               data-placement="top"
                               title="Monto total pendiente de pago a proveedores."></i>
                        </span>
                        <span class="info-box-number">
                            ${{ number_format($paymentAnalysis['pending_amount'] ?? 0, 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tablas de Análisis -->
        <div class="row mt-3">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-trophy text-warning"></i>
                            Top 10 Proveedores
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-secondary">Datos históricos</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-valign-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Proveedor</th>
                                        <th>
                                            Total Compras
                                            <i class="fas fa-question-circle text-muted ml-1"
                                               data-toggle="tooltip"
                                               data-placement="top"
                                               title="Monto total invertido en compras de aguacate de este proveedor. Te ayuda a ver en quién gastas más dinero."></i>
                                        </th>
                                        <th>
                                            Peso Total
                                            <i class="fas fa-question-circle text-muted ml-1"
                                               data-toggle="tooltip"
                                               data-placement="top"
                                               title="Kilogramos totales de aguacate comprados de este proveedor. Indica el volumen de tu relación comercial."></i>
                                        </th>
                                        <th>
                                            Lotes
                                            <i class="fas fa-question-circle text-muted ml-1"
                                               data-toggle="tooltip"
                                               data-placement="top"
                                               title="Número de compras o entregas realizadas con este proveedor. Más lotes puede indicar mayor frecuencia de abastecimiento."></i>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($supplierRanking->take(10) as $index => $supplier)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><strong>{{ $supplier['supplier_name'] }}</strong></td>
                                        <td>${{ number_format($supplier['total_amount'], 2) }}</td>
                                        <td>{{ number_format($supplier['total_weight'], 0) }} kg</td>
                                        <td><span class="badge badge-info">{{ $supplier['total_lots'] }}</span></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-star text-primary"></i>
                            Calidad y Confiabilidad de Proveedores
                            <i class="fas fa-question-circle text-muted ml-2"
                               data-toggle="tooltip"
                               data-placement="top"
                               title="Evalúa la confiabilidad de cada proveedor basado en diversidad de calidades, consistencia de suministro y volumen promedio por entrega."></i>
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-secondary">Datos históricos</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-valign-middle">
                                <thead>
                                    <tr>
                                        <th>Proveedor</th>
                                        <th>
                                            Calidades
                                            <i class="fas fa-question-circle text-muted ml-1"
                                               data-toggle="tooltip"
                                               data-placement="top"
                                               title="Número de calidades diferentes que ofrece el proveedor. Más variedad indica mayor versatilidad."></i>
                                        </th>
                                        <th>
                                            Meses Activo
                                            <i class="fas fa-question-circle text-muted ml-1"
                                               data-toggle="tooltip"
                                               data-placement="top"
                                               title="Número de meses en los que has recibido suministros. Mayor número indica más consistencia."></i>
                                        </th>
                                        <th>
                                            Score Confiabilidad
                                            <i class="fas fa-question-circle text-muted ml-1"
                                               data-toggle="tooltip"
                                               data-placement="top"
                                               title="Puntaje de confiabilidad basado en diversidad de calidades y consistencia de suministro (0-100)."></i>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($performanceAnalysis->take(10) as $performance)
                                    <tr>
                                        <td>{{ $performance['supplier_name'] }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ $performance['qualities_offered'] }}</span>
                                        </td>
                                        <td>{{ $performance['months_active'] }} meses</td>
                                        <td>
                                            <div class="progress progress-xs">
                                                <div class="progress-bar bg-{{ $performance['reliability_score'] >= 70 ? 'success' : ($performance['reliability_score'] >= 40 ? 'warning' : 'danger') }}" style="width: {{ $performance['reliability_score'] }}%"></div>
                                            </div>
                                            <small>{{ number_format($performance['reliability_score'], 0) }}/100</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficas -->
        <div class="row mt-3">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar text-info"></i>
                            Evolución de Compras
                            <i class="fas fa-question-circle text-muted ml-2"
                               data-toggle="tooltip"
                               data-placement="top"
                               title="Muestra cómo ha evolucionado el valor de las compras realizadas a proveedores a lo largo del año actual, mes por mes."></i>
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-secondary">Datos históricos</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="purchaseEvolutionChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie text-success"></i>
                            Distribución por Calidad
                            <i class="fas fa-question-circle text-muted ml-2"
                               data-toggle="tooltip"
                               data-placement="top"
                               title="Porcentaje de cada calidad de aguacate que has comprado históricamente. Te ayuda a ver qué calidades predominan en tu inventario."></i>
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-secondary">Datos históricos</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="qualityDistributionChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Análisis de Precios -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-tags text-warning"></i>
                            Análisis de Precios por Proveedor
                            <i class="fas fa-question-circle text-muted ml-2"
                               data-toggle="tooltip"
                               data-placement="top"
                               title="Compara los precios mínimos, máximos y promedio que pagas a cada proveedor. Te ayuda a identificar quién ofrece mejores precios y quién tiene precios más estables."></i>
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-secondary">Datos históricos</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Proveedor</th>
                                        <th>Precio Mínimo</th>
                                        <th>Precio Máximo</th>
                                        <th>Precio Promedio</th>
                                        <th>Rango de Precio</th>
                                        <th>Análisis</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($priceAnalysis as $price)
                                    <tr>
                                        <td><strong>{{ $price['supplier_name'] }}</strong></td>
                                        <td class="text-success">${{ number_format($price['min_price'], 2) }}</td>
                                        <td class="text-danger">${{ number_format($price['max_price'], 2) }}</td>
                                        <td class="text-info">${{ number_format($price['avg_price'], 2) }}</td>
                                        <td>${{ number_format($price['price_range'], 2) }}</td>
                                        <td>
                                            @if($price['price_range'] < 5)
                                                <span class="badge badge-success">Estable</span>
                                            @elseif($price['price_range'] < 10)
                                                <span class="badge badge-warning">Variable</span>
                                            @else
                                                <span class="badge badge-danger">Volátil</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Inicializar tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Variables para las gráficas
    let charts = {};

    // Función para inicializar todas las gráficas
    function initializeCharts() {
        initializePurchaseEvolution();
        initializeQualityDistribution();
    }

    // Función para inicializar evolución de compras
    function initializePurchaseEvolution() {
        const ctx = document.getElementById('purchaseEvolutionChart');
        if (!ctx) return;

        const evolutionData = {!! json_encode($purchaseEvolution) !!};

        charts.purchaseEvolution = new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: evolutionData.map(item => item.month),
                datasets: [{
                    label: 'Monto Total',
                    data: evolutionData.map(item => item.total_amount),
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Función para inicializar distribución por calidad
    function initializeQualityDistribution() {
        const ctx = document.getElementById('qualityDistributionChart');
        if (!ctx) return;

        // Por ahora usando datos dummy
        charts.qualityDistribution = new Chart(ctx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Primera', 'Segunda', 'Tercera'],
                datasets: [{
                    data: [45, 35, 20],
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // Inicializar gráficas al cargar la página
    initializeCharts();
});
</script>
@endpush
