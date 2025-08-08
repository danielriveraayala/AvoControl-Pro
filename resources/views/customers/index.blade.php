@extends('layouts.admin')

@section('title', 'Clientes')
@section('page-title', 'Clientes')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Clientes</li>
@endsection

@section('content')
    <!-- Filtros y Búsqueda -->
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-search"></i> Filtros</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('customers.index') }}" class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Buscar</label>
                                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nombre, contacto o email...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Tipo</label>
                                <select name="customer_type" class="form-control">
                                    <option value="">Todos</option>
                                    <option value="mayorista" {{ request('customer_type') == 'mayorista' ? 'selected' : '' }}>Mayorista</option>
                                    <option value="minorista" {{ request('customer_type') == 'minorista' ? 'selected' : '' }}>Minorista</option>
                                    <option value="distribuidor" {{ request('customer_type') == 'distribuidor' ? 'selected' : '' }}>Distribuidor</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Estado</label>
                                <select name="status" class="form-control">
                                    <option value="">Todos</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activo</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Ciudad</label>
                                <input type="text" name="city" value="{{ request('city') }}" class="form-control" placeholder="Ciudad...">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="d-block">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="d-block">
                                    <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Limpiar
                                    </a>
                                    <a href="{{ route('customers.create') }}" class="btn btn-success">
                                        <i class="fas fa-plus"></i> Nuevo
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
                    <h3>{{ $customers->total() }}</h3>
                    <p>Total Clientes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $customers->where('status', 'active')->count() }}</h3>
                    <p>Clientes Activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>${{ number_format($totalBalance ?? 0, 2) }}</h3>
                    <p>Cuentas por Cobrar</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $customers->where('customer_type', 'mayorista')->count() }}</h3>
                    <p>Mayoristas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-store"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Clientes -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list"></i> Lista de Clientes</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="maximize">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <div id="customersTableContainer">
                        @if(!request()->ajax())
                            @include('customers.partials.table')
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

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

@push('scripts')
<script>
let customersDataTable = null;

$(document).ready(function() {
    // Apply DataTable to existing table
    if ($('#customersTableContainer table').length > 0) {
        initializeCustomersDataTable();
    } else {
        // Load initial data if table doesn't exist
        loadCustomers();
    }
});

function initializeCustomersDataTable() {
    // Apply DataTable to existing table
    if (!$.fn.DataTable.isDataTable('#customersTableContainer table')) {
        customersDataTable = $('#customersTableContainer table').DataTable({
            pageLength: 25,
            order: [[0, 'asc']], // Order by name
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            responsive: true,
            autoWidth: false,
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            drawCallback: function() {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    }
    
    // Apply filters when changed
    $('form[method="GET"] select, form[method="GET"] input').on('change', function() {
        loadCustomers();
    });
}

function loadCustomers(page = 1) {
    const formData = new FormData($('form[method="GET"]')[0]);
    formData.append('page', page);
    formData.append('ajax', '1');
    
    const params = new URLSearchParams(formData);
    
    // Show loading state
    if (page === 1) {
        $('#customersTableContainer').html(`
            <div class="text-center p-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Cargando...</span>
                </div>
                <p class="mt-2 text-muted">Actualizando datos...</p>
            </div>
        `);
    }
    
    fetch(`{{ route('customers.index') }}?${params}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.html) {
            $('#customersTableContainer').html(data.html);
            
            // Update statistics if available
            if (data.stats) {
                $('.small-box:eq(0) .inner h3').text(data.stats.total);
                $('.small-box:eq(1) .inner h3').text(data.stats.active);
                $('.small-box:eq(2) .inner h3').text('$' + data.stats.balance);
                $('.small-box:eq(3) .inner h3').text(data.stats.mayoristas || data.stats.active);
            }
            
            // Re-initialize DataTable on new content
            initializeCustomersDataTable();
            
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
            
            // Update URL
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
            if (page === 1) {
                history.pushState({}, '', url);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        $('#customersTableContainer').html(`
            <div class="alert alert-danger">
                <h5><i class="icon fas fa-ban"></i> Error de Conexión!</h5>
                No se pudieron cargar los clientes.
                <div class="mt-2">
                    <button class="btn btn-sm btn-outline-danger" onclick="loadCustomers()">
                        <i class="fas fa-redo"></i> Reintentar
                    </button>
                </div>
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
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Cargando...</p>
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
                Error al cargar el contenido. 
                <a href="${url}" target="_blank" class="btn btn-sm btn-outline-primary">
                    Abrir en nueva pestaña
                </a>
            </div>
        `);
    });
});

// Handle pagination
$(document).on('click', '.pagination a', function(e) {
    e.preventDefault();
    const url = new URL($(this).attr('href'));
    const page = url.searchParams.get('page') || 1;
    loadCustomers(page);
});
</script>
@endpush