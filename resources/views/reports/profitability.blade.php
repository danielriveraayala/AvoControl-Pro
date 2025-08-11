@extends('layouts.admin')

@section('title', 'Análisis de Rentabilidad')
@section('page-title', 'Análisis de Rentabilidad')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Rentabilidad</li>
@endsection

@section('content')

<div class="content">
    <div class="container-fluid">
        <!-- Nota informativa -->
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Nota:</strong> Este análisis muestra métricas históricas de rentabilidad basadas en ventas confirmadas, entregadas o enviadas. Las ventas en borrador no están incluidas.
                </div>
            </div>
        </div>

        <!-- Métricas Principales usando Info Boxes de AdminLTE -->
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="info-box" data-metric="total-profit">
                    <span class="info-box-icon bg-success elevation-1">
                        <i class="fas fa-dollar-sign"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">
                            Ganancia Total
                            <i class="fas fa-question-circle text-muted ml-1"
                               data-toggle="tooltip"
                               data-placement="top"
                               title="Dinero que queda después de restar todos los costos de compra de aguacate. Es lo que realmente ganaste en el período seleccionado."></i>
                        </span>
                        <span class="info-box-number">
                            ${{ number_format($generalMetrics['total_profit'], 2) }}
                        </span>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: 100%"></div>
                        </div>
                        <span class="progress-description">
                            Período analizado
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="info-box" data-metric="profit-margin">
                    <span class="info-box-icon bg-{{ $generalMetrics['profit_margin'] >= 20 ? 'success' : ($generalMetrics['profit_margin'] >= 10 ? 'warning' : 'danger') }} elevation-1">
                        <i class="fas fa-percentage"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">
                            Margen Promedio
                            <i class="fas fa-question-circle text-muted ml-1"
                               data-toggle="tooltip"
                               data-placement="top"
                               title="Porcentaje de ganancia sobre las ventas. Por ejemplo, si vendes $100 y te costó $70, tu margen es 30%. Entre más alto, mejor."></i>
                        </span>
                        <span class="info-box-number">
                            {{ number_format($generalMetrics['profit_margin'], 1) }}%
                        </span>
                        <div class="progress">
                            <div class="progress-bar bg-{{ $generalMetrics['profit_margin'] >= 20 ? 'success' : ($generalMetrics['profit_margin'] >= 10 ? 'warning' : 'danger') }}"
                                 style="width: {{ min($generalMetrics['profit_margin'] * 2, 100) }}%"></div>
                        </div>
                        <span class="progress-description">
                            {{ $generalMetrics['profit_margin'] >= 20 ? 'Excelente margen' : ($generalMetrics['profit_margin'] >= 10 ? 'Margen aceptable' : 'Margen bajo') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="info-box" data-metric="sales-count">
                    <span class="info-box-icon bg-info elevation-1">
                        <i class="fas fa-shopping-cart"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">
                            Total de Ventas
                            <i class="fas fa-question-circle text-muted ml-1"
                               data-toggle="tooltip"
                               data-placement="top"
                               title="Número total de ventas realizadas en el período. Cada factura o venta cuenta como una transacción."></i>
                        </span>
                        <span class="info-box-number">
                            {{ $generalMetrics['sales_count'] }}
                        </span>
                        <div class="progress">
                            <div class="progress-bar bg-info" style="width: 85%"></div>
                        </div>
                        <span class="progress-description">
                            Transacciones realizadas
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="info-box" data-metric="avg-profit">
                    <span class="info-box-icon bg-primary elevation-1">
                        <i class="fas fa-chart-bar"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">
                            Ganancia Promedio
                            <i class="fas fa-question-circle text-muted ml-1"
                               data-toggle="tooltip"
                               data-placement="top"
                               title="Cuánto ganas en promedio por cada venta. Se calcula dividiendo la ganancia total entre el número de ventas."></i>
                        </span>
                        <span class="info-box-number">
                            ${{ number_format($generalMetrics['avg_profit_per_sale'], 2) }}
                        </span>
                        <div class="progress">
                            <div class="progress-bar bg-primary" style="width: 70%"></div>
                        </div>
                        <span class="progress-description">
                            Por venta realizada
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadística de Ventas en Borrador -->
        @if($draftSales['count'] > 0)
        <div class="row mt-3">
            <div class="col-12">
                <div class="alert alert-warning">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-1">
                                <i class="fas fa-exclamation-triangle"></i>
                                Ventas Pendientes de Confirmar
                                <i class="fas fa-question-circle text-muted ml-2"
                                   data-toggle="tooltip"
                                   data-placement="top"
                                   title="Ventas que están en borrador y aún no se han confirmado. Estas ventas no están incluidas en las estadísticas de rentabilidad hasta que se confirmen."></i>
                            </h5>
                            <p class="mb-0">
                                Tienes <strong>{{ $draftSales['count'] }}</strong> {{ $draftSales['count'] == 1 ? 'venta' : 'ventas' }} en borrador por un valor de
                                <strong>${{ number_format($draftSales['total_amount'], 2) }}</strong>
                                ({{ number_format($draftSales['total_weight'], 0) }} kg).
                            </p>
                        </div>
                        <div class="col-md-4 text-right">
                            <div class="d-flex justify-content-end align-items-center">
                                <div class="mr-3">
                                    <small class="text-muted">Promedio por venta:</small>
                                    <div class="font-weight-bold">${{ number_format($draftSales['avg_amount'], 2) }}</div>
                                </div>
                                <div>
                                    <small class="text-muted">{{ $draftSales['customers'] }} {{ $draftSales['customers'] == 1 ? 'cliente' : 'clientes' }}</small>
                                    <div class="font-weight-bold">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Tablas de Datos -->
        <div class="row mt-3">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-trophy text-warning"></i>
                            Top 10 Clientes por Rentabilidad
                            <i class="fas fa-question-circle text-muted ml-2"
                               data-toggle="tooltip"
                               data-placement="top"
                               title="Los clientes que te han generado más dinero después de descontar los costos. Estos son tus clientes más valiosos."></i>
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-secondary mr-2">Datos históricos</span>
                            <span class="badge badge-success">{{ $profitByCustomer->count() }} clientes</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-valign-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Cliente</th>
                                        <th>Ganancia</th>
                                        <th>Margen</th>
                                        <th>Ventas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($profitByCustomer->take(10) as $index => $cliente)
                                    <tr>
                                        <td>
                                            <span class="badge badge-{{ $index < 3 ? 'warning' : 'secondary' }}">
                                                {{ $index + 1 }}
                                                @if($index === 0) <i class="fas fa-crown"></i> @endif
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ $cliente['customer_name'] }}</strong>
                                        </td>
                                        <td>
                                            <span class="text-success font-weight-bold">
                                                ${{ number_format($cliente['profit'], 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $cliente['margin'] >= 20 ? 'success' : ($cliente['margin'] >= 10 ? 'warning' : 'danger') }}">
                                                {{ number_format($cliente['margin'], 1) }}%
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $cliente['sales_count'] }}</span>
                                        </td>
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
                            <i class="fas fa-gem text-primary"></i>
                            Top 10 Ventas Más Rentables
                            <i class="fas fa-question-circle text-muted ml-2"
                               data-toggle="tooltip"
                               data-placement="top"
                               title="Las ventas individuales que te dieron el mejor margen de ganancia. Te ayuda a identificar qué tipo de ventas son más rentables."></i>
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-secondary mr-2">Datos históricos</span>
                            <span class="badge badge-primary">{{ $topProfitableSales->count() }} ventas</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-valign-middle">
                                <thead>
                                    <tr>
                                        <th>Venta</th>
                                        <th>Cliente</th>
                                        <th>Ganancia</th>
                                        <th>Margen</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topProfitableSales->take(10) as $venta)
                                    <tr>
                                        <td>
                                            <span class="text-primary font-weight-bold">
                                                #{{ $venta['sale_code'] }}
                                            </span>
                                        </td>
                                        <td>{{ $venta['customer'] }}</td>
                                        <td>
                                            <span class="text-success font-weight-bold">
                                                ${{ number_format($venta['profit'], 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="progress progress-xs">
                                                <div class="progress-bar bg-{{ $venta['margin'] >= 25 ? 'success' : ($venta['margin'] >= 15 ? 'warning' : 'danger') }}"
                                                     style="width: {{ min($venta['margin'], 100) }}%"></div>
                                            </div>
                                            <small>{{ number_format($venta['margin'], 1) }}%</small>
                                        </td>
                                        <td class="text-muted">
                                            <small>{{ $venta['date'] }}</small>
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

        <!-- Análisis de Precios por Calidad -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-tags text-info"></i>
                            Análisis de Precios por Calidad
                            <i class="fas fa-question-circle text-muted ml-2"
                               data-toggle="tooltip"
                               data-placement="top"
                               title="Compara cuánto pagas por cada calidad de aguacate vs. cuánto lo vendes. Te ayuda a decidir qué calidades son más rentables comprar."></i>
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-secondary mr-2">Datos históricos</span>
                            <span class="badge badge-info">{{ $priceAnalysis->count() }} calidades</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Calidad</th>
                                        <th>Precio Compra Promedio</th>
                                        <th>Precio Venta Promedio</th>
                                        <th>Ganancia por Kg</th>
                                        <th>Margen Promedio</th>
                                        <th>Análisis</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($priceAnalysis as $precio)
                                    <tr>
                                        <td>
                                            <span class="badge" style="background-color: {{ $precio['color'] }}; color: white;">
                                                {{ $precio['quality'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-danger font-weight-bold">
                                                ${{ number_format($precio['avg_purchase_price'], 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-success font-weight-bold">
                                                ${{ number_format($precio['avg_sale_price'], 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-info font-weight-bold">
                                                ${{ number_format($precio['price_spread'], 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="progress progress-sm">
                                                <div class="progress-bar bg-{{ $precio['markup_percentage'] >= 20 ? 'success' : ($precio['markup_percentage'] >= 10 ? 'warning' : 'danger') }}"
                                                     style="width: {{ min($precio['markup_percentage'] * 2, 100) }}%"></div>
                                            </div>
                                            <small>{{ number_format($precio['markup_percentage'], 1) }}%</small>
                                        </td>
                                        <td>
                                            <i class="fas fa-arrow-up text-success" title="Tendencia positiva"></i>
                                            <small class="text-muted">Rentable</small>
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

        <!-- Gráficas ChartJS -->
        <div class="row mt-3">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar text-primary"></i>
                            Evolución de Rentabilidad
                            <i class="fas fa-question-circle text-muted ml-2"
                               data-toggle="tooltip"
                               data-placement="top"
                               title="Cómo ha cambiado tu rentabilidad a lo largo del tiempo. Te permite ver tendencias y identificar períodos buenos or malos."></i>
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-secondary">Datos históricos</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="profitEvolutionChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie text-info"></i>
                            Rentabilidad por Calidad
                            <i class="fas fa-question-circle text-muted ml-2"
                               data-toggle="tooltip"
                               data-placement="top"
                               title="Qué porcentaje de tus ganancias viene de cada calidad de aguacate. Te ayuda a enfocar en las calidades más rentables."></i>
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-secondary">Datos históricos</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="profitByQualityChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line text-success"></i>
                            Comparativa Mensual
                            <i class="fas fa-question-circle text-muted ml-2"
                               data-toggle="tooltip"
                               data-placement="top"
                               title="Compara ingresos, costos y ganancias de los últimos meses. Te ayuda a identificar patrones estacionales en tu negocio."></i>
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-secondary">Datos históricos</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyComparisonChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-doughnut text-warning"></i>
                            Distribución de Márgenes
                            <i class="fas fa-question-circle text-muted ml-2"
                               data-toggle="tooltip"
                               data-placement="top"
                               title="Cuántas de tus ventas tienen márgenes altos, medios o bajos. Te ayuda a ver si la mayoría de tus ventas son rentables."></i>
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-secondary">Datos históricos</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="marginDistributionChart" style="height: 300px;"></canvas>
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

    // Configurar moment.js en español
    moment.locale('es');

    // Función para inicializar gráficas
    function initializeCharts() {
        initializeProfitEvolution();
        initializeProfitByQuality();
        initializeMonthlyComparison();
        initializeMarginDistribution();
    }

    // Función para inicializar gráfica de evolución
    function initializeProfitEvolution() {
        const profitEvolutionCtx = document.getElementById('profitEvolutionChart').getContext('2d');
        charts.profitEvolution = new Chart(profitEvolutionCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($profitEvolution, 'date')) !!},
            datasets: [{
                label: 'Ganancia',
                data: {!! json_encode(array_column($profitEvolution, 'profit')) !!},
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                fill: true,
                tension: 0.4
            }, {
                label: 'Ingresos',
                data: {!! json_encode(array_column($profitEvolution, 'revenue')) !!},
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                fill: false,
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
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': $' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
        });
    }

    // Función para inicializar gráfica de rentabilidad por calidad
    function initializeProfitByQuality() {
        const profitByQualityCtx = document.getElementById('profitByQualityChart').getContext('2d');
        charts.profitByQuality = new Chart(profitByQualityCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($profitByQuality->pluck('quality')->toArray()) !!},
            datasets: [{
                data: {!! json_encode($profitByQuality->pluck('profit')->toArray()) !!},
                backgroundColor: {!! json_encode($profitByQuality->pluck('color')->toArray()) !!},
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return context.label + ': $' + value.toLocaleString() + ' (' + percentage + '%)';
                        }
                    }
                },
                legend: {
                    position: 'bottom'
                }
            }
        }
        });
    }

    // Función para inicializar gráfica de comparativa mensual
    function initializeMonthlyComparison() {
        const monthlyComparisonCtx = document.getElementById('monthlyComparisonChart').getContext('2d');
        charts.monthlyComparison = new Chart(monthlyComparisonCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_column($monthlyComparison, 'month')) !!},
            datasets: [{
                label: 'Ingresos',
                data: {!! json_encode(array_column($monthlyComparison, 'revenue')) !!},
                backgroundColor: 'rgba(0, 123, 255, 0.8)',
                borderColor: '#007bff',
                borderWidth: 1
            }, {
                label: 'Costos',
                data: {!! json_encode(array_column($monthlyComparison, 'cost')) !!},
                backgroundColor: 'rgba(220, 53, 69, 0.8)',
                borderColor: '#dc3545',
                borderWidth: 1
            }, {
                label: 'Ganancia',
                data: {!! json_encode(array_column($monthlyComparison, 'profit')) !!},
                backgroundColor: 'rgba(40, 167, 69, 0.8)',
                borderColor: '#28a745',
                borderWidth: 1
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
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': $' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
        });
    }

    // Función para inicializar gráfica de distribución de márgenes
    function initializeMarginDistribution() {
        const marginDistributionCtx = document.getElementById('marginDistributionChart').getContext('2d');
        const marginData = {!! json_encode($marginAnalysis['distribution']) !!};
        charts.marginDistribution = new Chart(marginDistributionCtx, {
        type: 'pie',
        data: {
            labels: Object.keys(marginData),
            datasets: [{
                data: Object.values(marginData),
                backgroundColor: [
                    '#dc3545', // 0-10% - Rojo
                    '#ffc107', // 10-20% - Amarillo
                    '#fd7e14', // 20-30% - Naranja
                    '#20c997', // 30-40% - Teal
                    '#28a745', // 40-50% - Verde
                    '#6f42c1'  // 50%+ - Púrpura
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.parsed + ' ventas (' + percentage + '%)';
                        }
                    }
                },
                legend: {
                    position: 'bottom'
                }
            }
        }
        });
    }

    // Inicializar gráficas al cargar la página
    initializeCharts();
});
</script>
@endpush
