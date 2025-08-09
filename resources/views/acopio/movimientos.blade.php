@extends('layouts.admin')

@section('title', 'Movimientos de Acopio')
@section('page-title', 'Movimientos de Inventario por Acopio')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('acopio.index') }}">Acopio</a></li>
    <li class="breadcrumb-item active">Movimientos</li>
@endsection

@section('content')
    <!-- Filtros -->
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-filter"></i> Filtros</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Calidad</label>
                                <select name="quality_grade" class="form-control">
                                    <option value="">Todas las calidades</option>
                                    @foreach(\App\Models\QualityGrade::where('active', true)->orderBy('name')->get() as $quality)
                                        <option value="{{ $quality->name }}" {{ request('quality_grade') == $quality->name ? 'selected' : '' }}>
                                            {{ $quality->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Fecha Desde</label>
                                <input type="date" name="date_from" value="{{ request('date_from', now()->subDays(90)->format('Y-m-d')) }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Fecha Hasta</label>
                                <input type="date" name="date_to" value="{{ request('date_to', now()->format('Y-m-d')) }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="d-block">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filtrar
                                    </button>
                                    <a href="{{ route('acopio.movimientos') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Limpiar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Movimientos -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exchange-alt"></i> 
                        Historial de Movimientos de Inventario
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportMovimientos()">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="movimientosTable" class="table table-striped table-hover">
                            <thead>
                                <tr class="bg-light">
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th>Calidad</th>
                                    <th>Cliente/Proveedor</th>
                                    <th>Peso (kg)</th>
                                    <th>Precio/kg</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables will populate this -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>

<script>
let movimientosTable;

$(document).ready(function() {
    console.log('Initializing DataTable...');
    
    // Check if table exists
    if ($('#movimientosTable').length === 0) {
        console.error('Table #movimientosTable not found!');
        return;
    }
    
    // Test with minimal configuration first
    console.log('Table found, proceeding with DataTable initialization...');
    
    // Initialize DataTables with minimal config
    try {
        movimientosTable = $('#movimientosTable').DataTable({
            serverSide: true,
            processing: false, // Disable processing indicator
            ajax: {
                url: '{{ route('acopio.movimientos') }}',
                type: 'GET',
                data: function (d) {
                    d.quality_grade = $('select[name="quality_grade"]').val() || '';
                    d.date_from = $('input[name="date_from"]').val() || '{{ now()->subDays(90)->format('Y-m-d') }}';
                    d.date_to = $('input[name="date_to"]').val() || '{{ now()->format('Y-m-d') }}';
                    return d;
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTables Ajax error:', error, thrown);
                }
            },
            columns: [
                {
                    data: 'fecha',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            const fecha = new Date(data);
                            const fechaFormateada = fecha.toLocaleDateString('es-ES');
                            return `
                                <div class="text-sm">
                                    <strong>${fechaFormateada}</strong>
                                    <br><small class="text-muted">${row.tipo === 'entrada' ? 'Ingreso' : 'Venta'}</small>
                                </div>
                            `;
                        }
                        return data;
                    }
                },
                {
                    data: 'tipo',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            const badgeClass = data === 'entrada' ? 'success' : 'danger';
                            const icon = data === 'entrada' ? 'fa-arrow-down' : 'fa-arrow-up';
                            const text = data === 'entrada' ? 'Ingreso' : 'Venta';
                            return `<span class="badge badge-${badgeClass}"><i class="fas ${icon}"></i> ${text}</span>`;
                        }
                        return data;
                    }
                },
                {
                    data: 'descripcion',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return `<div class="text-sm">${data}</div>`;
                        }
                        return data;
                    }
                },
                {
                    data: 'calidad',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            const badgeClasses = {
                                'Primeras': 'success',
                                'Segunda': 'warning', 
                                'Tercera': 'info',
                                'Cuarta': 'primary',
                                'Industrial': 'secondary'
                            };
                            const badgeClass = badgeClasses[data] || 'secondary';
                            return `<span class="badge badge-${badgeClass}">${data}</span>`;
                        }
                        return data;
                    }
                },
                {
                    data: 'referencia',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return `<div class="text-sm font-weight-bold">${data}</div>`;
                        }
                        return data;
                    }
                },
                {
                    data: 'peso',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return `<strong>${parseFloat(data).toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2})} kg</strong>`;
                        }
                        return data;
                    }
                },
                {
                    data: 'costo_unitario',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return `<span class="text-muted">$${parseFloat(data).toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>`;
                        }
                        return data;
                    }
                },
                {
                    data: 'valor_total',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            const colorClass = row.tipo === 'entrada' ? 'text-danger' : 'text-success';
                            return `<strong class="${colorClass}">$${parseFloat(data).toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong>`;
                        }
                        return data;
                    }
                }
            ],
            pageLength: 25,
            order: [[0, 'desc']],
            responsive: true,
            language: {
                processing: "Procesando...",
                search: "Buscar:",
                lengthMenu: "Mostrar _MENU_ registros",
                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                infoEmpty: "Mostrando 0 a 0 de 0 registros",
                paginate: {
                    previous: "Anterior", 
                    next: "Siguiente"
                }
            },
            initComplete: function() {
                console.log('DataTable initialized successfully');
            }
        });
        
        console.log('DataTable object created:', movimientosTable);
        
    } catch(error) {
        console.error('Error initializing DataTable:', error);
    }

    // Apply filters when changed
    $('select[name="quality_grade"], input[name="date_from"], input[name="date_to"]').on('change', function() {
        console.log('Filter changed, redrawing table...');
        movimientosTable.draw();
    });

    // Filter form submission
    $('form').on('submit', function(e) {
        e.preventDefault();
        console.log('Form submitted, redrawing table...');
        movimientosTable.draw();
    });
    
    console.log('DataTable initialized successfully!');
});

function exportMovimientos() {
    const params = new URLSearchParams();
    params.append('quality_grade', $('select[name="quality_grade"]').val());
    params.append('date_from', $('input[name="date_from"]').val());
    params.append('date_to', $('input[name="date_to"]').val());
    params.append('export', 'excel');
    
    window.open(`{{ route('acopio.movimientos') }}?${params}`, '_blank');
}
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/es.min.js"></script>
@endpush