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
                                <select name="status_filter" class="form-control" onchange="reloadTable()">
                                    <option value="">Todos</option>
                                    <option value="draft">Borrador</option>
                                    <option value="confirmed">Confirmado</option>
                                    <option value="delivered">Entregado</option>
                                    <option value="cancelled">Cancelado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Estado Pago</label>
                                <select name="payment_status_filter" class="form-control" onchange="reloadTable()">
                                    <option value="">Todos</option>
                                    <option value="pending">Pendiente</option>
                                    <option value="partial">Parcial</option>
                                    <option value="paid">Pagado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Cliente</label>
                                <select name="customer_id_filter" class="form-control" onchange="reloadTable()">
                                    <option value="">Todos</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Desde</label>
                                <input type="date" name="date_from_filter" class="form-control" onchange="reloadTable()">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Hasta</label>
                                <input type="date" name="date_to_filter" class="form-control" onchange="reloadTable()">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-primary btn-block" onclick="reloadTable()">
                                    <i class="fas fa-search"></i>
                                </button>
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
                    <h3 class="card-title">
                        <i class="fas fa-shopping-cart"></i>
                        Lista de Ventas
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success" onclick="window.location='{{ route('sales.create') }}'">
                            <i class="fas fa-plus"></i> Nueva Venta
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="salesTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Peso Total</th>
                                    <th>Monto Total</th>
                                    <th>Estado</th>
                                    <th>Estado Pago</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
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
                    <!-- Content loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar -->
    <div class="modal fade" id="editSaleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
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
                    <!-- Content loaded via AJAX -->
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
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h4 class="modal-title">
                        <i class="fas fa-dollar-sign"></i>
                        Registrar Pago
                    </h4>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="paymentModalBody">
                    <!-- Content loaded via AJAX -->
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
@endsection

@section('scripts')
<script>
let salesTable;

$(document).ready(function() {
    // Initialize DataTable
    initializeSalesTable();
});

// ========================================
// DATATABLE INITIALIZATION
// ========================================
function initializeSalesTable() {
    salesTable = $('#salesTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        ajax: {
            url: '{{ route("sales.index") }}',
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: function(d) {
                d.status_filter = $('select[name="status_filter"]').val();
                d.payment_status_filter = $('select[name="payment_status_filter"]').val();
                d.customer_id_filter = $('select[name="customer_id_filter"]').val();
                d.date_from_filter = $('input[name="date_from_filter"]').val();
                d.date_to_filter = $('input[name="date_to_filter"]').val();
            }
        },
        columns: [
            { data: 'sale_code', name: 'sale_code' },
            { data: 'customer', name: 'customer.name' },
            { data: 'sale_date', name: 'sale_date' },
            { data: 'total_weight', name: 'total_weight' },
            { data: 'total_amount', name: 'total_amount' },
            { 
                data: 'status', 
                name: 'status',
                render: function(data) {
                    const badges = {
                        'draft': '<span class="badge badge-secondary">Borrador</span>',
                        'confirmed': '<span class="badge badge-primary">Confirmado</span>',
                        'delivered': '<span class="badge badge-success">Entregado</span>',
                        'cancelled': '<span class="badge badge-danger">Cancelado</span>'
                    };
                    return badges[data] || '<span class="badge badge-secondary">' + data + '</span>';
                }
            },
            { 
                data: 'payment_status', 
                name: 'payment_status',
                render: function(data) {
                    const badges = {
                        'pending': '<span class="badge badge-warning">Pendiente</span>',
                        'partial': '<span class="badge badge-info">Parcial</span>',
                        'paid': '<span class="badge badge-success">Pagado</span>'
                    };
                    return badges[data] || '<span class="badge badge-secondary">' + data + '</span>';
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    let actions = '<div class="btn-group btn-group-sm">';
                    
                    // Ver detalles
                    actions += `<button type="button" class="btn btn-info" onclick="viewDetails(${row.id})" title="Ver detalles">
                        <i class="fas fa-eye"></i>
                    </button>`;
                    
                    // Editar (solo en borrador)
                    if (row.status === 'draft') {
                        actions += `<button type="button" class="btn btn-primary" onclick="editSale(${row.id})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>`;
                    }
                    
                    // Confirmar (solo en borrador)
                    if (row.status === 'draft') {
                        actions += `<button type="button" class="btn btn-warning" onclick="confirmSale(${row.id})" title="Confirmar">
                            <i class="fas fa-check"></i>
                        </button>`;
                    }
                    
                    // Entregar (solo confirmado)
                    if (row.status === 'confirmed') {
                        actions += `<button type="button" class="btn btn-success" onclick="deliverSale(${row.id})" title="Marcar como entregado">
                            <i class="fas fa-truck"></i>
                        </button>`;
                    }
                    
                    // Registrar pago (si no está cancelado y no está completamente pagado)
                    if (row.status !== 'cancelled' && row.payment_status !== 'paid') {
                        actions += `<button type="button" class="btn btn-success" onclick="registerPayment(${row.id})" title="Registrar pago">
                            <i class="fas fa-dollar-sign"></i>
                        </button>`;
                    }
                    
                    // Imprimir factura
                    actions += `<button type="button" class="btn btn-secondary" onclick="printInvoice(${row.id})" title="Imprimir factura">
                        <i class="fas fa-print"></i>
                    </button>`;
                    
                    // Cancelar (solo borrador o confirmado)
                    if (row.status === 'draft' || row.status === 'confirmed') {
                        actions += `<button type="button" class="btn btn-warning" onclick="cancelSale(${row.id})" title="Cancelar venta">
                            <i class="fas fa-times"></i>
                        </button>`;
                    }
                    
                    // Eliminar (solo borrador sin pagos)
                    if (row.status === 'draft' && row.payment_status === 'pending') {
                        actions += `<button type="button" class="btn btn-danger" onclick="deleteSale(${row.id})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>`;
                    }
                    
                    actions += '</div>';
                    return actions;
                }
            }
        ],
        order: [[2, 'desc']],
        pageLength: 25
    });
}

// ========================================
// UTILITY FUNCTIONS
// ========================================
function reloadTable() {
    if (salesTable) {
        salesTable.ajax.reload();
    }
}

function showToast(type, message) {
    if (typeof toastr !== 'undefined') {
        toastr[type](message);
    } else {
        alert(message);
    }
}

function getCSRFToken() {
    return $('meta[name="csrf-token"]').attr('content');
}

// ========================================
// MODAL FUNCTIONS
// ========================================

// 1. Ver Detalles
function viewDetails(saleId) {
    $('#viewSaleModal').modal('show');
    $('#viewSaleModalBody').html('<div class="text-center p-4"><div class="spinner-border"></div><p class="mt-2">Cargando...</p></div>');
    
    $.ajax({
        url: `/sales/${saleId}/details`,
        method: 'GET',
        dataType: 'json',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success) {
                $('#viewSaleModalBody').html(response.html);
            } else {
                $('#viewSaleModalBody').html('<div class="alert alert-danger">Error: ' + response.message + '</div>');
            }
        },
        error: function(xhr) {
            console.error('Error loading details:', xhr);
            $('#viewSaleModalBody').html('<div class="alert alert-danger">Error de conexión al cargar detalles</div>');
        }
    });
}

// 2. Editar Venta
function editSale(saleId) {
    $('#editSaleModal').modal('show');
    $('#editSaleModalBody').html('<div class="text-center p-4"><div class="spinner-border"></div><p class="mt-2">Cargando formulario...</p></div>');
    
    $.ajax({
        url: `/sales/${saleId}/edit-modal`,
        method: 'GET',
        dataType: 'json',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success) {
                $('#editSaleModalBody').html(response.html);
            } else {
                $('#editSaleModalBody').html('<div class="alert alert-danger">Error: ' + response.message + '</div>');
            }
        },
        error: function(xhr) {
            console.error('Error loading edit form:', xhr);
            $('#editSaleModalBody').html('<div class="alert alert-danger">Error de conexión al cargar formulario</div>');
        }
    });
}

function updateSale() {
    const formData = new FormData($('#editSaleForm')[0]);
    const saleId = formData.get('sale_id');
    
    $('#updateSaleBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
    
    $.ajax({
        url: `/sales/${saleId}/update-modal`,
        method: 'PUT',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCSRFToken()
        },
        success: function(response) {
            if (response.success) {
                showToast('success', response.message);
                $('#editSaleModal').modal('hide');
                reloadTable();
            } else {
                showToast('error', response.message);
            }
        },
        error: function(xhr) {
            console.error('Error updating sale:', xhr);
            showToast('error', 'Error de conexión al actualizar');
        },
        complete: function() {
            $('#updateSaleBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Actualizar Venta');
        }
    });
}

// 3. Confirmar Venta
function confirmSale(saleId) {
    Swal.fire({
        title: '¿Confirmar venta?',
        text: 'La venta será marcada como confirmada.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#007bff',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, confirmar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            updateSaleStatus(saleId, 'confirmed');
        }
    });
}

// 4. Entregar Venta
function deliverSale(saleId) {
    Swal.fire({
        title: '¿Marcar como entregada?',
        text: 'La venta será marcada como entregada.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, entregar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            updateSaleStatus(saleId, 'delivered');
        }
    });
}

// 5. Cancelar Venta
function cancelSale(saleId) {
    Swal.fire({
        title: '¿Cancelar venta?',
        text: 'Esta acción restaurará el inventario asociado.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            updateSaleStatus(saleId, 'cancelled');
        }
    });
}

function updateSaleStatus(saleId, newStatus) {
    $.ajax({
        url: `/sales/${saleId}/status`,
        method: 'PATCH',
        data: { status: newStatus },
        dataType: 'json',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCSRFToken()
        },
        success: function(response) {
            if (response.success) {
                showToast('success', response.message);
                reloadTable();
            } else {
                showToast('error', response.message);
            }
        },
        error: function(xhr) {
            console.error('Error updating status:', xhr);
            showToast('error', 'Error de conexión al actualizar estado');
        }
    });
}

// 6. Registrar Pago
function registerPayment(saleId) {
    $('#paymentModal').modal('show');
    $('#paymentModalBody').html('<div class="text-center p-4"><div class="spinner-border"></div><p class="mt-2">Cargando formulario...</p></div>');
    
    $.ajax({
        url: `/sales/${saleId}/payment-form`,
        method: 'GET',
        dataType: 'json',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success) {
                $('#paymentModalBody').html(response.html);
            } else {
                $('#paymentModalBody').html('<div class="alert alert-danger">Error: ' + response.message + '</div>');
            }
        },
        error: function(xhr) {
            console.error('Error loading payment form:', xhr);
            $('#paymentModalBody').html('<div class="alert alert-danger">Error de conexión al cargar formulario</div>');
        }
    });
}

function savePayment() {
    const formData = new FormData($('#paymentForm')[0]);
    
    $('#savePaymentBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
    
    $.ajax({
        url: '/payments/sale-payment',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCSRFToken()
        },
        success: function(response) {
            if (response.success) {
                showToast('success', response.message);
                $('#paymentModal').modal('hide');
                reloadTable();
            } else {
                showToast('error', response.message);
            }
        },
        error: function(xhr) {
            console.error('Error saving payment:', xhr);
            showToast('error', 'Error de conexión al guardar pago');
        },
        complete: function() {
            $('#savePaymentBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Registrar Pago');
        }
    });
}

// 7. Imprimir Factura
function printInvoice(saleId) {
    window.open(`/sales/${saleId}/invoice`, '_blank');
}

// 8. Eliminar Venta
function deleteSale(saleId) {
    Swal.fire({
        title: '¿Eliminar venta?',
        text: 'Esta acción eliminará permanentemente la venta y no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/sales/${saleId}`,
                method: 'DELETE',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCSRFToken()
                },
                success: function(response) {
                    if (response.success) {
                        showToast('success', response.message);
                        reloadTable();
                    } else {
                        showToast('error', response.message);
                    }
                },
                error: function(xhr) {
                    console.error('Error deleting sale:', xhr);
                    showToast('error', 'Error de conexión al eliminar');
                }
            });
        }
    });
}
</script>
@endsection