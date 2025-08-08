@extends('layouts.admin')

@section('title', 'Gestión de Lotes')
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
                                    @foreach($qualityGrades as $quality)
                                        <option value="{{ $quality->name }}" {{ request('quality') == $quality->name ? 'selected' : '' }}>
                                            {{ $quality->name }}
                                        </option>
                                    @endforeach
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
                                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#lotModal" onclick="openCreateLotModal()">
                                        <i class="fas fa-plus"></i> Nuevo Lote
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row" id="statsRow">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="totalLots">{{ $stats['total'] ?? 0 }}</h3>
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
                    <h3 id="activeLots">{{ $stats['active'] ?? 0 }}</h3>
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
                    <h3 id="totalWeight">{{ $stats['weight'] ?? '0.00' }} kg</h3>
                    <p>Peso Total</p>
                </div>
                <div class="icon">
                    <i class="fas fa-weight"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="totalValue">${{ $stats['value'] ?? '0.00' }}</h3>
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
                    <h3 class="card-title"><i class="fas fa-table"></i> Lista de Lotes</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table id="lotsTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Proveedor</th>
                                <th>Fecha Cosecha</th>
                                <th>Peso Total</th>
                                <th>Disponible</th>
                                <th>Calidad</th>
                                <th>Estado</th>
                                <th>Precio/kg</th>
                                <th>Valor Total</th>
                                <th>Acciones</th>
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

    <!-- Create/Edit Lot Modal -->
    <div class="modal fade" id="lotModal" tabindex="-1" aria-labelledby="lotModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="lotModalTitle">Nuevo Lote</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="lotForm" data-mode="create">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="supplier_id">Proveedor</label>
                                    <select name="supplier_id" id="supplier_id" class="form-control">
                                        <option value="">🕶️ Anónimo</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="codigo">Código <span class="text-danger">*</span></label>
                                    <input type="text" name="codigo" id="codigo" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="peso_inicial">Peso Inicial (kg) <span class="text-danger">*</span></label>
                                    <input type="number" name="peso_inicial" id="peso_inicial" class="form-control" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="precio_compra">Precio Compra <span class="text-danger">*</span></label>
                                    <input type="number" name="precio_compra" id="precio_compra" class="form-control" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="precio_venta_sugerido">Precio Venta Sugerido</label>
                                    <input type="number" name="precio_venta_sugerido" id="precio_venta_sugerido" class="form-control" step="0.01">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="calidad">Calidad <span class="text-danger">*</span></label>
                                    <select name="calidad" id="calidad" class="form-control" required>
                                        <option value="">Seleccionar calidad</option>
                                        @foreach($qualityGrades as $quality)
                                            <option value="{{ $quality->name }}" title="{{ $quality->description }}">
                                                {{ $quality->name }}
                                                @if($quality->caliber_range || $quality->weight_range)
                                                    ({{ $quality->caliber_range }} - {{ $quality->weight_range }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_compra">Fecha de Compra <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha_compra" id="fecha_compra" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="notas">Notas</label>
                                    <textarea name="notas" id="notas" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Estadísticas en tiempo real -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card card-info">
                                    <div class="card-header">
                                        <h3 class="card-title">📊 Estadísticas del Lote</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="info-box bg-info">
                                                    <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Costo Total</span>
                                                        <span class="info-box-number" id="total-cost">$0.00</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-box bg-success">
                                                    <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Ganancia Potencial</span>
                                                        <span class="info-box-number" id="potential-profit">
                                                            <button type="button" class="btn btn-sm btn-outline-light" onclick="toggleSensitiveData()">
                                                                <i class="fas fa-eye-slash"></i> Mostrar
                                                            </button>
                                                        </span>
                                                        <div id="profit-details" style="display: none;">
                                                            <small class="text-light" id="profit-amount">$0.00</small><br>
                                                            <small class="text-light" id="profit-margin">0% margen</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Lot Report Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white" id="reportModalTitle">Reporte de Lote</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="reportContent">
                        <!-- Report content will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-info" onclick="printLotReport()">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                    <button type="button" class="btn btn-danger" onclick="downloadLotPDF(currentLotId)">
                        <i class="fas fa-file-pdf"></i> Descargar PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let currentLotId = null;
let lotsTable;

$(document).ready(function() {
    // Initialize DataTables
    lotsTable = $('#lotsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('lots.index') }}',
            data: function (d) {
                // Add filter data
                d.status = $('#filterForm select[name="status"]').val();
                d.quality = $('#filterForm select[name="quality"]').val();
                d.supplier_id = $('#filterForm select[name="supplier_id"]').val();
                d.date_from = $('#filterForm input[name="date_from"]').val();
                d.date_to = $('#filterForm input[name="date_to"]').val();
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables Ajax error:', error, thrown);
                toastr.error('Error al cargar los datos');
            }
        },
        columns: [
            {
                data: 'lot_code',
                name: 'lot_code',
                render: function(data, type, row) {
                    return `<strong class="text-primary">${data}</strong><br><small class="text-muted">${new Date(row.created_at).toLocaleDateString('es-ES')}</small>`;
                }
            },
            {
                data: 'supplier',
                name: 'supplier.name',
                render: function(data, type, row) {
                    if (data && data.name) {
                        return `<strong>${data.name}</strong>${data.city ? `<br><small class="text-muted"><i class="fas fa-map-marker-alt"></i> ${data.city}</small>` : ''}`;
                    }
                    return '<span class="text-muted"><i class="fas fa-user-secret"></i> 🕶️ Anónimo</span>';
                }
            },
            {
                data: 'harvest_date',
                name: 'harvest_date',
                render: function(data) {
                    return `<span class="badge badge-info">${new Date(data).toLocaleDateString('es-ES')}</span>`;
                }
            },
            {
                data: 'total_weight',
                name: 'total_weight',
                render: function(data) {
                    return `<strong>${parseFloat(data).toFixed(2)} kg</strong>`;
                }
            },
            {
                data: 'weight_available',
                name: 'weight_available',
                render: function(data) {
                    const badgeClass = parseFloat(data) > 0 ? 'badge-success' : 'badge-secondary';
                    return `<span class="badge ${badgeClass}">${parseFloat(data).toFixed(2)} kg</span>`;
                }
            },
            {
                data: 'quality_grade',
                name: 'quality_grade',
                render: function(data) {
                    const qualityMap = {
                        'Primera': '<span class="badge badge-success"><i class="fas fa-star"></i> Primera</span>',
                        'Segunda': '<span class="badge badge-warning"><i class="fas fa-star-half-alt"></i> Segunda</span>',
                        'Tercera': '<span class="badge badge-danger"><i class="far fa-star"></i> Tercera</span>'
                    };
                    return qualityMap[data] || `<span class="badge badge-secondary">${data}</span>`;
                }
            },
            {
                data: 'status',
                name: 'status',
                render: function(data) {
                    const statusMap = {
                        'active': '<span class="badge badge-primary"><i class="fas fa-check-circle"></i> Activo</span>',
                        'partial': '<span class="badge badge-warning"><i class="fas fa-clock"></i> Parcial</span>',
                        'sold': '<span class="badge badge-success"><i class="fas fa-handshake"></i> Vendido</span>'
                    };
                    return statusMap[data] || `<span class="badge badge-secondary">${data}</span>`;
                }
            },
            {
                data: 'purchase_price_per_kg',
                name: 'purchase_price_per_kg',
                render: function(data) {
                    return `<strong class="text-success">$${parseFloat(data).toFixed(2)}</strong>`;
                }
            },
            {
                data: 'total_purchase_cost',
                name: 'total_purchase_cost',
                render: function(data) {
                    return `<strong class="text-primary">$${parseFloat(data).toFixed(2)}</strong>`;
                }
            },
            {
                data: 'id',
                name: 'actions',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-info" onclick="openViewLotModal(${data})" title="Ver reporte">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-primary" onclick="openEditLotModal(${data})" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu">
                                <button class="dropdown-item" onclick="downloadLotPDF(${data})">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </button>
                                <button class="dropdown-item text-danger" onclick="deleteLot(${data}, '${row.lot_code}')">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    `;
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        responsive: true,
        order: [[2, 'desc']], // Order by harvest_date desc
        pageLength: 20,
        lengthMenu: [[2, 10, 20, 50, 100], [2, 10, 20, 50, 100]],
        drawCallback: function() {
            $('[data-toggle="tooltip"]').tooltip();
            // Update statistics after each draw
            updateStats();
        }
    });

    // Apply filters when changed
    $('#filterForm select, #filterForm input').on('change', function() {
        lotsTable.draw();
    });
});

function updateStats() {
    // Simple stats update - you can enhance this
    const info = lotsTable.page.info();
    $('#totalLots').text(info.recordsTotal);
}

// Wrapper functions for actions to match table calls
function viewLotReport(id) {
    openViewLotModal(id);
}

function editLot(id) {
    openEditLotModal(id);
}

function downloadPDF(id) {
    downloadLotPDF(id);
}

function openEditLotModal(id) {
    console.log('Opening edit lot modal for ID:', id);
    $('#lotModalTitle').text('Editar Lote');
    $('#lotForm').attr('data-mode', 'edit');
    $('#lotForm').attr('data-id', id);

    // Load lot data
    fetch(`{{ url('lots') }}/${id}`, {
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
        console.log('Precio venta sugerido:', data.precio_venta_sugerido);
        
        // Populate form fields
        $('#supplier_id').val(data.supplier_id || '');
        $('#codigo').val(data.codigo || '');
        $('#peso_inicial').val(data.peso_inicial || '');
        $('#precio_compra').val(data.precio_compra || '');
        $('#precio_venta_sugerido').val(data.precio_venta_sugerido || '');
        $('#calidad').val(data.calidad || '');
        $('#notas').val(data.notas || '');
        $('#fecha_compra').val(data.fecha_compra || '');

        console.log('Form field set with value:', $('#precio_venta_sugerido').val());

        // Update statistics after loading data
        setTimeout(updateLotStatistics, 100);

        $('#lotModal').modal('show');
    })
    .catch(error => {
        console.error('Error loading lot data:', error);
        toastr.error('Error al cargar los datos del lote: ' + error.message);
    });
}

function openViewLotModal(id) {
    console.log('Opening view lot modal for ID:', id);
    currentLotId = id;
    
    fetch(`{{ url('lots') }}/${id}/report`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('Report response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Report data received:', data);
        if (data.html) {
            $('#reportContent').html(data.html);
            $('#reportModal').modal('show');
        } else if (data.error) {
            console.error('Server error:', data.error);
            toastr.error('Error del servidor: ' + data.error);
        } else {
            console.error('No HTML content in response');
            toastr.error('No se recibió contenido del reporte');
        }
    })
    .catch(error => {
        console.error('Error loading report:', error);
        toastr.error('Error al cargar el reporte: ' + error.message);
    });
}

function deleteLot(id, codigo) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: `¿Deseas eliminar el lote ${codigo}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`{{ url('lots') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success('Lote eliminado correctamente');
                    lotsTable.draw();
                } else {
                    toastr.error(data.message || 'Error al eliminar el lote');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('Error al eliminar el lote');
            });
        }
    });
}

function downloadLotPDF(id) {
    const url = `{{ url('lots') }}/${id}/pdf`;
    console.log('Downloading PDF from:', url);
    window.open(url, '_blank');
}

function printLotReport() {
    // Obtener datos del lote actual desde el modal
    const lotCode = $('#reportModalTitle').text().replace('Reporte del Lote ', '');
    const lotData = extractLotDataFromModal();
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Ticket - Lote ${lotCode}</title>
            <style>
                /* Configuración para impresora térmica */
                @page {
                    size: 80mm auto;
                    margin: 0;
                }
                
                body {
                    font-family: 'Courier New', monospace;
                    font-size: 12px;
                    line-height: 1.2;
                    margin: 0;
                    padding: 5mm;
                    width: 70mm;
                    color: #000;
                }
                
                .center {
                    text-align: center;
                }
                
                .bold {
                    font-weight: bold;
                }
                
                .header {
                    text-align: center;
                    border-bottom: 1px dashed #000;
                    padding-bottom: 5px;
                    margin-bottom: 8px;
                }
                
                .company {
                    font-size: 14px;
                    font-weight: bold;
                    margin-bottom: 2px;
                }
                
                .ticket-title {
                    font-size: 13px;
                    font-weight: bold;
                    margin: 3px 0;
                }
                
                .section {
                    margin-bottom: 8px;
                    border-bottom: 1px dashed #ccc;
                    padding-bottom: 5px;
                }
                
                .row {
                    display: flex;
                    justify-content: space-between;
                    margin: 1px 0;
                }
                
                .label {
                    font-weight: bold;
                    width: 50%;
                }
                
                .value {
                    text-align: right;
                    width: 50%;
                }
                
                .separator {
                    border-top: 1px dashed #000;
                    margin: 8px 0;
                }
                
                .footer {
                    text-align: center;
                    font-size: 10px;
                    margin-top: 10px;
                }
                
                .status {
                    text-align: center;
                    font-weight: bold;
                    padding: 2px;
                    margin: 3px 0;
                }
                
                .progress-bar {
                    width: 100%;
                    height: 8px;
                    background: #ddd;
                    margin: 2px 0;
                }
                
                .progress-fill {
                    height: 100%;
                    background: #000;
                }
                
                @media print {
                    body {
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="company">AVOCONTROL PRO</div>
                <div class="ticket-title">REPORTE DE LOTE</div>
                <div>${new Date().toLocaleString('es-ES')}</div>
            </div>
            
            <div class="section">
                <div class="center bold">LOTE: ${lotCode}</div>
            </div>
            
            <div class="section">
                <div class="row">
                    <span class="label">Proveedor:</span>
                    <span class="value">${lotData.supplier || 'Anónimo'}</span>
                </div>
                <div class="row">
                    <span class="label">Fecha:</span>
                    <span class="value">${lotData.date}</span>
                </div>
                <div class="row">
                    <span class="label">Calidad:</span>
                    <span class="value">${lotData.quality}</span>
                </div>
                <div class="row">
                    <span class="label">Estado:</span>
                    <span class="value">${lotData.status}</span>
                </div>
            </div>
            
            <div class="section">
                <div class="center bold">PESOS</div>
                <div class="row">
                    <span class="label">Total:</span>
                    <span class="value">${lotData.totalWeight}</span>
                </div>
                <div class="row">
                    <span class="label">Vendido:</span>
                    <span class="value">${lotData.soldWeight}</span>
                </div>
                <div class="row">
                    <span class="label">Disponible:</span>
                    <span class="value">${lotData.availableWeight}</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: ${lotData.soldPercentage}%"></div>
                </div>
                <div class="center">Vendido: ${lotData.soldPercentage}%</div>
            </div>
            
            <div class="section">
                <div class="center bold">FINANZAS</div>
                <div class="row">
                    <span class="label">Precio/kg:</span>
                    <span class="value">$${lotData.pricePerKg}</span>
                </div>
                <div class="row">
                    <span class="label">Costo Total:</span>
                    <span class="value">$${lotData.totalCost}</span>
                </div>
                <div class="row">
                    <span class="label">Ingresos:</span>
                    <span class="value">$${lotData.revenue}</span>
                </div>
                <div class="separator"></div>
                <div class="row">
                    <span class="label">GANANCIA ACTUAL:</span>
                    <span class="value bold">$${lotData.profit}</span>
                </div>
            </div>
            
            ${lotData.suggestedPrice ? `
            <div class="section">
                <div class="center bold">PROYECCION</div>
                <div class="row">
                    <span class="label">Precio Sugerido:</span>
                    <span class="value">$${lotData.suggestedPrice}/kg</span>
                </div>
                <div class="row">
                    <span class="label">Ingresos Potenc.:</span>
                    <span class="value">$${lotData.potentialRevenue}</span>
                </div>
                <div class="separator"></div>
                <div class="row">
                    <span class="label">GANANCIA POTENC.:</span>
                    <span class="value bold">$${lotData.potentialProfit}</span>
                </div>
                <div class="center">Margen: ${lotData.potentialMargin}%</div>
            </div>
            ` : ''}
            
            ${lotData.notes ? `
            <div class="section">
                <div class="center bold">NOTAS</div>
                <div style="text-align: left; font-size: 10px;">
                    ${lotData.notes}
                </div>
            </div>
            ` : ''}
            
            <div class="footer">
                <div>AvoControl Pro v1.0</div>
                <div>Sistema de Gestión de Aguacates</div>
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 500);
}

function extractLotDataFromModal() {
    // Extraer datos del modal actual para el ticket
    const reportContent = document.getElementById('reportContent');
    
    // Buscar el precio sugerido y datos potenciales
    const suggestedPriceRow = $(reportContent).find('table:eq(2) tr').filter(function() {
        return $(this).find('td:first').text().includes('Precio Venta Sugerido');
    });
    const potentialRevenueRow = $(reportContent).find('table:eq(2) tr').filter(function() {
        return $(this).find('td:first').text().includes('Ingresos Potenciales');
    });
    const potentialProfitRow = $(reportContent).find('table:eq(2) tr').filter(function() {
        return $(this).find('td:first').text().includes('Ganancia Potencial');
    });
    
    return {
        supplier: $(reportContent).find('table:first tr:nth-child(2) td:nth-child(2)').text().trim() || 'Anónimo',
        date: $(reportContent).find('table:first tr:nth-child(3) td:nth-child(2)').text().trim(),
        quality: $(reportContent).find('table:first tr:nth-child(4) td:nth-child(2)').text().trim(),
        status: $(reportContent).find('table:first tr:nth-child(5) td:nth-child(2)').text().trim(),
        totalWeight: $(reportContent).find('table:eq(1) tr:nth-child(1) td:nth-child(2)').text().trim(),
        soldWeight: $(reportContent).find('table:eq(1) tr:nth-child(2) td:nth-child(2)').text().trim(),
        availableWeight: $(reportContent).find('table:eq(1) tr:nth-child(3) td:nth-child(2)').text().trim(),
        soldPercentage: $(reportContent).find('table:eq(1) tr:nth-child(4) td:nth-child(2)').text().replace('%', '').trim(),
        pricePerKg: $(reportContent).find('table:eq(2) tr:nth-child(1) td:nth-child(2)').text().replace('$', '').trim(),
        totalCost: $(reportContent).find('table:eq(2) tr:nth-child(2) td:nth-child(2)').text().replace('$', '').trim(),
        revenue: $(reportContent).find('table:eq(2) tr:nth-child(3) td:nth-child(2)').text().replace('$', '').trim(),
        profit: $(reportContent).find('table:eq(2) tr:nth-child(4) td:nth-child(2)').text().replace(/[^0-9.-]/g, '').trim(),
        suggestedPrice: suggestedPriceRow.length ? suggestedPriceRow.find('td:nth-child(2)').text().replace(/[^0-9.]/g, '').trim() : null,
        potentialRevenue: potentialRevenueRow.length ? potentialRevenueRow.find('td:nth-child(2)').text().replace(/[^0-9.-]/g, '').trim() : null,
        potentialProfit: potentialProfitRow.length ? potentialProfitRow.find('td:nth-child(2)').text().replace(/[^0-9.-]/g, '').trim() : null,
        potentialMargin: potentialProfitRow.length ? potentialProfitRow.find('small').text().replace(/[^0-9.-]/g, '').trim() : null,
        notes: $(reportContent).find('.alert').text().trim() || null
    };
}

// Form submission
$('#lotForm').submit(function(e) {
    e.preventDefault();

    const mode = $(this).attr('data-mode');
    const id = $(this).attr('data-id');
    const formData = new FormData(this);

    let url = '{{ route("lots.store") }}';

    if (mode === 'edit') {
        url = `{{ url('lots') }}/${id}`;
        formData.append('_method', 'PUT');
    }

    fetch(url, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            toastr.success(mode === 'create' ? 'Lote creado correctamente' : 'Lote actualizado correctamente');
            $('#lotModal').modal('hide');
            lotsTable.draw();
        } else {
            if (data.errors) {
                Object.keys(data.errors).forEach(key => {
                    toastr.error(data.errors[key][0]);
                });
            } else {
                toastr.error(data.message || 'Error al guardar el lote');
                console.error('Server error details:', data);
            }
        }
    })
    .catch(error => {
        console.error('Error details:', error);
        toastr.error('Error al guardar el lote: ' + error.message);
    });
});

// Calculate statistics in real-time
function updateLotStatistics() {
    const peso = parseFloat($('#peso_inicial').val()) || 0;
    const precioCompra = parseFloat($('#precio_compra').val()) || 0;
    const precioVenta = parseFloat($('#precio_venta_sugerido').val()) || 0;

    const costoTotal = peso * precioCompra;
    const ingresoTotal = peso * precioVenta;
    const ganancia = ingresoTotal - costoTotal;
    const margen = costoTotal > 0 ? ((ganancia / costoTotal) * 100) : 0;

    $('#total-cost').text('$' + costoTotal.toLocaleString('es-MX', {minimumFractionDigits: 2}));
    $('#profit-amount').text('$' + ganancia.toLocaleString('es-MX', {minimumFractionDigits: 2}));
    $('#profit-margin').text(margen.toFixed(1) + '% margen');

    // Change color based on profitability - ONLY within the modal
    const profitBox = $('#lotModal .info-box.bg-success, #lotModal .info-box.bg-danger');
    if (ganancia < 0) {
        profitBox.removeClass('bg-success').addClass('bg-danger');
    } else {
        profitBox.removeClass('bg-danger').addClass('bg-success');
    }
}

// Toggle sensitive data visibility
function toggleSensitiveData() {
    const details = $('#profit-details');
    const button = $('#potential-profit button');
    const icon = button.find('i');

    if (details.is(':visible')) {
        details.hide();
        icon.removeClass('fa-eye').addClass('fa-eye-slash');
        button.html('<i class="fas fa-eye-slash"></i> Mostrar');
    } else {
        details.show();
        icon.removeClass('fa-eye-slash').addClass('fa-eye');
        button.html('<i class="fas fa-eye"></i> Ocultar');
    }
}

// Bind real-time calculation events
$(document).on('input', '#peso_inicial, #precio_compra, #precio_venta_sugerido', function() {
    updateLotStatistics();
});

// Reset statistics when opening create modal
function openCreateLotModal() {
    $('#lotModalTitle').text('Nuevo Lote');
    $('#lotForm')[0].reset();
    $('#lotForm').attr('data-mode', 'create');
    $('#lotForm').attr('data-id', '');

    // Set default date to today
    const today = new Date().toISOString().split('T')[0];
    $('#fecha_compra').val(today);

    // Reset statistics
    $('#total-cost').text('$0.00');
    $('#profit-amount').text('$0.00');
    $('#profit-margin').text('0% margen');
    $('#profit-details').hide();
    $('#potential-profit button').html('<i class="fas fa-eye-slash"></i> Mostrar');
    // Only reset colors within the modal
    $('#lotModal .info-box.bg-danger').removeClass('bg-danger').addClass('bg-success');

    $('#lotModal').modal('show');
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
