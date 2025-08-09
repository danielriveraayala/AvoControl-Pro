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
                                <a class="dropdown-item" href="#" onclick="exportReport('pdf')">
                                    <i class="fas fa-file-pdf text-danger"></i> PDF
                                </a>
                                <a class="dropdown-item" href="#" onclick="exportReport('excel')">
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
                                                    $badgeClass = [
                                                        'Primeras' => 'success',
                                                        'Segunda' => 'warning',
                                                        'Tercera' => 'info',
                                                        'Cuarta' => 'primary',
                                                        'Industrial' => 'secondary'
                                                    ][$qualityName] ?? 'secondary';
                                                @endphp
                                                <span class="badge badge-{{ $badgeClass }} badge-lg">
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
                                                    $badgeClass = [
                                                        'Primeras' => 'success',
                                                        'Segunda' => 'warning',
                                                        'Tercera' => 'info',
                                                        'Cuarta' => 'primary',
                                                        'Industrial' => 'secondary'
                                                    ][$calidad] ?? 'secondary';
                                                @endphp
                                                <span class="badge badge-{{ $badgeClass }} badge-lg">
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
        $colors = [
            'Primeras' => '#28a745',
            'Segunda' => '#ffc107', 
            'Tercera' => '#17a2b8',
            'Cuarta' => '#007bff',
            'Industrial' => '#6c757d'
        ];
        
        $ingresosChartData = $reporte['resumen']->map(function($item) use ($colors) {
            $qualityName = $item->qualityGrade ? $item->qualityGrade->name : 'Sin calidad';
            return [
                'label' => $qualityName,
                'value' => $item->inversion_total,
                'color' => $colors[$qualityName] ?? '#6c757d'
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
                        'color' => $colors[$calidad] ?? '#6c757d'
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
@endif

function exportReport(format) {
    const params = new URLSearchParams(window.location.search);
    params.append('export', format);
    window.open(`{{ route('acopio.reporte') }}?${params}`, '_blank');
}
</script>
@endpush