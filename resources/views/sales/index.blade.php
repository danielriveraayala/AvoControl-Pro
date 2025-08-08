@extends('layouts.admin')

@section('title', 'Ventas')
@section('page-title', 'Gestión de Ventas')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Ventas</li>
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
                                <label>Estado Venta</label>
                                <select name="status" class="form-control" onchange="loadSales()">
                                    <option value="">Todos</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Borrador</option>
                                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmado</option>
                                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Entregado</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Estado Pago</label>
                                <select name="payment_status" class="form-control" onchange="loadSales()">
                                    <option value="">Todos</option>
                                    <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Parcial</option>
                                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Pagado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Cliente</label>
                                <select name="customer_id" class="form-control" onchange="loadSales()">
                                    <option value="">Todos</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Desde</label>
                                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" onchange="loadSales()">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Hasta</label>
                                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" onchange="loadSales()">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="d-block">
                                    <a href="{{ route('sales.create') }}" class="btn btn-success btn-ajax">
                                        <i class="fas fa-plus"></i> Nueva Venta
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Métricas de Ventas -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-shopping-cart"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Ventas</span>
                    <span class="info-box-number" id="totalSales">{{ $sales->total() }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Valor Total</span>
                    <span class="info-box-number" id="totalValue">${{ number_format($sales->sum('total_amount'), 0) }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-weight"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Peso Total</span>
                    <span class="info-box-number" id="totalWeight">{{ number_format($sales->sum('total_weight'), 0) }} kg</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pendientes Pago</span>
                    <span class="info-box-number" id="pendingPayments">{{ $sales->whereIn('payment_status', ['pending', 'partial'])->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Ventas -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list"></i> Lista de Ventas</h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-download"></i> Exportar
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" onclick="exportData('excel')">
                                    <i class="fas fa-file-excel text-success"></i> Excel
                                </a>
                                <a class="dropdown-item" href="#" onclick="exportData('pdf')">
                                    <i class="fas fa-file-pdf text-danger"></i> PDF
                                </a>
                            </div>
                        </div>
                        <button type="button" class="btn btn-tool" data-card-widget="maximize">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <div id="salesTableContainer">
                            <div class="text-center p-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Cargando...</span>
                                </div>
                                <p class="mt-2 text-muted">Cargando ventas...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Acciones Rápidas -->
    <div class="modal fade" id="quickActionModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
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
    loadSales();
    
    // Auto-refresh every 2 minutes
    setInterval(loadSales, 120000);
});

function loadSales(page = 1) {
    const formData = new FormData($('#filterForm')[0]);
    formData.append('page', page);
    formData.append('ajax', '1');
    
    const params = new URLSearchParams(formData);
    
    // Show loading state
    if (page === 1) {
        $('#salesTableContainer').html(`
            <div class="text-center p-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Cargando...</span>
                </div>
                <p class="mt-2 text-muted">Actualizando datos...</p>
            </div>
        `);
    }
    
    fetch(`{{ route('sales.index') }}?${params}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.html) {
            $('#salesTableContainer').html(data.html);
            
            // Update statistics
            if (data.stats) {
                $('#totalSales').text(data.stats.total);
                $('#totalValue').text('$' + data.stats.value);
                $('#totalWeight').text(data.stats.weight + ' kg');
                $('#pendingPayments').text(data.stats.pending);
            }
            
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
        $('#salesTableContainer').html(`
            <div class="alert alert-danger">
                <h5><i class="icon fas fa-ban"></i> Error de Conexión!</h5>
                No se pudieron cargar las ventas. Verifique su conexión a internet.
                <div class="mt-2">
                    <button class="btn btn-sm btn-outline-danger" onclick="loadSales()">
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
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
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
                <h5><i class="icon fas fa-exclamation-triangle"></i> Error!</h5>
                No se pudo cargar el formulario. 
                <a href="${url}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                    <i class="fas fa-external-link-alt"></i> Abrir en nueva pestaña
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
    loadSales(page);
});

// Export functions
function exportData(format) {
    const formData = new FormData($('#filterForm')[0]);
    formData.append('export', format);
    
    const params = new URLSearchParams(formData);
    window.open(`{{ route('sales.index') }}?${params}`, '_blank');
}
</script>
@endpush