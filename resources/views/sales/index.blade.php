@extends('layouts.admin')

@section('title', 'Ventas')
@section('page-title', 'Gestión de Ventas')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Ventas</li>
@endsection

@section('content')
    <!-- Alertas de Déficit de Inventario -->
    @if(isset($alertasDeficit) && count($alertasDeficit) > 0)
    <div class="row">
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-exclamation-triangle"></i> ¡ALERTA! Déficit de Inventario para Ventas</h5>
                <p class="mb-2">Las siguientes calidades no tienen inventario suficiente para cubrir las ventas:</p>
                <div class="row">
                    @foreach($alertasDeficit as $alerta)
                    <div class="col-md-4 mb-2">
                        <div class="card bg-danger">
                            <div class="card-body p-2">
                                <h6 class="mb-1"><i class="fas fa-box"></i> {{ $alerta['quality'] }}</h6>
                                <small>
                                    <strong>Déficit Total:</strong> {{ number_format($alerta['deficit_total'], 2) }} kg<br>
                                    <strong>Disponible:</strong> {{ number_format($alerta['disponible'], 2) }} kg<br>
                                    <strong>Ventas Afectadas:</strong> {{ $alerta['ventas_afectadas'] }}
                                </small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <hr>
                <p class="mb-0">
                    <strong>Acción requerida:</strong>
                    <a href="{{ route('lots.index') }}" class="btn btn-sm btn-success">
                        <i class="fas fa-plus"></i> Registrar Lotes
                    </a> o cancele ventas para resolver el déficit.
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Métricas de Ventas -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-shopping-cart"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Ventas</span>
                    <span class="info-box-number" id="totalSales">0</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Valor Total</span>
                    <span class="info-box-number" id="totalValue">$0</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-weight"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Peso Total</span>
                    <span class="info-box-number" id="totalWeight">0 kg</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pendientes Pago</span>
                    <span class="info-box-number" id="pendingPayments">0</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline collapsed-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-filter"></i> Filtros</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form id="filterForm" class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Estado Venta</label>
                                <select name="status_filter" class="form-control" onchange="reloadTable()">
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
                                <select name="payment_status_filter" class="form-control" onchange="reloadTable()">
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
                                <select name="customer_id_filter" class="form-control" onchange="reloadTable()">
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
                                <input type="date" name="date_from_filter" value="{{ request('date_from') }}" class="form-control" onchange="reloadTable()">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Hasta</label>
                                <input type="date" name="date_to_filter" value="{{ request('date_to') }}" class="form-control" onchange="reloadTable()">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="d-block">
                                    <button type="button" class="btn btn-success" onclick="openNewSaleModal()">
                                        <i class="fas fa-plus"></i> Nueva Venta
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
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
                            <button type="button" class="btn btn-success" onclick="openNewSaleModal()">
                                <i class="fas fa-plus"></i> Nueva Venta
                            </button>
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
                        <table id="salesTable" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Peso (kg)</th>
                                    <th>Monto</th>
                                    <th>Estado Venta</th>
                                    <th>Estado Pago</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Nueva Venta -->
    <div class="modal fade" id="newSaleModal" tabindex="-1" data-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h4 class="modal-title">
                        <i class="fas fa-shopping-cart"></i>
                        Nueva Venta por Acopio
                    </h4>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="newSaleModalBody">
                    <!-- Content loaded dynamically -->
                    <div class="text-center p-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando formulario de venta...</p>
                    </div>
                </div>
                <div class="modal-footer" id="newSaleModalFooter">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-success" id="submitSaleBtn" onclick="submitSaleForm()" disabled>
                        <i class="fas fa-save"></i> Registrar Venta
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ver Detalles -->
    <div class="modal fade" id="viewSaleModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h4 class="modal-title">
                        <i class="fas fa-eye"></i>
                        Detalles de la Venta
                    </h4>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="viewSaleModalBody">
                    <!-- Content loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Venta -->
    <div class="modal fade" id="editSaleModal" tabindex="-1" data-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title">
                        <i class="fas fa-edit"></i>
                        Editar Venta
                    </h4>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="editSaleModalBody">
                    <!-- Content loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" id="updateSaleBtn" onclick="updateSale()">
                        <i class="fas fa-save"></i> Actualizar Venta
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Registrar Pago -->
    <div class="modal fade" id="paymentModal" tabindex="-1" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h4 class="modal-title">
                        <i class="fas fa-dollar-sign"></i>
                        Registrar Pago de Venta
                    </h4>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="paymentModalBody">
                    <!-- Content loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-success" id="savePaymentBtn" onclick="savePayment()">
                        <i class="fas fa-save"></i> Registrar Pago
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Timeline de Pagos -->
    <div class="modal fade" id="paymentTimelineModal" tabindex="-1" data-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h4 class="modal-title">
                        <i class="fas fa-history"></i>
                        Timeline de Pagos
                    </h4>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="paymentTimelineModalBody">
                    <!-- Content loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap4.min.css">
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.3.0/js/responsive.bootstrap4.min.js"></script>

<script>
let salesTable;
const deficitPorVenta = @json($deficitPorVenta ?? []);

// Variables globales para el modal de ventas
let saleModalInventory = {};
let saleModalItemCount = 0;
let saleModalCustomers = [];

$(document).ready(function() {
    // Setup CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': getCSRFToken()
        },
        xhrFields: {
            withCredentials: true
        }
    });

    // Initialize DataTable
    initializeSalesTable();

    // Debug: Test CSRF token
    console.log('CSRF Token on load:', getCSRFToken());
});

// Helper function to get CSRF token
function getCSRFToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
}


function initializeSalesTable() {
    salesTable = $('#salesTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: '{{ route("sales.index") }}',
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCSRFToken(),
                'Accept': 'application/json'
            },
            xhrFields: {
                withCredentials: true
            },
            data: function(d) {
                // Add filter parameters
                d.status_filter = $('select[name="status_filter"]').val();
                d.payment_status_filter = $('select[name="payment_status_filter"]').val();
                d.customer_id_filter = $('select[name="customer_id_filter"]').val();
                d.date_from_filter = $('input[name="date_from_filter"]').val();
                d.date_to_filter = $('input[name="date_to_filter"]').val();
            },
            dataSrc: function(json) {
                console.log('DataTables response:', json);

                // Check for authentication errors
                if (json.message && json.message === 'Unauthenticated.') {
                    window.location.href = '/login';
                    return [];
                }

                // Update statistics
                if (json.stats) {
                    $('#totalSales').text(json.stats.total);
                    $('#totalValue').text('$' + json.stats.value);
                    $('#totalWeight').text(json.stats.weight + ' kg');
                    $('#pendingPayments').text(json.stats.pending);
                }
                return json.data || [];
            },
            error: function(xhr, error, code) {
                console.error('DataTables AJAX error:', {xhr, error, code});

                if (xhr.status === 401) {
                    window.location.href = '/login';
                } else {
                    toastr.error('Error cargando datos de ventas: ' + (xhr.responseJSON?.message || error));
                }
            }
        },
        columns: [
            {
                data: 'sale_code',
                render: function(data, type, row) {
                    let html = `<strong class="text-primary">${data}</strong>`;
                    if (row.invoice_number) {
                        html += `<br><small class="text-muted"><i class="fas fa-receipt"></i> ${row.invoice_number}</small>`;
                    }
                    return html;
                }
            },
            {
                data: 'customer',
                render: function(data, type, row) {
                    let html = `<div class="d-flex align-items-center"><div><strong>${data}</strong>`;
                    if (row.customer_type) {
                        const badgeClass = {
                            'mayorista': 'badge-primary',
                            'distribuidor': 'badge-info'
                        }[row.customer_type] || 'badge-secondary';
                        html += `<br><span class="badge badge-sm ${badgeClass}">${row.customer_type.charAt(0).toUpperCase() + row.customer_type.slice(1)}</span>`;
                    }
                    html += '</div></div>';
                    return html;
                }
            },
            {
                data: 'sale_date',
                render: function(data, type, row) {
                    let html = `<span class="badge badge-info">${data}</span>`;
                    html += `<br><small class="text-muted">${row.sale_date_human}</small>`;
                    if (row.delivery_date) {
                        html += `<br><small class="text-success"><i class="fas fa-truck"></i> ${row.delivery_date}</small>`;
                    }
                    return html;
                }
            },
            {
                data: 'total_weight',
                render: function(data, type, row) {
                    let html = `<strong>${data} kg</strong>`;
                    if (row.items_count > 0) {
                        const itemText = row.items_count == 1 ? 'item' : 'items';
                        html += `<br><small class="text-muted">${row.items_count} ${itemText}</small>`;
                    }

                    // Verificar si hay déficit para esta venta
                    if (deficitPorVenta[row.id]) {
                        html += '<div class="mt-1">';
                        deficitPorVenta[row.id].forEach(function(deficit) {
                            html += `
                                <small class="d-block">
                                    <span class="badge badge-danger">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Déficit ${deficit.quality}: ${deficit.deficit.toFixed(2)} kg
                                    </span>
                                </small>
                            `;
                        });
                        html += '</div>';
                    }

                    return html;
                }
            },
            {
                data: 'total_amount',
                render: function(data, type, row) {
                    let html = `<strong class="text-success">$${data}</strong>`;
                    if (parseFloat(row.avg_price) > 0) {
                        html += `<br><small class="text-muted">$${row.avg_price}/kg</small>`;
                    }
                    return html;
                }
            },
            {
                data: 'status',
                render: function(data, type, row) {
                    const statusConfig = {
                        'draft': { class: 'secondary', icon: 'edit', text: 'Borrador' },
                        'confirmed': { class: 'primary', icon: 'check', text: 'Confirmado' },
                        'shipped': { class: 'info', icon: 'shipping-fast', text: 'Enviado' },
                        'delivered': { class: 'success', icon: 'handshake', text: 'Entregado' },
                        'cancelled': { class: 'danger', icon: 'times', text: 'Cancelado' }
                    };
                    const config = statusConfig[data] || { class: 'secondary', icon: 'question', text: data };
                    let html = `<span class="badge badge-${config.class}"><i class="fas fa-${config.icon}"></i> ${config.text}</span>`;

                    // Añadir indicador de déficit si existe
                    if (deficitPorVenta[row.id] && data !== 'cancelled') {
                        html += ' <span class="badge badge-danger" title="Déficit de inventario"><i class="fas fa-exclamation-triangle"></i></span>';
                    }

                    return html;
                }
            },
            {
                data: 'payment_status',
                render: function(data, type, row) {
                    const statusConfig = {
                        'pending': { class: 'warning', text: 'Pendiente' },
                        'partial': { class: 'info', text: 'Parcial' },
                        'paid': { class: 'success', text: 'Pagado' },
                        'overdue': { class: 'danger', text: 'Vencido' }
                    };
                    const config = statusConfig[data] || { class: 'secondary', text: data };
                    return `<div class="d-flex align-items-center"><span class="badge badge-${config.class}">${config.text}</span></div>`;
                }
            },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    let html = '<div class="btn-group">';

                    console.log('Rendering actions for row:', row); // Debug log

                    html += `<button type="button" class="btn btn-sm btn-info" onclick="showSaleDetails(${row.id})" data-toggle="tooltip" title="Ver detalles">
                        <i class="fas fa-eye"></i>
                    </button>`;

                    if (row.can_edit) {
                        html += `<button type="button" class="btn btn-sm btn-primary" onclick="editSale(${row.id})" data-toggle="tooltip" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>`;
                    }

                    // Botón de Timeline de Pagos
                    html += `<button type="button" class="btn btn-sm btn-warning" onclick="showPaymentTimeline(${row.id})" data-toggle="tooltip" title="Timeline de Pagos">
                        <i class="fas fa-history"></i>
                    </button>`;

                    if (row.status !== 'cancelled') {
                        html += `<div class="btn-group">
                            <button type="button" class="btn btn-sm btn-secondary dropdown-toggle dropdown-icon" data-toggle="dropdown"></button>
                            <div class="dropdown-menu">`;

                        if (row.status === 'draft') {
                            html += `<a class="dropdown-item" href="#" onclick="changeStatus(${row.id}, 'confirmed')">
                                <i class="fas fa-check text-success"></i> Confirmar
                            </a>`;
                        }

                        if (row.status === 'confirmed') {
                            html += `<a class="dropdown-item" href="#" onclick="changeStatus(${row.id}, 'delivered')">
                                <i class="fas fa-truck text-info"></i> Marcar Entregado
                            </a>`;
                        }

                        if (row.payment_status !== 'paid') {
                            html += `<a class="dropdown-item" href="#" onclick="registerPayment(${row.id})">
                                <i class="fas fa-dollar-sign text-success"></i> Registrar Pago
                            </a>`;
                        }

                        html += `<a class="dropdown-item" href="#" onclick="printInvoice(${row.id})">
                                    <i class="fas fa-print text-primary"></i> Imprimir Factura
                                </a>`;

                        if (['draft', 'confirmed'].includes(row.status)) {
                            html += `<div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#" onclick="changeStatus(${row.id}, 'cancelled')">
                                    <i class="fas fa-ban"></i> Cancelar Venta
                                </a>`;
                        }

                        if (row.can_delete) {
                            html += `<div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#" onclick="deleteSale(${row.id})">
                                    <i class="fas fa-trash"></i> Eliminar Registro
                                </a>`;
                        }

                        html += '</div></div>';
                    }

                    html += '</div>';
                    return html;
                }
            }
        ],
        order: [[2, 'desc']], // Order by date descending
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.12.1/i18n/es-ES.json'
        },
        drawCallback: function() {
            // Initialize tooltips after table redraw
            $('[data-toggle="tooltip"]').tooltip();
        }
    });
}

function reloadTable() {
    if (salesTable) {
        salesTable.ajax.reload(null, false); // false = stay on current page
    }
}

// Function to refresh table after creating new sale (backward compatibility)
function loadSales() {
    reloadTable();
}

// Export functions
function exportData(format) {
    const formData = new FormData($('#filterForm')[0]);
    formData.append('export', format);

    const params = new URLSearchParams(formData);
    window.open(`{{ route('sales.index') }}?${params}`, '_blank');
}

// Action functions
function showSaleDetails(saleId) {
    console.log('Opening sale details modal for ID:', saleId);
    $('#viewSaleModal').modal('show');
    $('#viewSaleModalBody').html('<div class="text-center p-4"><div class="spinner-border text-primary"></div><p class="mt-2">Cargando detalles...</p></div>');

    fetch(`{{ url('sales') }}/${saleId}/details`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('Details response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Details data received:', data);
        if (data.success && data.html) {
            $('#viewSaleModalBody').html(data.html);
        } else if (data.error) {
            console.error('Server error:', data.error);
            toastr.error('Error del servidor: ' + data.error);
        } else {
            console.error('No HTML content in response');
            toastr.error('No se recibió contenido del reporte');
        }
    })
    .catch(error => {
        console.error('Error loading sale details:', error);
        toastr.error('Error al cargar los detalles: ' + error.message);
    });
}

function editSale(saleId) {
    console.log('Opening edit sale modal for ID:', saleId);
    $('#editSaleModal').modal('show');
    $('#editSaleModalBody').html('<div class="text-center p-4"><div class="spinner-border text-primary"></div><p class="mt-2">Cargando formulario...</p></div>');

    fetch(`{{ url('sales') }}/${saleId}/edit-modal`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('Edit response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Edit data received:', data);
        if (data.success && data.html) {
            $('#editSaleModalBody').html(data.html);
        } else if (data.error) {
            console.error('Server error:', data.error);
            toastr.error('Error del servidor: ' + data.error);
        } else {
            console.error('No HTML content in response');
            toastr.error('No se recibió contenido del formulario');
        }
    })
    .catch(error => {
        console.error('Error loading edit form:', error);
        toastr.error('Error al cargar el formulario: ' + error.message);
    });
}

function changeStatus(saleId, newStatus) {
    let title, text, confirmButtonText, confirmButtonClass;

    switch(newStatus) {
        case 'confirmed':
            title = '¿Confirmar venta?';
            text = 'Esta acción confirmará la venta y no se podrá editar posteriormente.';
            confirmButtonText = 'Sí, confirmar';
            confirmButtonClass = 'success';
            break;
        case 'delivered':
            title = '¿Marcar como entregada?';
            text = 'Se marcará la venta como entregada con la fecha actual.';
            confirmButtonText = 'Sí, entregar';
            confirmButtonClass = 'info';
            break;
        case 'cancelled':
            title = '¿Cancelar venta?';
            text = 'Esta acción cancelará la venta y liberará el inventario.';
            confirmButtonText = 'Sí, cancelar';
            confirmButtonClass = 'danger';
            break;
    }

    Swal.fire({
        title: title,
        text: text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: confirmButtonClass === 'danger' ? '#dc3545' : confirmButtonClass === 'success' ? '#28a745' : '#17a2b8',
        cancelButtonColor: '#6c757d',
        confirmButtonText: confirmButtonText,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            updateSaleStatus(saleId, newStatus);
        }
    });
}

function updateSaleStatus(saleId, newStatus) {
    $.ajax({
        url: `{{ url('sales') }}/${saleId}/status`,
        method: 'PATCH',
        data: JSON.stringify({ status: newStatus }),
        contentType: 'application/json',
        dataType: 'json',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getCSRFToken()
        },
        success: function(data) {
            if (data.success) {
                toastr.success(data.message);
                reloadTable();
            } else {
                toastr.error(data.message || 'Error al actualizar el estado');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error in updateSaleStatus:', error);

            if (xhr.status === 401 || xhr.responseText.includes('login')) {
                Swal.fire({
                    title: 'Sesión Expirada',
                    text: 'Tu sesión ha expirado. Serás redirigido al login.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = '/login';
                });
                return;
            }

            toastr.error('Error de conexión');
        }
    });
}

function registerPayment(saleId, fromTimeline = false) {
    console.log('Opening payment modal for sale ID:', saleId);

    // Si viene del timeline, cerrar el modal del timeline primero
    if (fromTimeline || $('#paymentTimelineModal').hasClass('show')) {
        $('#paymentTimelineModal').modal('hide');

        // Esperar a que se cierre el modal del timeline antes de abrir el de pagos
        setTimeout(() => {
            $('#paymentModal').modal('show');
            loadPaymentForm(saleId);
        }, 500);
    } else {
        $('#paymentModal').modal('show');
        loadPaymentForm(saleId);
    }
}

function loadPaymentForm(saleId) {
    $('#paymentModalBody').html('<div class="text-center p-4"><div class="spinner-border text-primary"></div><p class="mt-2">Cargando formulario de pago...</p></div>');

    fetch(`{{ url('sales') }}/${saleId}/payment-form`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('Payment form response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Payment form data received:', data);
        if (data.success && data.html) {
            $('#paymentModalBody').html(data.html);
        } else if (data.error) {
            console.error('Server error:', data.error);
            toastr.error('Error del servidor: ' + data.error);
        } else {
            console.error('No HTML content in response');
            toastr.error('No se recibió contenido del formulario');
        }
    })
    .catch(error => {
        console.error('Error loading payment form:', error);
        toastr.error('Error al cargar el formulario de pago: ' + error.message);
    });
}

function savePayment() {
    const form = $('#paymentForm')[0];
    const formData = new FormData(form);

    $('#savePaymentBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    $.ajax({
        url: '{{ url('payments/sale-payment') }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getCSRFToken()
        },
        success: function(data) {
            if (data.success) {
                toastr.success(data.message);
                $('#paymentModal').modal('hide');
                reloadTable();
            } else {
                toastr.error(data.message || 'Error al registrar el pago');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error in savePayment:', error);

            if (xhr.status === 401 || xhr.responseText.includes('login')) {
                Swal.fire({
                    title: 'Sesión Expirada',
                    text: 'Tu sesión ha expirado. Serás redirigido al login.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = '/login';
                });
                return;
            }

            toastr.error('Error de conexión');
        },
        complete: function() {
            $('#savePaymentBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Registrar Pago');
        }
    });
}

function printInvoice(saleId) {
    window.open(`{{ url('sales') }}/${saleId}/invoice`, '_blank');
}

function showPaymentTimeline(saleId) {
    console.log('Opening payment timeline for sale ID:', saleId);
    $('#paymentTimelineModal').modal('show');
    $('#paymentTimelineModalBody').html('<div class="text-center p-4"><div class="spinner-border text-primary"></div><p class="mt-2">Cargando timeline...</p></div>');

    fetch(`{{ url('sales') }}/${saleId}/payment-timeline`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('Timeline response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Timeline data received:', data);
        if (data.success && data.html) {
            $('#paymentTimelineModalBody').html(data.html);
        } else if (data.error) {
            console.error('Server error:', data.error);
            toastr.error('Error del servidor: ' + data.error);
        } else {
            console.error('No HTML content in response');
            toastr.error('No se recibió contenido del timeline');
        }
    })
    .catch(error => {
        console.error('Error loading payment timeline:', error);
        toastr.error('Error al cargar el timeline: ' + error.message);
    });
}

function updateSale() {
    const form = $('#editSaleForm')[0];
    const formData = new FormData(form);
    const saleId = formData.get('sale_id');

    $('#updateSaleBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');

    $.ajax({
        url: `{{ url('sales') }}/${saleId}/update-modal`,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getCSRFToken()
        },
        success: function(data) {
            if (data.success) {
                toastr.success(data.message);
                $('#editSaleModal').modal('hide');
                reloadTable();
            } else {
                toastr.error(data.message || 'Error al actualizar la venta');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error in updateSale:', error);

            if (xhr.status === 401 || xhr.responseText.includes('login')) {
                Swal.fire({
                    title: 'Sesión Expirada',
                    text: 'Tu sesión ha expirado. Serás redirigido al login.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = '/login';
                });
                return;
            }

            toastr.error('Error de conexión');
        },
        complete: function() {
            $('#updateSaleBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Actualizar Venta');
        }
    });
}

function deleteSale(saleId) {
    Swal.fire({
        title: '¿Eliminar venta?',
        text: 'Esta acción eliminará permanentemente el registro de venta y no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `{{ url('sales') }}/${saleId}`,
                method: 'DELETE',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken()
                },
                success: function(data) {
                    if (data.success) {
                        toastr.success(data.message);
                        reloadTable();
                    } else {
                        toastr.error(data.message || 'Error al eliminar la venta');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error in deleteSale:', error);

                    if (xhr.status === 401 || xhr.responseText.includes('login')) {
                        Swal.fire({
                            title: 'Sesión Expirada',
                            text: 'Tu sesión ha expirado. Serás redirigido al login.',
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = '/login';
                        });
                        return;
                    }

                    toastr.error('Error de conexión');
                }
            });
        }
    });
}

// ===============================
// NUEVA VENTA MODAL FUNCTIONS
// ===============================

function openNewSaleModal() {
    $('#newSaleModal').modal('show');
    loadNewSaleForm();
}

function loadNewSaleForm() {
    $('#newSaleModalBody').html(`
        <div class="text-center p-4">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
            <p class="mt-2">Cargando datos de inventario y clientes...</p>
        </div>
    `);

    // Cargar datos para el formulario
    fetch('{{ route("sales.create") }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        saleModalInventory = data.inventario || {};
        saleModalCustomers = data.customers || [];
        renderNewSaleForm();
    })
    .catch(error => {
        console.error('Error:', error);
        $('#newSaleModalBody').html(`
            <div class="alert alert-danger">
                <h5><i class="icon fas fa-exclamation-triangle"></i> Error!</h5>
                No se pudieron cargar los datos del formulario.
                <button class="btn btn-sm btn-outline-danger mt-2" onclick="loadNewSaleForm()">
                    <i class="fas fa-redo"></i> Reintentar
                </button>
            </div>
        `);
    });
}

function renderNewSaleForm() {
    const inventoryCards = Object.values(saleModalInventory).map(inv => {
        // Usar el color dinámico de la base de datos
        const qualityColor = inv.quality_color || '#6c757d';

        return `
            <div class="col-md-3">
                <div class="text-center">
                    <span class="badge badge-lg d-block mb-1" style="background-color: ${qualityColor}; color: white;">
                        ${inv.quality_grade}
                    </span>
                    <strong class="d-block">${parseFloat(inv.peso_disponible).toFixed(2)} kg</strong>
                    <small class="text-muted">disponible</small>
                </div>
            </div>
        `;
    }).join('');

    const customerOptions = saleModalCustomers.map(customer =>
        `<option value="${customer.id}" data-phone="${customer.phone || ''}" data-address="${customer.address || ''}">${customer.name} - ${customer.contact_person}</option>`
    ).join('');

    const formHtml = `
        <form id="modalSaleForm">
            <div class="row">
                <!-- Información General -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-user"></i>
                                Información General
                            </h3>
                        </div>
                        <div class="card-body">
                            <!-- Cliente -->
                            <div class="form-group">
                                <label for="modal_customer_id">Cliente *</label>
                                <select name="customer_id" id="modal_customer_id" class="form-control" required>
                                    <option value="">Seleccione un cliente</option>
                                    ${customerOptions}
                                </select>
                                <small class="form-text text-muted" id="modal-customer-info"></small>
                            </div>

                            <!-- Fecha de Venta -->
                            <div class="form-group">
                                <label for="modal_sale_date">Fecha de Venta *</label>
                                <input type="date" name="sale_date" id="modal_sale_date" class="form-control"
                                       value="${new Date().toISOString().split('T')[0]}"
                                       max="${new Date().toISOString().split('T')[0]}" required>
                            </div>

                            <!-- Fecha de Entrega -->
                            <div class="form-group">
                                <label for="modal_delivery_date">Fecha de Entrega</label>
                                <input type="date" name="delivery_date" id="modal_delivery_date" class="form-control">
                            </div>

                            <!-- Número de Factura -->
                            <div class="form-group">
                                <label for="modal_invoice_number">Número de Factura</label>
                                <input type="text" name="invoice_number" id="modal_invoice_number" class="form-control" placeholder="Opcional">
                            </div>

                            <!-- Notas -->
                            <div class="form-group">
                                <label for="modal_notes">Notas</label>
                                <textarea name="notes" id="modal_notes" class="form-control" rows="3"
                                         placeholder="Notas adicionales de la venta"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items de Venta -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-layer-group"></i>
                                Items por Calidad
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-success btn-sm" onclick="addModalQualityItem()">
                                    <i class="fas fa-plus"></i> Agregar Calidad
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">

                            <!-- Inventario Disponible -->
                            <div class="alert m-3">
                                <h5><i class="icon fas fa-info-circle"></i> Inventario Disponible</h5>
                                <div class="row">
                                    ${inventoryCards}
                                </div>
                            </div>

                            <!-- Container de Items -->
                            <div id="modal-items-container" class="p-3">
                                <div class="text-center text-muted py-4" id="modal-no-items-message">
                                    <i class="fas fa-shopping-cart fa-3x mb-2"></i>
                                    <p>No hay items agregados. Haga clic en "Agregar Calidad" para comenzar.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 text-center">
                                    <div class="description-block">
                                        <span class="description-percentage text-success">
                                            <i class="fas fa-weight"></i>
                                        </span>
                                        <h5 class="description-header" id="modal-totalWeight">0.00</h5>
                                        <span class="description-text">KG TOTAL</span>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="description-block">
                                        <span class="description-percentage text-info">
                                            <i class="fas fa-layer-group"></i>
                                        </span>
                                        <h5 class="description-header" id="modal-totalItems">0</h5>
                                        <span class="description-text">CALIDADES</span>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="description-block">
                                        <span class="description-percentage text-warning">
                                            <i class="fas fa-dollar-sign"></i>
                                        </span>
                                        <h5 class="description-header" id="modal-totalAmount">$0.00</h5>
                                        <span class="description-text">MONTO TOTAL</span>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="description-block">
                                        <span class="description-percentage text-primary">
                                            <i class="fas fa-calculator"></i>
                                        </span>
                                        <h5 class="description-header" id="modal-avgPrice">$0.00</h5>
                                        <span class="description-text">PRECIO PROM.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    `;

    $('#newSaleModalBody').html(formHtml);

    // Event listeners
    $('#modal_customer_id').change(function() {
        const option = $(this).find('option:selected');
        const phone = option.data('phone');
        const address = option.data('address');

        let info = '';
        if (phone) info += `Teléfono: ${phone}`;
        if (address) info += (info ? ' | ' : '') + `Dirección: ${address}`;

        $('#modal-customer-info').text(info);
        updateModalSummary();
    });

    $('#modal_sale_date').change(updateModalSummary);

    // Verificar si se pasó calidad por URL
    const urlParams = new URLSearchParams(window.location.search);
    const preSelectedQuality = urlParams.get('calidad');
    if (preSelectedQuality && saleModalInventory[preSelectedQuality]) {
        addModalQualityItem(preSelectedQuality);
    }
}

function addModalQualityItem(preSelectedQuality = null) {
    const container = $('#modal-items-container');
    const noItemsMsg = $('#modal-no-items-message');

    noItemsMsg.hide();
    saleModalItemCount++;

    // Obtener calidades disponibles (que no estén ya seleccionadas)
    const usedQualities = [];
    $('.modal-quality-select').each(function() {
        if ($(this).val()) {
            usedQualities.push($(this).val());
        }
    });

    const availableQualities = Object.keys(saleModalInventory).filter(q =>
        !usedQualities.includes(q) && saleModalInventory[q].peso_disponible > 0
    );

    if (availableQualities.length === 0 && !preSelectedQuality) {
        toastr.warning('No hay más calidades disponibles para agregar');
        return;
    }

    const qualityOptions = availableQualities.map(quality => `
        <option value="${quality}"
               ${preSelectedQuality === quality ? 'selected' : ''}
               data-available="${saleModalInventory[quality].peso_disponible}">
            ${quality} (${parseFloat(saleModalInventory[quality].peso_disponible).toFixed(2)} kg disponible)
        </option>
    `).join('');

    const itemHtml = `
        <div class="modal-item-row card card-outline card-primary mb-3" id="modal-item-${saleModalItemCount}">
            <div class="card-header">
                <h3 class="card-title">Item #${saleModalItemCount}</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool text-danger" onclick="removeModalItem(${saleModalItemCount})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Calidad *</label>
                            <select name="items[${saleModalItemCount}][quality_grade]" class="form-control modal-quality-select"
                                   onchange="updateModalQualityInfo(${saleModalItemCount}, this.value)" required>
                                <option value="">Seleccione calidad</option>
                                ${qualityOptions}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Peso (kg) *</label>
                            <input type="number" name="items[${saleModalItemCount}][weight]"
                                  class="form-control modal-weight-input"
                                  min="0.01" step="0.01"
                                  onchange="calculateModalItemTotal(${saleModalItemCount})" required>
                            <small class="form-text text-muted" id="modal-available-${saleModalItemCount}"></small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Precio/kg *</label>
                            <input type="number" name="items[${saleModalItemCount}][price_per_kg]"
                                  class="form-control modal-price-input"
                                  min="0.01" step="0.01"
                                  onchange="calculateModalItemTotal(${saleModalItemCount})" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Subtotal</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="text" class="form-control modal-subtotal-display" readonly>
                                <input type="hidden" name="items[${saleModalItemCount}][subtotal]" class="modal-subtotal-input">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Estado</label>
                            <div class="text-center">
                                <span class="badge badge-secondary" id="modal-status-${saleModalItemCount}">Incompleto</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    container.append(itemHtml);

    if (preSelectedQuality) {
        updateModalQualityInfo(saleModalItemCount, preSelectedQuality);
    }

    updateModalSummary();
}

function removeModalItem(itemId) {
    $(`#modal-item-${itemId}`).remove();
    updateModalSummary();

    if ($('.modal-item-row').length === 0) {
        $('#modal-no-items-message').show();
    }
}

function updateModalQualityInfo(itemId, quality) {
    if (!quality || !saleModalInventory[quality]) {
        $(`#modal-available-${itemId}`).text('');
        return;
    }

    const available = parseFloat(saleModalInventory[quality].peso_disponible);
    const qualityColor = saleModalInventory[quality].quality_color || '#6c757d';

    $(`#modal-available-${itemId}`).text(`Disponible: ${available.toFixed(2)} kg`);

    // Actualizar el título del card con el color de la calidad
    const cardTitle = $(`#modal-item-${itemId} .card-title`);
    cardTitle.html(`Item #${itemId} <span class="badge badge-sm ml-2" style="background-color: ${qualityColor}; color: white;">${quality}</span>`);

    $(`#modal-item-${itemId} .modal-weight-input`).attr('max', available);
    calculateModalItemTotal(itemId);
}

function calculateModalItemTotal(itemId) {
    const itemRow = $(`#modal-item-${itemId}`);
    const weight = parseFloat(itemRow.find('.modal-weight-input').val()) || 0;
    const price = parseFloat(itemRow.find('.modal-price-input').val()) || 0;
    const quality = itemRow.find('.modal-quality-select').val();

    if (quality && saleModalInventory[quality]) {
        const available = parseFloat(saleModalInventory[quality].peso_disponible);
        if (weight > available) {
            toastr.error(`No hay suficiente inventario. Disponible: ${available.toFixed(2)} kg`);
            itemRow.find('.modal-weight-input').val(available);
            return;
        }
    }

    const subtotal = weight * price;

    itemRow.find('.modal-subtotal-display').val(subtotal.toFixed(2));
    itemRow.find('.modal-subtotal-input').val(subtotal);

    const statusBadge = $(`#modal-status-${itemId}`);
    if (weight > 0 && price > 0 && quality) {
        statusBadge.removeClass('badge-secondary').addClass('badge-success').text('Completo');
    } else {
        statusBadge.removeClass('badge-success').addClass('badge-secondary').text('Incompleto');
    }

    updateModalSummary();
}

function updateModalSummary() {
    let totalWeight = 0;
    let totalAmount = 0;
    let totalItems = 0;
    let allComplete = true;

    $('.modal-item-row').each(function() {
        const weight = parseFloat($(this).find('.modal-weight-input').val()) || 0;
        const price = parseFloat($(this).find('.modal-price-input').val()) || 0;
        const quality = $(this).find('.modal-quality-select').val();

        if (weight > 0 && price > 0 && quality) {
            totalWeight += weight;
            totalAmount += (weight * price);
            totalItems++;
        } else {
            allComplete = false;
        }
    });

    const avgPrice = totalWeight > 0 ? totalAmount / totalWeight : 0;

    $('#modal-totalWeight').text(totalWeight.toFixed(2));
    $('#modal-totalAmount').text(totalAmount.toLocaleString('es-MX', {minimumFractionDigits: 2}));
    $('#modal-totalItems').text(totalItems);
    $('#modal-avgPrice').text('$' + avgPrice.toFixed(2));

    const hasCustomer = $('#modal_customer_id').val();
    const hasDate = $('#modal_sale_date').val();

    $('#submitSaleBtn').prop('disabled', !(allComplete && totalItems > 0 && hasCustomer && hasDate));
}

function submitSaleForm() {
    const form = $('#modalSaleForm')[0];
    const formData = new FormData(form);

    // Disable submit button and show loading
    $('#submitSaleBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    fetch('{{ route("sales.store") }}', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getCSRFToken()
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                try {
                    const errorData = JSON.parse(text);
                    if (response.status === 401 || errorData.message === 'Unauthenticated.') {
                        window.location.href = '/login';
                        return;
                    }
                    throw new Error(errorData.message || `Error HTTP ${response.status}`);
                } catch (e) {
                    throw new Error(`Error HTTP ${response.status}: ${text}`);
                }
            });
        }

        return response.json();
    })
    .then(data => {
        // Verificar si es una respuesta de no autenticación
        if (data.message === 'Unauthenticated.') {
            toastr.error('Sesión expirada. Redirigiendo al login...');
            setTimeout(() => window.location.href = '/login', 2000);
            return;
        }

        // Verificar si hay errores de validación
        if (data.errors) {
            let errorMessages = [];
            Object.keys(data.errors).forEach(field => {
                errorMessages.push(...data.errors[field]);
            });
            toastr.error('Errores de validación: ' + errorMessages.join(', '));
            return;
        }

        if (data.success === true) {
            toastr.success(data.message || 'Venta registrada exitosamente');
            $('#newSaleModal').modal('hide');

            // Wait a moment for modal to close, then refresh the sales list
            setTimeout(() => {
                reloadTable(); // Refresh the DataTable
            }, 300);

            // Show additional confirmation with sale code if available
            if (data.sale && data.sale.sale_code) {
                setTimeout(() => {
                    toastr.info(`Código de venta: ${data.sale.sale_code}`, 'Venta Creada', {timeOut: 5000});
                }, 1000);
            }
        } else {
            throw new Error(data.message || 'Error al procesar la venta');
        }
    })
    .catch(error => {
        console.error('Error en venta:', error);
        console.error('Stack trace:', error.stack);

        // Mostrar alerta con más detalles para debugging
        const errorMsg = error.message || 'Error desconocido al registrar la venta';
        toastr.error(errorMsg);

        // Alerta temporal para debugging (remover en producción)
        console.log('DEBUG: Error completo capturado:', {
            message: error.message,
            name: error.name,
            stack: error.stack
        });
    })
    .finally(() => {
        $('#submitSaleBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Registrar Venta');
    });
}

// Reset modal when closed
$('#newSaleModal').on('hidden.bs.modal', function() {
    saleModalItemCount = 0;
    $('#newSaleModalBody').html(`
        <div class="text-center p-4">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
            <p class="mt-2">Cargando formulario de venta...</p>
        </div>
    `);
    $('#submitSaleBtn').prop('disabled', true);
});
</script>
@endpush
