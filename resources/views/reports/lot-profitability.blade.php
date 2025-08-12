@extends('layouts.admin')

@section('title', 'Reporte de Rentabilidad por Lotes')
@section('page-title', 'Reporte de Rentabilidad por Lotes')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="#">Reportes</a></li>
    <li class="breadcrumb-item active">Rentabilidad por Lotes</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Filtros -->
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtros del Reporte</h3>
        </div>
        <div class="card-body">
            <form id="filterForm" class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Fecha Inicio</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" 
                               value="{{ $startDate->format('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Fecha Fin</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" 
                               value="{{ $endDate->format('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Buscar Lote</label>
                        <input type="text" id="lot_search" name="lot_search" class="form-control" 
                               placeholder="Código de lote...">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Filtrar por Calidad</label>
                        <select id="quality_filter" name="quality_filter" class="form-control">
                            <option value="">Todas las calidades</option>
                            @foreach($qualityGrades as $quality)
                                <option value="{{ $quality->name }}" style="color: {{ $quality->color }};">
                                    {{ $quality->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="btn-group-vertical btn-block">
                            <button type="button" onclick="loadReport()" class="btn btn-primary">
                                <i class="fas fa-search"></i> Generar Reporte
                            </button>
                            <button type="button" onclick="clearFilters()" class="btn btn-secondary btn-sm mt-1">
                                <i class="fas fa-eraser"></i> Limpiar Filtros
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Loading indicator -->
    <div id="loadingIndicator" class="text-center py-5" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Cargando...</span>
        </div>
        <p class="mt-2">Generando reporte...</p>
    </div>

    <!-- Contenido del reporte -->
    <div id="reportContent">
        <!-- Métricas Generales -->
        <div class="row" id="metricsRow">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3 id="totalRevenue">${{ number_format($generalMetrics['total_revenue'], 2) }}</h3>
                        <p>Ingresos Totales</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 id="totalCost">${{ number_format($generalMetrics['total_cost'], 2) }}</h3>
                        <p>Costo Total</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-money-bill-alt"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3 id="totalProfit">${{ number_format($generalMetrics['total_profit'], 2) }}</h3>
                        <p>Utilidad Total</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3 id="profitMargin">{{ number_format($generalMetrics['profit_margin'], 1) }}%</h3>
                        <p>Margen de Utilidad</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficas -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-pie"></i> Utilidad por Calidad</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="profitByQualityChart" style="min-height: 250px; height: 250px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-bar"></i> Top 10 Lotes Más Rentables</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="topLotsChart" style="min-height: 250px; height: 250px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla Simplificada con DataTable -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-boxes"></i> Rentabilidad Detallada por Lote
                </h3>
                <div class="card-tools">
                    <span class="badge badge-primary" id="lotsCount">{{ count($lotProfitability) }} Lotes</span>
                    <span class="badge badge-info" id="totalWeight">{{ number_format($generalMetrics['total_weight'], 2) }} kg</span>
                </div>
            </div>
            <div class="card-body">
                <table id="lotProfitabilityTable" class="table table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Lote</th>
                            <th>Proveedor</th>
                            <th>Calidad</th>
                            <th>Peso Vendido</th>
                            <th>Utilidad</th>
                            <th>Margen</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="lotTableBody">
                        @foreach($lotProfitability as $lot)
                            <tr>
                                <td><strong>{{ $lot['lot_code'] }}</strong></td>
                                <td>{{ Str::limit($lot['supplier'], 30) }}</td>
                                <td>
                                    <span class="badge" style="background-color: {{ $lot['quality_color'] }}; color: white;">
                                        {{ $lot['quality'] }}
                                    </span>
                                </td>
                                <td data-order="{{ $lot['total_weight_sold'] }}">
                                    {{ number_format($lot['total_weight_sold'], 2) }} kg
                                </td>
                                <td data-order="{{ $lot['total_profit'] }}">
                                    @if($lot['total_profit'] >= 0)
                                        <span class="text-success">
                                            <strong>${{ number_format($lot['total_profit'], 2) }}</strong>
                                        </span>
                                    @else
                                        <span class="text-danger">
                                            <strong>-${{ number_format(abs($lot['total_profit']), 2) }}</strong>
                                        </span>
                                    @endif
                                </td>
                                <td data-order="{{ $lot['avg_margin'] }}">
                                    @if($lot['avg_margin'] >= 30)
                                        <span class="badge badge-success">
                                    @elseif($lot['avg_margin'] >= 20)
                                        <span class="badge badge-warning">
                                    @else
                                        <span class="badge badge-danger">
                                    @endif
                                        {{ number_format($lot['avg_margin'], 1) }}%
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-info btn-block btn-sm d-none d-md-inline-block" data-lot-code="{{ $lot['lot_code'] }}" onclick="showLotDetailsFromButton(this)">
                                        <i class="fas fa-eye"></i> Ver Detalle
                                    </button>
                                    <button class="btn btn-info d-md-none" data-lot-code="{{ $lot['lot_code'] }}" onclick="showLotDetailsFromButton(this)">
                                        <i class="fas fa-eye"></i> Ver
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-right">TOTALES:</th>
                            <th id="footerWeight"></th>
                            <th id="footerProfit"></th>
                            <th id="footerMargin"></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Resúmenes -->
        <div class="row">
            <!-- Por Calidad -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-layer-group"></i> Resumen por Calidad</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-sm">
                                <thead class="sticky-top bg-white">
                                    <tr>
                                        <th>Calidad</th>
                                        <th class="text-right">Utilidad</th>
                                        <th class="text-right">Margen</th>
                                    </tr>
                                </thead>
                                <tbody id="summaryByQuality">
                                    @foreach($summaryByQuality as $summary)
                                    <tr>
                                        <td>
                                            <span class="badge" style="background-color: {{ $summary['color'] }}; color: white;">
                                                {{ $summary['quality'] }}
                                            </span>
                                            <small class="text-muted d-block">
                                                {{ $summary['lot_count'] }} lotes | {{ $summary['sale_count'] }} ventas
                                            </small>
                                        </td>
                                        <td class="text-right">
                                            @if($summary['total_profit'] >= 0)
                                                <span class="text-success">${{ number_format($summary['total_profit'], 2) }}</span>
                                            @else
                                                <span class="text-danger">-${{ number_format(abs($summary['total_profit']), 2) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <span class="badge badge-{{ $summary['margin'] >= 30 ? 'success' : ($summary['margin'] >= 20 ? 'warning' : 'danger') }}">
                                                {{ number_format($summary['margin'], 1) }}%
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Por Proveedor -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-truck"></i> Resumen por Proveedor</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-sm">
                                <thead class="sticky-top bg-white">
                                    <tr>
                                        <th>Proveedor</th>
                                        <th class="text-right">Utilidad</th>
                                        <th class="text-right">Margen</th>
                                    </tr>
                                </thead>
                                <tbody id="summaryBySupplier">
                                    @foreach(array_slice($summaryBySupplier, 0, 10) as $summary)
                                    <tr>
                                        <td>
                                            {{ Str::limit($summary['supplier'], 25) }}
                                            <small class="text-muted d-block">
                                                {{ $summary['lot_count'] }} lotes | {{ number_format($summary['total_weight'], 0) }} kg
                                            </small>
                                        </td>
                                        <td class="text-right">
                                            @if($summary['total_profit'] >= 0)
                                                <span class="text-success">${{ number_format($summary['total_profit'], 2) }}</span>
                                            @else
                                                <span class="text-danger">-${{ number_format(abs($summary['total_profit']), 2) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <span class="badge badge-{{ $summary['margin'] >= 30 ? 'success' : ($summary['margin'] >= 20 ? 'warning' : 'danger') }}">
                                                {{ number_format($summary['margin'], 1) }}%
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Por Cliente -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-users"></i> Resumen por Cliente</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-sm">
                                <thead class="sticky-top bg-white">
                                    <tr>
                                        <th>Cliente</th>
                                        <th class="text-right">Utilidad</th>
                                        <th class="text-right">Margen</th>
                                    </tr>
                                </thead>
                                <tbody id="summaryByCustomer">
                                    @foreach(array_slice($summaryByCustomer, 0, 10) as $summary)
                                    <tr>
                                        <td>
                                            {{ Str::limit($summary['customer'], 25) }}
                                            <small class="text-muted d-block">
                                                {{ $summary['purchase_count'] }} compras | {{ number_format($summary['total_weight'], 0) }} kg
                                            </small>
                                        </td>
                                        <td class="text-right">
                                            @if($summary['total_profit'] >= 0)
                                                <span class="text-success">${{ number_format($summary['total_profit'], 2) }}</span>
                                            @else
                                                <span class="text-danger">-${{ number_format(abs($summary['total_profit']), 2) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <span class="badge badge-{{ $summary['margin'] >= 30 ? 'success' : ($summary['margin'] >= 20 ? 'warning' : 'danger') }}">
                                                {{ number_format($summary['margin'], 1) }}%
                                            </span>
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

<!-- Modal de Detalles del Lote -->
<div class="modal fade" id="lotDetailsModal" tabindex="-1" role="dialog" aria-labelledby="lotDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title" id="lotDetailsModalLabel">Información Completa del Lote</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="lotDetailsContent"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let lotTable;
let profitByQualityChart;
let topLotsChart;
let currentData = @json($lotProfitability);

$(document).ready(function() {
    // Debug de Bootstrap y modal
    console.log('Bootstrap loaded:', typeof Bootstrap !== 'undefined');
    console.log('jQuery modal function:', typeof $.fn.modal);
    console.log('Modal element on load:', $('#lotDetailsModal').length);
    
    // Evento para debug del modal
    $('#lotDetailsModal').on('show.bs.modal', function() {
        console.log('Modal está por mostrarse');
    });
    
    $('#lotDetailsModal').on('shown.bs.modal', function() {
        console.log('Modal se mostró correctamente');
    });
    
    // Inicializar DataTable 
    initDataTable();
    
    // Inicializar gráficas
    initCharts();
});

function initCharts() {
    // Datos iniciales para las gráficas
    var summaryByQuality = @json($summaryByQuality);
    var lotProfitability = @json($lotProfitability);
    
    // Gráfica de Utilidad por Calidad
    var ctx1 = document.getElementById('profitByQualityChart').getContext('2d');
    profitByQualityChart = new Chart(ctx1, {
        type: 'pie',
        data: {
            labels: summaryByQuality.map(item => item.quality),
            datasets: [{
                data: summaryByQuality.map(item => Math.abs(item.total_profit)),
                backgroundColor: summaryByQuality.map(item => item.color || '#6c757d'),
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
                            var profit = summaryByQuality[context.dataIndex].total_profit;
                            var sign = profit >= 0 ? '' : '-';
                            return context.label + ': ' + sign + '$' + Math.abs(profit).toFixed(2);
                        }
                    }
                }
            }
        }
    });
    
    // Gráfica de Top 10 Lotes
    var topLots = lotProfitability.slice(0, 10);
    var ctx2 = document.getElementById('topLotsChart').getContext('2d');
    topLotsChart = new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: topLots.map(item => item.lot_code),
            datasets: [{
                label: 'Utilidad',
                data: topLots.map(item => item.total_profit),
                backgroundColor: topLots.map(item => item.total_profit >= 0 ? 'rgba(40, 167, 69, 0.8)' : 'rgba(220, 53, 69, 0.8)'),
                borderColor: topLots.map(item => item.total_profit >= 0 ? 'rgb(40, 167, 69)' : 'rgb(220, 53, 69)'),
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
                            return '$' + value.toFixed(0);
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            var lot = topLots[context.dataIndex];
                            return [
                                'Utilidad: $' + lot.total_profit.toFixed(2),
                                'Margen: ' + lot.avg_margin.toFixed(1) + '%',
                                'Calidad: ' + lot.quality
                            ];
                        }
                    }
                }
            }
        }
    });
}

function loadReport() {
    // Validar fechas
    var startDate = $('#start_date').val();
    var endDate = $('#end_date').val();
    
    if (!startDate || !endDate) {
        toastr.error('Por favor seleccione ambas fechas');
        return;
    }
    
    // Mostrar indicador de carga
    $('#loadingIndicator').show();
    $('#reportContent').css('opacity', '0.5');
    
    // Obtener valores de los filtros
    var lotSearch = $('#lot_search').val();
    var qualityFilter = $('#quality_filter').val();
    
    // Hacer petición AJAX con timeout mayor
    $.ajax({
        url: '{{ route('reports.lot-profitability') }}',
        type: 'GET',
        timeout: 30000, // 30 segundos de timeout
        data: {
            start_date: startDate,
            end_date: endDate,
            lot_search: lotSearch,
            quality_filter: qualityFilter
        },
        success: function(response) {
            try {
                // Actualizar datos globales
                currentData = response.lotProfitability;
                
                // Actualizar métricas
                $('#totalRevenue').text('$' + parseFloat(response.generalMetrics.total_revenue).toFixed(2));
                $('#totalCost').text('$' + parseFloat(response.generalMetrics.total_cost).toFixed(2));
                $('#totalProfit').text('$' + parseFloat(response.generalMetrics.total_profit).toFixed(2));
                $('#profitMargin').text(parseFloat(response.generalMetrics.profit_margin).toFixed(1) + '%');
                $('#lotsCount').text(response.generalMetrics.lots_sold + ' Lotes');
                $('#totalWeight').text(parseFloat(response.generalMetrics.total_weight).toFixed(2) + ' kg');
                
                // Actualizar tabla
                updateTable(response.lotProfitability);
                
                // Actualizar resúmenes
                updateSummaries(response);
                
                // Actualizar gráficas
                updateCharts(response);
                
                // Mostrar notificación de éxito
                toastr.success('Reporte actualizado correctamente');
            } catch(e) {
                console.error('Error procesando respuesta:', e);
                toastr.error('Error al procesar los datos del reporte');
            } finally {
                // Ocultar indicador de carga
                $('#loadingIndicator').hide();
                $('#reportContent').css('opacity', '1');
            }
        },
        error: function(xhr, status, error) {
            $('#loadingIndicator').hide();
            $('#reportContent').css('opacity', '1');
            
            if (status === 'timeout') {
                toastr.error('La solicitud tardó demasiado tiempo. Por favor intente con un rango de fechas menor.');
            } else {
                toastr.error('Error al cargar el reporte: ' + error);
            }
            console.error('Error AJAX:', status, error);
        }
    });
}

function updateTable(lotData) {
    // Destruir la tabla existente y recrearla para evitar problemas
    lotTable.destroy();
    
    // Limpiar el HTML de la tabla
    $('#lotProfitabilityTable tbody').empty();
    
    // Agregar las filas directamente al HTML
    var tableHtml = '';
    lotData.forEach(function(lot) {
        var profitHtml = lot.total_profit >= 0 
            ? '<span class="text-success"><strong>$' + lot.total_profit.toFixed(2) + '</strong></span>'
            : '<span class="text-danger"><strong>-$' + Math.abs(lot.total_profit).toFixed(2) + '</strong></span>';
        
        var marginClass = lot.avg_margin >= 30 ? 'success' : (lot.avg_margin >= 20 ? 'warning' : 'danger');
        var marginHtml = '<span class="badge badge-' + marginClass + '">' + lot.avg_margin.toFixed(1) + '%</span>';
        
        tableHtml += '<tr>';
        tableHtml += '<td><strong>' + lot.lot_code + '</strong></td>';
        tableHtml += '<td>' + lot.supplier.substring(0, 30) + (lot.supplier.length > 30 ? '...' : '') + '</td>';
        tableHtml += '<td><span class="badge" style="background-color: ' + lot.quality_color + '; color: white;">' + lot.quality + '</span></td>';
        tableHtml += '<td data-order="' + lot.total_weight_sold + '">' + lot.total_weight_sold.toFixed(2) + ' kg</td>';
        tableHtml += '<td data-order="' + lot.total_profit + '">' + profitHtml + '</td>';
        tableHtml += '<td data-order="' + lot.avg_margin + '">' + marginHtml + '</td>';
        tableHtml += '<td><button class="btn btn-info btn-block btn-sm d-none d-md-inline-block" data-lot-code="' + lot.lot_code + '" onclick="showLotDetailsFromButton(this)"><i class="fas fa-eye"></i> Ver Detalle</button><button class="btn btn-info d-md-none" data-lot-code="' + lot.lot_code + '" onclick="showLotDetailsFromButton(this)"><i class="fas fa-eye"></i> Ver</button></td>';
        tableHtml += '</tr>';
    });
    
    $('#lotProfitabilityTable tbody').html(tableHtml);
    
    // Reinicializar DataTable
    initDataTable();
}

function initDataTable() {
    lotTable = $('#lotProfitabilityTable').DataTable({
        responsive: true,
        order: [[4, 'desc']], // Ordenar por utilidad descendente
        pageLength: 25,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        columnDefs: [
            { className: "text-center", targets: [3, 5] },
            { orderable: false, targets: [6] },
            { 
                targets: [3, 4, 5], // Columnas con data-order
                type: 'num',
                render: function(data, type, row, meta) {
                    if (type === 'type' || type === 'sort') {
                        // Extraer valor numérico del data-order para ordenamiento
                        var $temp = $('<div>').html(data);
                        var orderValue = $temp.find('[data-order]').attr('data-order');
                        return orderValue ? parseFloat(orderValue) : 0;
                    }
                    return data;
                }
            }
        ],
        footerCallback: function(row, data, start, end, display) {
            var api = this.api();
            
            // Calcular totales usando los datos mostrados
            var totalWeight = 0;
            var totalProfit = 0;
            var totalRevenue = 0;
            
            // Usar los datos de currentData para calcular correctamente
            api.rows({page: 'current'}).every(function(rowIdx, tableLoop, rowLoop) {
                var rowData = this.data();
                var lotCode = $(rowData[0]).text(); // Extraer código del lote del HTML
                var lotData = currentData.find(l => l && l.lot_code === lotCode);
                if (lotData) {
                    totalWeight += parseFloat(lotData.total_weight_sold) || 0;
                    totalProfit += parseFloat(lotData.total_profit) || 0;
                    totalRevenue += parseFloat(lotData.total_revenue) || 0;
                }
            });
            
            var margin = totalRevenue > 0 ? (totalProfit / totalRevenue) * 100 : 0;
            
            // Actualizar footer
            $(api.column(3).footer()).html(totalWeight.toFixed(2) + ' kg');
            $(api.column(4).footer()).html(
                totalProfit >= 0 
                    ? '<span class="text-success"><strong>$' + totalProfit.toFixed(2) + '</strong></span>'
                    : '<span class="text-danger"><strong>-$' + Math.abs(totalProfit).toFixed(2) + '</strong></span>'
            );
            $(api.column(5).footer()).html('<span class="badge badge-primary">' + margin.toFixed(1) + '%</span>');
        }
    });
}

function showLotDetailsFromButton(button) {
    var lotCode = button.getAttribute('data-lot-code');
    console.log('Buscando lote:', lotCode, 'en datos:', currentData);
    var lot = currentData.find(l => l && l.lot_code === lotCode);
    if (lot) {
        console.log('Lote encontrado:', lot);
        showLotDetails(lot);
    } else {
        console.error('No se encontró el lote:', lotCode, 'en', currentData);
        toastr.error('No se pudo encontrar la información del lote: ' + lotCode);
    }
}

function clearFilters() {
    // Limpiar todos los filtros
    $('#lot_search').val('');
    $('#quality_filter').val('');
    
    // Restablecer fechas al valor por defecto (última semana)
    var lastWeek = new Date();
    lastWeek.setDate(lastWeek.getDate() - 7);
    var today = new Date();
    
    $('#start_date').val(lastWeek.toISOString().split('T')[0]);
    $('#end_date').val(today.toISOString().split('T')[0]);
    
    // Recargar el reporte con filtros limpos
    loadReport();
    
    toastr.info('Filtros limpiados correctamente');
}

function updateSummaries(response) {
    // Actualizar resumen por calidad
    var qualityHtml = '';
    response.summaryByQuality.forEach(function(item) {
        qualityHtml += '<tr><td><span class="badge" style="background-color: ' + item.color + '; color: white;">' + item.quality + '</span>';
        qualityHtml += '<small class="text-muted d-block">' + item.lot_count + ' lotes | ' + item.sale_count + ' ventas</small></td>';
        qualityHtml += '<td class="text-right">' + (item.total_profit >= 0 ? '<span class="text-success">$' : '<span class="text-danger">-$') + Math.abs(item.total_profit).toFixed(2) + '</span></td>';
        var marginClass = item.margin >= 30 ? 'success' : (item.margin >= 20 ? 'warning' : 'danger');
        qualityHtml += '<td class="text-right"><span class="badge badge-' + marginClass + '">' + item.margin.toFixed(1) + '%</span></td></tr>';
    });
    $('#summaryByQuality').html(qualityHtml);
    
    // Actualizar resumen por proveedor
    var supplierHtml = '';
    response.summaryBySupplier.slice(0, 10).forEach(function(item) {
        supplierHtml += '<tr><td>' + item.supplier.substring(0, 25) + (item.supplier.length > 25 ? '...' : '');
        supplierHtml += '<small class="text-muted d-block">' + item.lot_count + ' lotes | ' + item.total_weight.toFixed(0) + ' kg</small></td>';
        supplierHtml += '<td class="text-right">' + (item.total_profit >= 0 ? '<span class="text-success">$' : '<span class="text-danger">-$') + Math.abs(item.total_profit).toFixed(2) + '</span></td>';
        var marginClass = item.margin >= 30 ? 'success' : (item.margin >= 20 ? 'warning' : 'danger');
        supplierHtml += '<td class="text-right"><span class="badge badge-' + marginClass + '">' + item.margin.toFixed(1) + '%</span></td></tr>';
    });
    $('#summaryBySupplier').html(supplierHtml);
    
    // Actualizar resumen por cliente
    var customerHtml = '';
    response.summaryByCustomer.slice(0, 10).forEach(function(item) {
        customerHtml += '<tr><td>' + item.customer.substring(0, 25) + (item.customer.length > 25 ? '...' : '');
        customerHtml += '<small class="text-muted d-block">' + item.purchase_count + ' compras | ' + item.total_weight.toFixed(0) + ' kg</small></td>';
        customerHtml += '<td class="text-right">' + (item.total_profit >= 0 ? '<span class="text-success">$' : '<span class="text-danger">-$') + Math.abs(item.total_profit).toFixed(2) + '</span></td>';
        var marginClass = item.margin >= 30 ? 'success' : (item.margin >= 20 ? 'warning' : 'danger');
        customerHtml += '<td class="text-right"><span class="badge badge-' + marginClass + '">' + item.margin.toFixed(1) + '%</span></td></tr>';
    });
    $('#summaryByCustomer').html(customerHtml);
}

function updateCharts(response) {
    // Actualizar gráfica de utilidad por calidad
    profitByQualityChart.data.labels = response.summaryByQuality.map(item => item.quality);
    profitByQualityChart.data.datasets[0].data = response.summaryByQuality.map(item => Math.abs(item.total_profit));
    profitByQualityChart.data.datasets[0].backgroundColor = response.summaryByQuality.map(item => item.color || '#6c757d');
    profitByQualityChart.update();
    
    // Actualizar gráfica de top lotes
    var topLots = response.lotProfitability.slice(0, 10);
    topLotsChart.data.labels = topLots.map(item => item.lot_code);
    topLotsChart.data.datasets[0].data = topLots.map(item => item.total_profit);
    topLotsChart.data.datasets[0].backgroundColor = topLots.map(item => item.total_profit >= 0 ? 'rgba(40, 167, 69, 0.8)' : 'rgba(220, 53, 69, 0.8)');
    topLotsChart.data.datasets[0].borderColor = topLots.map(item => item.total_profit >= 0 ? 'rgb(40, 167, 69)' : 'rgb(220, 53, 69)');
    topLotsChart.update();
}

function showLotDetails(lot) {
    console.log('Mostrando detalles del lote:', lot);
    
    try {
        console.log('Iniciando construcción del HTML...');
        
        // Función helper para valores seguros
        const safe = (value, defaultValue = 'N/A') => (value !== undefined && value !== null) ? value : defaultValue;
        const safeNum = (value, defaultValue = 0) => (value !== undefined && value !== null && !isNaN(value)) ? parseFloat(value) : defaultValue;
        
        let html = `
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-box"></i> Información del Lote</h6>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Código:</dt>
                            <dd class="col-sm-7"><strong>${safe(lot.lot_code)}</strong></dd>
                            
                            <dt class="col-sm-5">Proveedor:</dt>
                            <dd class="col-sm-7">${safe(lot.supplier)}</dd>
                            
                            <dt class="col-sm-5">Calidad:</dt>
                            <dd class="col-sm-7">
                                <span class="badge" style="background-color: ${safe(lot.quality_color, '#6c757d')}; color: white;">
                                    ${safe(lot.quality)}
                                </span>
                            </dd>
                            
                            <dt class="col-sm-5">Fecha Entrada:</dt>
                            <dd class="col-sm-7">${safe(lot.entry_date)}</dd>
                            
                            <dt class="col-sm-5">Peso Total:</dt>
                            <dd class="col-sm-7">${safeNum(lot.total_weight).toFixed(2)} kg</dd>
                            
                            <dt class="col-sm-5">Peso Vendido:</dt>
                            <dd class="col-sm-7">${safeNum(lot.total_weight_sold).toFixed(2)} kg</dd>
                            
                            <dt class="col-sm-5">Peso Disponible:</dt>
                            <dd class="col-sm-7">${(safeNum(lot.total_weight) - safeNum(lot.total_weight_sold)).toFixed(2)} kg</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-chart-line"></i> Resumen Financiero</h6>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-5">Precio Compra:</dt>
                            <dd class="col-sm-7">$${safeNum(lot.purchase_price).toFixed(2)} / kg</dd>
                            
                            <dt class="col-sm-5">Precio Venta Prom:</dt>
                            <dd class="col-sm-7">$${(safeNum(lot.total_revenue) / Math.max(safeNum(lot.total_weight_sold), 1)).toFixed(2)} / kg</dd>
                            
                            <dt class="col-sm-5">Total Ingresos:</dt>
                            <dd class="col-sm-7 text-info"><strong>$${safeNum(lot.total_revenue).toFixed(2)}</strong></dd>
                            
                            <dt class="col-sm-5">Total Costo:</dt>
                            <dd class="col-sm-7 text-warning">$${safeNum(lot.total_cost).toFixed(2)}</dd>
                            
                            <dt class="col-sm-5">Utilidad Total:</dt>
                            <dd class="col-sm-7 ${safeNum(lot.total_profit) >= 0 ? 'text-success' : 'text-danger'}">
                                <strong>${safeNum(lot.total_profit) >= 0 ? '' : '-'}$${Math.abs(safeNum(lot.total_profit)).toFixed(2)}</strong>
                            </dd>
                            
                            <dt class="col-sm-5">Margen:</dt>
                            <dd class="col-sm-7">
                                <span class="badge badge-${safeNum(lot.avg_margin) >= 30 ? 'success' : (safeNum(lot.avg_margin) >= 20 ? 'warning' : 'danger')}">
                                    ${safeNum(lot.avg_margin).toFixed(1)}%
                                </span>
                            </dd>
                            
                            <dt class="col-sm-5">Total Ventas:</dt>
                            <dd class="col-sm-7"><span class="badge badge-info">${safeNum(lot.sales_count)} ventas</span></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-shopping-cart"></i> Detalle de Ventas</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Código Venta</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th class="text-right">Peso (kg)</th>
                                <th class="text-right">Precio Venta</th>
                                <th class="text-right">Ingresos</th>
                                <th class="text-right">Costo</th>
                                <th class="text-right">Utilidad</th>
                                <th class="text-center">Margen</th>
                            </tr>
                        </thead>
                        <tbody>`;
    
        // Agregar filas de ventas si existen
        if (lot.sales && Array.isArray(lot.sales)) {
            lot.sales.forEach(sale => {
                const profit = safeNum(sale.profit);
                const margin = safeNum(sale.margin);
                const profitClass = profit >= 0 ? 'text-success' : 'text-danger';
                const marginClass = margin >= 30 ? 'success' : (margin >= 20 ? 'warning' : 'danger');
                
                html += `
                    <tr>
                        <td><strong>${safe(sale.sale_code)}</strong></td>
                        <td>${safe(sale.sale_date)}</td>
                        <td>${safe(sale.customer)}</td>
                        <td class="text-right">${safeNum(sale.weight_sold).toFixed(2)}</td>
                        <td class="text-right">$${safeNum(sale.sale_price).toFixed(2)}</td>
                        <td class="text-right text-info">$${safeNum(sale.revenue).toFixed(2)}</td>
                        <td class="text-right text-warning">$${safeNum(sale.cost).toFixed(2)}</td>
                        <td class="text-right ${profitClass}">
                            <strong>${profit >= 0 ? '' : '-'}$${Math.abs(profit).toFixed(2)}</strong>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-${marginClass}">${margin.toFixed(1)}%</span>
                        </td>
                    </tr>`;
            });
        } else {
            html += `
                <tr>
                    <td colspan="9" class="text-center text-muted">No hay ventas registradas para este lote</td>
                </tr>`;
        }
        
        html += `
                        </tbody>
                        <tfoot class="bg-light font-weight-bold">
                            <tr>
                                <td colspan="3" class="text-right">TOTALES:</td>
                                <td class="text-right">${safeNum(lot.total_weight_sold).toFixed(2)} kg</td>
                                <td></td>
                                <td class="text-right text-info">$${safeNum(lot.total_revenue).toFixed(2)}</td>
                                <td class="text-right text-warning">$${safeNum(lot.total_cost).toFixed(2)}</td>
                                <td class="text-right ${safeNum(lot.total_profit) >= 0 ? 'text-success' : 'text-danger'}">
                                    ${safeNum(lot.total_profit) >= 0 ? '' : '-'}$${Math.abs(safeNum(lot.total_profit)).toFixed(2)}
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-primary">${safeNum(lot.avg_margin).toFixed(1)}%</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>`;
        
    
        console.log('HTML construido correctamente');
        
        // Insertar el contenido
        document.getElementById('lotDetailsContent').innerHTML = html;
        console.log('Contenido insertado en el modal');
        
        // Debug del modal
        console.log('Modal element exists:', $('#lotDetailsModal').length > 0);
        console.log('Bootstrap version:', typeof $.fn.modal);
        
        // Intentar mostrar el modal con diferentes métodos
        try {
            $('#lotDetailsModal').modal('show');
            console.log('Modal show ejecutado con jQuery');
        } catch (e) {
            console.error('Error con jQuery modal:', e);
            // Intentar con JavaScript puro
            const modalElement = document.getElementById('lotDetailsModal');
            if (modalElement) {
                modalElement.style.display = 'block';
                modalElement.classList.add('show');
                document.body.classList.add('modal-open');
                console.log('Modal abierto con JavaScript puro');
            }
        }
        
    } catch (error) {
        console.error('Error en showLotDetails:', error);
        toastr.error('Error al mostrar los detalles del lote');
    }
}
</script>
@endpush