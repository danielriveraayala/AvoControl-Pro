@extends('layouts.admin')

@section('title', 'Lotes')
@section('page-title', 'Gestión de Lotes')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Lotes</li>
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
                    <form id="filterForm" class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Estado</label>
                                <select name="status" class="form-control" onchange="loadLots()">
                                    <option value="">Todos</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activo</option>
                                    <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Parcial</option>
                                    <option value="sold" {{ request('status') == 'sold' ? 'selected' : '' }}>Vendido</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Calidad</label>
                                <select name="quality" class="form-control" onchange="loadLots()">
                                    <option value="">Todas</option>
                                    <option value="Primera" {{ request('quality') == 'Primera' ? 'selected' : '' }}>Primera</option>
                                    <option value="Segunda" {{ request('quality') == 'Segunda' ? 'selected' : '' }}>Segunda</option>
                                    <option value="Tercera" {{ request('quality') == 'Tercera' ? 'selected' : '' }}>Tercera</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Proveedor</label>
                                <select name="supplier_id" class="form-control" onchange="loadLots()">
                                    <option value="">Todos</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Desde</label>
                                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" onchange="loadLots()">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Hasta</label>
                                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" onchange="loadLots()">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="d-block">
                                    <a href="{{ route('lots.create') }}" class="btn btn-success btn-ajax">
                                        <i class="fas fa-plus"></i> Nuevo Lote
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="totalLots">{{ $lots->total() }}</h3>
                    <p>Total Lotes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="activeLots">{{ $lots->where('status', 'active')->count() }}</h3>
                    <p>Lotes Activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 id="totalWeight">{{ number_format($lots->sum('weight_available'), 0) }} kg</h3>
                    <p>Peso Disponible</p>
                </div>
                <div class="icon">
                    <i class="fas fa-weight"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="totalValue">${{ number_format($lots->sum('total_purchase_cost'), 0) }}</h3>
                    <p>Valor Total</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Lotes -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list"></i> Lista de Lotes</h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-download"></i> Exportar
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" onclick="exportData('excel')"><i class="fas fa-file-excel"></i> Excel</a>
                                <a class="dropdown-item" href="#" onclick="exportData('pdf')"><i class="fas fa-file-pdf"></i> PDF</a>
                            </div>
                        </div>
                        <button type="button" class="btn btn-tool" data-card-widget="maximize">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <div id="lotsTableContainer">
                            <div class="text-center p-4">
                                <div class="spinner-border" role="status">
                                    <span class="sr-only">Cargando...</span>
                                </div>
                                <p class="mt-2">Cargando lotes...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Quick Actions -->
    <div class="modal fade" id="quickActionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="quickActionTitle">Acción Rápida</h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="quickActionBody">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Load initial data
    loadLots();
    
    // Auto-refresh every 30 seconds
    setInterval(loadLots, 30000);
});

function loadLots(page = 1) {
    const formData = new FormData($('#filterForm')[0]);
    formData.append('page', page);
    formData.append('ajax', '1');
    
    const params = new URLSearchParams(formData);
    
    // Show loading state
    $('#lotsTableContainer').html(`
        <div class="text-center p-4">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
            <p class="mt-2 text-muted">Actualizando datos...</p>
        </div>
    `);
    
    fetch(`{{ route('lots.index') }}?${params}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.html) {
            $('#lotsTableContainer').html(data.html);
            
            // Update statistics
            if (data.stats) {
                $('#totalLots').text(data.stats.total);
                $('#activeLots').text(data.stats.active);
                $('#totalWeight').text(data.stats.weight + ' kg');
                $('#totalValue').text('$' + data.stats.value);
            }
            
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
            
            // Update URL without page reload
            const url = new URL(window.location);
            params.delete('ajax');
            params.delete('page');
            for (let [key, value] of params) {
                if (value) {
                    url.searchParams.set(key, value);
                } else {
                    url.searchParams.delete(key);
                }
            }
            history.pushState({}, '', url);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        $('#lotsTableContainer').html(`
            <div class="alert alert-danger">
                <h5><i class="icon fas fa-ban"></i> Error!</h5>
                Ha ocurrido un error al cargar los datos. Por favor, intente de nuevo.
                <button class="btn btn-sm btn-outline-danger ml-2" onclick="loadLots()">
                    <i class="fas fa-redo"></i> Reintentar
                </button>
            </div>
        `);
    });
}

// Handle AJAX links
$(document).on('click', '.btn-ajax', function(e) {
    e.preventDefault();
    const url = $(this).attr('href');
    const title = $(this).text().trim();
    
    $('#quickActionTitle').text(title);
    $('#quickActionBody').html(`
        <div class="text-center p-4">
            <div class="spinner-border" role="status"></div>
            <p class="mt-2">Cargando formulario...</p>
        </div>
    `);
    
    $('#quickActionModal').modal('show');
    
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        $('#quickActionBody').html(html);
    })
    .catch(error => {
        $('#quickActionBody').html(`
            <div class="alert alert-danger">
                Error al cargar el formulario. <a href="${url}" target="_blank">Abrir en nueva pestaña</a>
            </div>
        `);
    });
});

// Handle pagination
$(document).on('click', '.pagination a', function(e) {
    e.preventDefault();
    const url = new URL($(this).attr('href'));
    const page = url.searchParams.get('page') || 1;
    loadLots(page);
});

// Export functions
function exportData(format) {
    const formData = new FormData($('#filterForm')[0]);
    formData.append('export', format);
    
    const params = new URLSearchParams(formData);
    window.open(`{{ route('lots.index') }}?${params}`, '_blank');
    
    // Show toast notification
    $(document).Toasts('create', {
        class: 'bg-info',
        title: 'Exportación',
        subtitle: 'Iniciada',
        body: `Se ha iniciado la exportación en formato ${format.toUpperCase()}`
    });
}

// Quick actions
function quickEdit(id) {
    $('.btn-ajax[href*="lots/' + id + '/edit"]').click();
}

function quickDelete(id) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/lots/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Eliminado', data.message, 'success');
                    loadLots();
                } else {
                    Swal.fire('Error', data.message || 'Error al eliminar', 'error');
                }
            });
        }
    });
}
</script>
@endpush

@push('styles')
<style>
.table td, .table th {
    vertical-align: middle;
}

.badge-quality {
    font-size: 0.875rem;
}

.progress-sm {
    height: 10px;
}

.btn-group .btn {
    margin-right: 0;
}

.quick-actions {
    white-space: nowrap;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.775rem;
    }
}
</style>
@endpush