@extends('layouts.admin')

@section('title', 'Nueva Venta por Acopio')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">🛒 Nueva Venta por Acopio</h1>
                <p class="text-muted">Vender por calidad del inventario total</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">Ventas</a></li>
                    <li class="breadcrumb-item active">Nueva Venta</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <form method="POST" action="{{ route('sales.store') }}" id="saleForm">
            @csrf

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
                                <label for="customer_id">Cliente *</label>
                                <select name="customer_id" id="customer_id" class="form-control select2" required>
                                    <option value="">Seleccione un cliente</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" 
                                               {{ old('customer_id') == $customer->id ? 'selected' : '' }}
                                               data-phone="{{ $customer->phone }}"
                                               data-address="{{ $customer->address }}">
                                            {{ $customer->name }} - {{ $customer->contact_person }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted" id="customer-info"></small>
                            </div>

                            <!-- Fecha de Venta -->
                            <div class="form-group">
                                <label for="sale_date">Fecha de Venta *</label>
                                <input type="date" name="sale_date" id="sale_date" class="form-control" 
                                       value="{{ old('sale_date', date('Y-m-d')) }}" 
                                       max="{{ date('Y-m-d') }}" required>
                                @error('sale_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Fecha de Entrega -->
                            <div class="form-group">
                                <label for="delivery_date">Fecha de Entrega</label>
                                <input type="date" name="delivery_date" id="delivery_date" class="form-control" 
                                       value="{{ old('delivery_date') }}">
                            </div>

                            <!-- Número de Factura -->
                            <div class="form-group">
                                <label for="invoice_number">Número de Factura</label>
                                <input type="text" name="invoice_number" id="invoice_number" class="form-control" 
                                       value="{{ old('invoice_number') }}" placeholder="Opcional">
                            </div>

                            <!-- Notas -->
                            <div class="form-group">
                                <label for="notes">Notas</label>
                                <textarea name="notes" id="notes" class="form-control" rows="3" 
                                         placeholder="Notas adicionales de la venta">{{ old('notes') }}</textarea>
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
                                <button type="button" class="btn btn-success btn-sm" onclick="addQualityItem()">
                                    <i class="fas fa-plus"></i> Agregar Calidad
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            
                            <!-- Inventario Disponible -->
                            <div class="alert alert-info m-3">
                                <h5><i class="icon fas fa-info-circle"></i> Inventario Disponible</h5>
                                <div class="row" id="inventory-summary">
                                    @foreach($inventario as $inv)
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            @php
                                                $badgeClass = match($inv->quality_grade) {
                                                    'Primera' => 'success',
                                                    'Segunda' => 'warning', 
                                                    'Tercera' => 'info',
                                                    'Industrial' => 'secondary',
                                                    default => 'secondary'
                                                };
                                            @endphp
                                            <span class="badge badge-{{ $badgeClass }} badge-lg d-block mb-1">
                                                {{ $inv->quality_grade }}
                                            </span>
                                            <strong class="d-block">{{ number_format($inv->peso_disponible, 2) }} kg</strong>
                                            <small class="text-muted">disponible</small>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Container de Items -->
                            <div id="items-container" class="p-3">
                                <!-- Los items se agregarán aquí dinámicamente -->
                                <div class="text-center text-muted py-4" id="no-items-message">
                                    <i class="fas fa-shopping-cart fa-3x mb-2"></i>
                                    <p>No hay items agregados. Haga clic en "Agregar Calidad" para comenzar.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen y Botones -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <!-- Resumen -->
                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-3 text-center">
                                            <div class="description-block border-right">
                                                <span class="description-percentage text-success">
                                                    <i class="fas fa-weight"></i>
                                                </span>
                                                <h5 class="description-header" id="totalWeight">0.00</h5>
                                                <span class="description-text">KG TOTAL</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <div class="description-block border-right">
                                                <span class="description-percentage text-info">
                                                    <i class="fas fa-layer-group"></i>
                                                </span>
                                                <h5 class="description-header" id="totalItems">0</h5>
                                                <span class="description-text">CALIDADES</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <div class="description-block border-right">
                                                <span class="description-percentage text-warning">
                                                    <i class="fas fa-dollar-sign"></i>
                                                </span>
                                                <h5 class="description-header" id="totalAmount">$0.00</h5>
                                                <span class="description-text">MONTO TOTAL</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <div class="description-block">
                                                <span class="description-percentage text-primary">
                                                    <i class="fas fa-calculator"></i>
                                                </span>
                                                <h5 class="description-header" id="avgPrice">$0.00</h5>
                                                <span class="description-text">PRECIO PROM.</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Botones -->
                                <div class="col-md-4">
                                    <div class="d-flex flex-column h-100 justify-content-center">
                                        <button type="submit" class="btn btn-success btn-lg mb-2" id="submitBtn" disabled>
                                            <i class="fas fa-save"></i> Registrar Venta
                                        </button>
                                        <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Cancelar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

<script>
// Inventario disponible (desde PHP)
const inventory = @json($inventario->keyBy('quality_grade'));

// Contador de items
let itemCount = 0;

// Inicializar al cargar la página
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap4',
        placeholder: 'Seleccione...',
        allowClear: true
    });
    
    // Mostrar información del cliente
    $('#customer_id').change(function() {
        const option = $(this).find('option:selected');
        const phone = option.data('phone');
        const address = option.data('address');
        
        let info = '';
        if (phone) info += `Teléfono: ${phone}`;
        if (address) info += (info ? ' | ' : '') + `Dirección: ${address}`;
        
        $('#customer-info').text(info);
    });

    // Verificar si se pasó calidad por URL
    const urlParams = new URLSearchParams(window.location.search);
    const preSelectedQuality = urlParams.get('calidad');
    if (preSelectedQuality && inventory[preSelectedQuality]) {
        addQualityItem(preSelectedQuality);
    }
});

function addQualityItem(preSelectedQuality = null) {
    const container = $('#items-container');
    const noItemsMsg = $('#no-items-message');
    
    // Ocultar mensaje de no items
    noItemsMsg.hide();
    
    itemCount++;
    
    // Obtener calidades disponibles (que no estén ya seleccionadas)
    const usedQualities = [];
    $('.quality-select').each(function() {
        if ($(this).val()) {
            usedQualities.push($(this).val());
        }
    });
    
    const availableQualities = Object.keys(inventory).filter(q => 
        !usedQualities.includes(q) && inventory[q].peso_disponible > 0
    );
    
    if (availableQualities.length === 0 && !preSelectedQuality) {
        toastr.warning('No hay más calidades disponibles para agregar');
        return;
    }

    const itemHtml = `
        <div class="item-row card card-outline card-primary mb-3" id="item-${itemCount}">
            <div class="card-header">
                <h3 class="card-title">Item #${itemCount}</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool text-danger" onclick="removeItem(${itemCount})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Calidad *</label>
                            <select name="items[${itemCount}][quality_grade]" class="form-control quality-select" 
                                   onchange="updateQualityInfo(${itemCount}, this.value)" required>
                                <option value="">Seleccione calidad</option>
                                ${availableQualities.map(quality => `
                                    <option value="${quality}" 
                                           ${preSelectedQuality === quality ? 'selected' : ''}
                                           data-available="${inventory[quality].peso_disponible}">
                                        ${quality} (${inventory[quality].peso_disponible.toFixed(2)} kg disponible)
                                    </option>
                                `).join('')}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Peso (kg) *</label>
                            <input type="number" name="items[${itemCount}][weight]" 
                                  class="form-control weight-input" 
                                  min="0.01" step="0.01" 
                                  onchange="calculateItemTotal(${itemCount})" required>
                            <small class="form-text text-muted" id="available-${itemCount}"></small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Precio/kg *</label>
                            <input type="number" name="items[${itemCount}][price_per_kg]" 
                                  class="form-control price-input" 
                                  min="0.01" step="0.01" 
                                  onchange="calculateItemTotal(${itemCount})" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Subtotal</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="text" class="form-control subtotal-display" readonly>
                                <input type="hidden" name="items[${itemCount}][subtotal]" class="subtotal-input">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Estado</label>
                            <div class="text-center">
                                <span class="badge badge-secondary" id="status-${itemCount}">Incompleto</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.append(itemHtml);
    
    // Si hay calidad preseleccionada, actualizar información
    if (preSelectedQuality) {
        updateQualityInfo(itemCount, preSelectedQuality);
    }
    
    updateSummary();
}

function removeItem(itemId) {
    $(`#item-${itemId}`).remove();
    updateSummary();
    
    // Mostrar mensaje si no hay items
    if ($('.item-row').length === 0) {
        $('#no-items-message').show();
    }
}

function updateQualityInfo(itemId, quality) {
    if (!quality || !inventory[quality]) {
        $(`#available-${itemId}`).text('');
        return;
    }
    
    const available = inventory[quality].peso_disponible;
    $(`#available-${itemId}`).text(`Disponible: ${available.toFixed(2)} kg`);
    
    // Actualizar límite máximo del peso
    $(`#item-${itemId} .weight-input`).attr('max', available);
    
    calculateItemTotal(itemId);
}

function calculateItemTotal(itemId) {
    const itemRow = $(`#item-${itemId}`);
    const weight = parseFloat(itemRow.find('.weight-input').val()) || 0;
    const price = parseFloat(itemRow.find('.price-input').val()) || 0;
    const quality = itemRow.find('.quality-select').val();
    
    // Validar peso disponible
    if (quality && inventory[quality]) {
        const available = inventory[quality].peso_disponible;
        if (weight > available) {
            toastr.error(`No hay suficiente inventario. Disponible: ${available.toFixed(2)} kg`);
            itemRow.find('.weight-input').val(available);
            weight = available;
        }
    }
    
    const subtotal = weight * price;
    
    // Actualizar display
    itemRow.find('.subtotal-display').val(subtotal.toFixed(2));
    itemRow.find('.subtotal-input').val(subtotal);
    
    // Actualizar estado
    const statusBadge = $(`#status-${itemId}`);
    if (weight > 0 && price > 0 && quality) {
        statusBadge.removeClass('badge-secondary').addClass('badge-success').text('Completo');
    } else {
        statusBadge.removeClass('badge-success').addClass('badge-secondary').text('Incompleto');
    }
    
    updateSummary();
}

function updateSummary() {
    let totalWeight = 0;
    let totalAmount = 0;
    let totalItems = 0;
    let allComplete = true;
    
    $('.item-row').each(function() {
        const weight = parseFloat($(this).find('.weight-input').val()) || 0;
        const price = parseFloat($(this).find('.price-input').val()) || 0;
        const quality = $(this).find('.quality-select').val();
        
        if (weight > 0 && price > 0 && quality) {
            totalWeight += weight;
            totalAmount += (weight * price);
            totalItems++;
        } else {
            allComplete = false;
        }
    });
    
    const avgPrice = totalWeight > 0 ? totalAmount / totalWeight : 0;
    
    // Actualizar displays
    $('#totalWeight').text(totalWeight.toFixed(2));
    $('#totalAmount').text(totalAmount.toLocaleString('es-MX', {minimumFractionDigits: 2}));
    $('#totalItems').text(totalItems);
    $('#avgPrice').text('$' + avgPrice.toFixed(2));
    
    // Habilitar/deshabilitar botón submit
    const hasCustomer = $('#customer_id').val();
    const hasDate = $('#sale_date').val();
    
    $('#submitBtn').prop('disabled', !(allComplete && totalItems > 0 && hasCustomer && hasDate));
}

// Validar al cambiar cliente o fecha
$('#customer_id, #sale_date').change(updateSummary);
</script>

<style>
.badge-lg {
    padding: 0.5rem 0.75rem;
    font-size: 0.9rem;
}

.description-block {
    border-right: 1px solid rgba(0,0,0,.125);
}

.description-block:last-child {
    border-right: none;
}

.item-row {
    transition: all 0.3s;
}

.item-row:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,.1);
}
</style>
@endsection