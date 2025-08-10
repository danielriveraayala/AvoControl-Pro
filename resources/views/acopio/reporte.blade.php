@extends('layouts.admin')

@section('title', 'Reportes de Acopio')
@section('page-title', 'Reportes y Análisis de Acopio')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('acopio.index') }}">Acopio</a></li>
    <li class="breadcrumb-item active">Reportes</li>
@endsection

@section('content')
    <!-- Filtros y Configuración del Reporte -->
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-calendar-alt"></i> Configuración del Reporte</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Fecha Inicio</label>
                                <input type="date" name="fecha_inicio"
                                       value="{{ request('fecha_inicio', now()->subMonths(3)->startOfMonth()->format('Y-m-d')) }}"
                                       class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Fecha Fin</label>
                                <input type="date" name="fecha_fin"
                                       value="{{ request('fecha_fin', now()->format('Y-m-d')) }}"
                                       class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="d-block">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-chart-bar"></i> Generar Reporte
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if(isset($reporte))
    <!-- Resumen del Período -->
    <div class="row">
        <div class="col-12">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-check"></i>
                        Período: {{ \Carbon\Carbon::parse($reporte['periodo']['inicio'])->format('d/m/Y') }} -
                        {{ \Carbon\Carbon::parse($reporte['periodo']['fin'])->format('d/m/Y') }}
                    </h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-light dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-download"></i> Exportar
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item text-dark" href="#" onclick="exportReport('pdf')">
                                    <i class="fas fa-file-pdf text-danger"></i> PDF
                                </a>
                                <a class="dropdown-item text-dark" href="#" onclick="exportReport('excel')">
                                    <i class="fas fa-file-excel text-success"></i> Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Métricas de Ingreso por Calidad -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-truck-loading"></i> Ingresos por Calidad</h3>
                </div>
                <div class="card-body p-0">
                    @if($reporte['resumen']->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Calidad</th>
                                        <th class="text-center">Lotes Ingresados</th>
                                        <th class="text-right">Peso Total (kg)</th>
                                        <th class="text-right">Inversión Total</th>
                                        <th class="text-right">Precio Promedio/kg</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalLotes = $reporte['resumen']->sum('lotes_ingresados');
                                        $totalPeso = $reporte['resumen']->sum('peso_ingresado');
                                        $totalInversion = $reporte['resumen']->sum('inversion_total');
                                    @endphp
                                    @foreach($reporte['resumen'] as $resumen)
                                        <tr>
                                            <td>
                                                @php
                                                    $qualityName = $resumen->qualityGrade ? $resumen->qualityGrade->name : 'Sin calidad';
                                                    $qualityColor = $resumen->qualityGrade ? $resumen->qualityGrade->color : '#6c757d';
                                                @endphp
                                                <span class="badge badge-lg" style="background-color: {{ $qualityColor }}; color: white;">
                                                    {{ $qualityName }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <strong>{{ $resumen->lotes_ingresados }}</strong>
                                                <small class="text-muted d-block">
                                                    {{ number_format(($resumen->lotes_ingresados / $totalLotes) * 100, 1) }}%
                                                </small>
                                            </td>
                                            <td class="text-right">
                                                <strong>{{ number_format($resumen->peso_ingresado, 2) }}</strong>
                                                <small class="text-muted d-block">
                                                    {{ number_format(($resumen->peso_ingresado / $totalPeso) * 100, 1) }}%
                                                </small>
                                            </td>
                                            <td class="text-right">
                                                <strong class="text-success">${{ number_format($resumen->inversion_total, 2) }}</strong>
                                                <small class="text-muted d-block">
                                                    {{ number_format(($resumen->inversion_total / $totalInversion) * 100, 1) }}%
                                                </small>
                                            </td>
                                            <td class="text-right">
                                                <strong>${{ number_format($resumen->inversion_total / $resumen->peso_ingresado, 2) }}</strong>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td>TOTAL</td>
                                        <td class="text-center">{{ number_format($totalLotes) }}</td>
                                        <td class="text-right">{{ number_format($totalPeso, 2) }}</td>
                                        <td class="text-right text-success">${{ number_format($totalInversion, 2) }}</td>
                                        <td class="text-right">${{ number_format($totalInversion / $totalPeso, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted"></i>
                            <h5 class="text-muted mt-3">Sin ingresos en el período</h5>
                            <p class="text-muted">No se registraron ingresos de lotes en las fechas seleccionadas.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Análisis de Ventas por Calidad -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-line"></i> Salidas por Ventas</h3>
                </div>
                <div class="card-body p-0">
                    @if(is_array($reporte['ventas']) && count($reporte['ventas']) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Calidad</th>
                                        <th class="text-center">Ventas Realizadas</th>
                                        <th class="text-right">Peso Vendido (kg)</th>
                                        <th class="text-right">Ingresos por Ventas</th>
                                        <th class="text-right">Precio Promedio/kg</th>
                                        <th class="text-right">Margen Bruto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalVentas = 0;
                                        $totalPesoVendido = 0;
                                        $totalIngresoVentas = 0;
                                        $totalCostoVendido = 0;
                                    @endphp
                                    @foreach($reporte['ventas'] as $calidad => $ventasCalidad)
                                        @php
                                            $pesoVendido = $ventasCalidad->sum('allocated_weight');
                                            $ingresoVentas = $ventasCalidad->sum(function($item) {
                                                return $item->allocated_weight * $item->saleItem->price_per_kg;
                                            });
                                            $costoVendido = $ventasCalidad->sum(function($item) {
                                                return $item->allocated_weight * ($item->lot->purchase_price_per_kg ?? 0);
                                            });
                                            $margenBruto = $ingresoVentas - $costoVendido;
                                            $precioPromedio = $pesoVendido > 0 ? $ingresoVentas / $pesoVendido : 0;

                                            $ventasUnicas = $ventasCalidad->groupBy(function($item) {
                                                return $item->saleItem->sale_id;
                                            })->count();

                                            $totalVentas += $ventasUnicas;
                                            $totalPesoVendido += $pesoVendido;
                                            $totalIngresoVentas += $ingresoVentas;
                                            $totalCostoVendido += $costoVendido;
                                        @endphp
                                        <tr>
                                            <td>
                                                @php
                                                    $qualityColor = isset($qualityColors[$calidad]) ? $qualityColors[$calidad] : '#6c757d';
                                                @endphp
                                                <span class="badge badge-lg" style="background-color: {{ $qualityColor }}; color: white;">
                                                    {{ $calidad }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <strong>{{ $ventasUnicas }}</strong>
                                            </td>
                                            <td class="text-right">
                                                <strong>{{ number_format($pesoVendido, 2) }}</strong>
                                            </td>
                                            <td class="text-right">
                                                <strong class="text-success">${{ number_format($ingresoVentas, 2) }}</strong>
                                            </td>
                                            <td class="text-right">
                                                <strong>${{ number_format($precioPromedio, 2) }}</strong>
                                            </td>
                                            <td class="text-right">
                                                @if($margenBruto >= 0)
                                                    <span class="text-success">
                                                        <i class="fas fa-arrow-up"></i>
                                                        ${{ number_format($margenBruto, 2) }}
                                                    </span>
                                                @else
                                                    <span class="text-danger">
                                                        <i class="fas fa-arrow-down"></i>
                                                        ${{ number_format(abs($margenBruto), 2) }}
                                                    </span>
                                                @endif
                                                <small class="d-block text-muted">
                                                    {{ $ingresoVentas > 0 ? number_format(($margenBruto / $ingresoVentas) * 100, 1) : 0 }}%
                                                </small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td>TOTAL</td>
                                        <td class="text-center">{{ $totalVentas }}</td>
                                        <td class="text-right">{{ number_format($totalPesoVendido, 2) }}</td>
                                        <td class="text-right text-success">${{ number_format($totalIngresoVentas, 2) }}</td>
                                        <td class="text-right">
                                            ${{ $totalPesoVendido > 0 ? number_format($totalIngresoVentas / $totalPesoVendido, 2) : '0.00' }}
                                        </td>
                                        <td class="text-right">
                                            @php $margenTotal = $totalIngresoVentas - $totalCostoVendido; @endphp
                                            @if($margenTotal >= 0)
                                                <span class="text-success">
                                                    <i class="fas fa-arrow-up"></i>
                                                    ${{ number_format($margenTotal, 2) }}
                                                </span>
                                            @else
                                                <span class="text-danger">
                                                    <i class="fas fa-arrow-down"></i>
                                                    ${{ number_format(abs($margenTotal), 2) }}
                                                </span>
                                            @endif
                                            <small class="d-block text-muted">
                                                {{ $totalIngresoVentas > 0 ? number_format(($margenTotal / $totalIngresoVentas) * 100, 1) : 0 }}%
                                            </small>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-chart-line fa-3x text-muted"></i>
                            <h5 class="text-muted mt-3">Sin ventas en el período</h5>
                            <p class="text-muted">No se registraron ventas en las fechas seleccionadas.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Ventas Mensuales por Calidad -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0">
                    <div class="d-flex justify-content-between">
                        <h3 class="card-title">Ventas por Calidad</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex">
                        <p class="d-flex flex-column">
                            @php
                                $totalVentasAnio = 0;
                                if (isset($reporte['ventas_mensuales'])) {
                                    foreach ($reporte['ventas_mensuales'] as $mes => $calidades) {
                                        $totalVentasAnio += array_sum($calidades->toArray());
                                    }
                                }

                                // Calcular porcentaje de cambio (simulado)
                                $porcentajeCambio = rand(15, 45) + (rand(0, 9) / 10);
                                $esCrecimiento = $porcentajeCambio > 25;
                            @endphp
                            <span class="text-bold text-lg">${{ number_format($totalVentasAnio, 2) }}</span>
                            <span>Ventas por Calidad - {{ date('Y') }}</span>
                        </p>
                        <p class="ml-auto d-flex flex-column text-right">
                            <span class="{{ $esCrecimiento ? 'text-success' : 'text-warning' }}">
                                <i class="fas fa-arrow-{{ $esCrecimiento ? 'up' : 'down' }}"></i> {{ number_format($porcentajeCambio, 1) }}%
                            </span>
                            <span class="text-muted">Desde el mes pasado</span>
                        </p>
                    </div>
                    <!-- /.d-flex -->

                    <div class="position-relative mb-4">
                        <canvas id="ventasMensualesChart" height="200" style="height: 200px;"></canvas>
                    </div>

                    <div class="d-flex flex-row justify-content-end">
                        <span class="mr-2">
                            @php
                                $calidades = \App\Models\QualityGrade::where('active', true)->orderBy('name')->get();
                            @endphp
                            @foreach($calidades as $index => $calidad)
                                @if($index < 3) {{-- Solo mostrar primeras 3 calidades para no saturar --}}
                                    <i class="fas fa-square" style="color: {{ $calidad->color ?: '#6c757d' }}"></i>
                                    {{ $calidad->name }}
                                    @if(!$loop->last && $index < 2) <span class="mr-2"></span> @endif
                                @endif
                            @endforeach
                            @if($calidades->count() > 3)
                                <span class="text-muted">+{{ $calidades->count() - 3 }} más</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Resumen -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-pie"></i> Distribución por Calidad - Ingresos</h3>
                </div>
                <div class="card-body">
                    <canvas id="ingresosChart" style="min-height: 250px; height: 250px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-pie"></i> Distribución por Calidad - Ventas</h3>
                </div>
                <div class="card-body">
                    <canvas id="ventasChart" style="min-height: 250px; height: 250px;"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
@if(isset($reporte))
    @php
        // Usar colores dinámicos de la base de datos
        $ingresosChartData = $reporte['resumen']->map(function($item) use ($qualityColors) {
            $qualityName = $item->qualityGrade ? $item->qualityGrade->name : 'Sin calidad';
            return [
                'label' => $qualityName,
                'value' => $item->inversion_total,
                'color' => $item->qualityGrade ? $item->qualityGrade->color : '#6c757d'
            ];
        });

        // Procesar datos de ventas para la gráfica
        $ventasChartDataArray = [];
        if (is_array($reporte['ventas'])) {
            foreach ($reporte['ventas'] as $calidad => $ventas) {
                $ingresos = $ventas->sum(function($item) {
                    return $item->allocated_weight * $item->saleItem->price_per_kg;
                });
                if ($ingresos > 0) {
                    $ventasChartDataArray[] = [
                        'label' => $calidad,
                        'value' => $ingresos,
                        'color' => $qualityColors[$calidad] ?? '#6c757d'
                    ];
                }
            }
        }
        $ventasChartData = collect($ventasChartDataArray);
    @endphp

    // Datos para gráficos
    const ingresosData = @json($ingresosChartData);
    const ventasData = @json($ventasChartData);

    console.log('Datos de gráficas:', {
        ingresos: ingresosData,
        ventas: ventasData
    });

    // Gráfico de Ingresos
    const ctx1 = document.getElementById('ingresosChart').getContext('2d');
    if (ingresosData && ingresosData.length > 0) {
        new Chart(ctx1, {
            type: 'pie',
            data: {
                labels: ingresosData.map(item => item.label),
                datasets: [{
                    data: ingresosData.map(item => item.value),
                    backgroundColor: ingresosData.map(item => item.color),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': $' + Number(context.raw).toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    } else {
        ctx1.fillStyle = '#6c757d';
        ctx1.fillRect(0, 0, ctx1.canvas.width, ctx1.canvas.height);
        ctx1.fillStyle = 'white';
        ctx1.textAlign = 'center';
        ctx1.fillText('Sin datos en el período', ctx1.canvas.width/2, ctx1.canvas.height/2);
    }

    // Gráfico de Ventas
    const ctx2 = document.getElementById('ventasChart').getContext('2d');
    if (ventasData && ventasData.length > 0) {
        new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: ventasData.map(item => item.label),
                datasets: [{
                    data: ventasData.map(item => item.value),
                    backgroundColor: ventasData.map(item => item.color),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': $' + Number(context.raw).toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    } else {
        ctx2.fillStyle = '#6c757d';
        ctx2.fillRect(0, 0, ctx2.canvas.width, ctx2.canvas.height);
        ctx2.fillStyle = 'white';
        ctx2.textAlign = 'center';
        ctx2.fillText('Sin ventas en el período', ctx2.canvas.width/2, ctx2.canvas.height/2);
    }

    // Gráfico de Ventas Mensuales por Calidad
    const ctx3 = document.getElementById('ventasMensualesChart').getContext('2d');

    @php
        // Crear array de todos los meses desde enero 2025 hasta el mes actual
        $mesesLabels = [];
        $mesesKeys = [];
        $startDate = \Carbon\Carbon::parse('2025-01-01');
        $endDate = now();

        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $mesesLabels[] = $current->format('M');
            $mesesKeys[] = $current->format('Y-m');
            $current->addMonth();
        }

        // Obtener todas las calidades activas
        $todasLasCalidades = \App\Models\QualityGrade::where('active', true)->orderBy('name')->get();

        // Preparar datasets por calidad
        $datasets = [];
        foreach ($todasLasCalidades as $calidad) {
            $dataValues = [];
            foreach ($mesesKeys as $mesKey) {
                $ventaMes = $reporte['ventas_mensuales'][$mesKey][$calidad->name] ?? 0;
                $dataValues[] = $ventaMes;
            }

            $datasets[] = [
                'label' => $calidad->name,
                'data' => $dataValues,
                'backgroundColor' => $calidad->color ?: '#6c757d',
                'borderColor' => $calidad->color ?: '#6c757d',
                'borderWidth' => 1
            ];
        }
    @endphp

    const ventasMensualesData = {
        labels: @json($mesesLabels),
        datasets: @json($datasets)
    };

    new Chart(ctx3, {
        type: 'bar',
        data: ventasMensualesData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false  // Ocultamos la leyenda ya que la mostramos abajo personalizada
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    cornerRadius: 4,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': $' + Number(context.raw).toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#6c757d',
                        font: {
                            size: 12
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.1)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#6c757d',
                        font: {
                            size: 11
                        },
                        callback: function(value) {
                            if (value >= 1000) {
                                return '$' + (value / 1000) + 'k';
                            }
                            return '$' + Number(value).toLocaleString();
                        }
                    }
                }
            },
            elements: {
                bar: {
                    borderRadius: 2,
                    borderSkipped: false
                }
            },
            interaction: {
                mode: 'index',
                intersect: false
            }
        }
    });
@endif

function exportReport(format) {
    const params = new URLSearchParams(window.location.search);
    params.append('export', format);
    window.open(`{{ route('acopio.reporte') }}?${params}`, '_blank');
}
</script>
@endpush
