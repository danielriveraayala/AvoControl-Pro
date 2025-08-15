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
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Load initial data
    loadSales();

});

function loadSales(page = 1) {
    console.log('loadSales called with page:', page);
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
        console.log('loadSales response:', data);
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

// ===============================
// NUEVA VENTA MODAL FUNCTIONS
// ===============================

// Variables globales para el modal de ventas
let saleModalInventory = {};
let saleModalItemCount = 0;
let saleModalCustomers = [];

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
        const badgeClass = {
            'Primera': 'success',
            'Segunda': 'warning',
            'Tercera': 'info',
            'Industrial': 'secondary'
        }[inv.quality_grade] || 'secondary';

        return `
            <div class="col-md-3">
                <div class="text-center">
                    <span class="badge badge-${badgeClass} badge-lg d-block mb-1">
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
    $(`#modal-available-${itemId}`).text(`Disponible: ${available.toFixed(2)} kg`);

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
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message || 'Venta registrada exitosamente');
            $('#newSaleModal').modal('hide');

            // Wait a moment for modal to close, then refresh the sales list
            setTimeout(() => {
                loadSales(); // Refresh the sales list
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
        console.error('Error:', error);
        toastr.error(error.message || 'Error al registrar la venta');
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
