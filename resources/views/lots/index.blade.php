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
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                                        Activo
                                    </option>
                                    <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>
                                        Parcial
                                    </option>
                                    <option value="sold" {{ request('status') == 'sold' ? 'selected' : '' }}>Vendido
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Calidad</label>
                                <select name="quality" class="form-control" onchange="loadLots()">
                                    <option value="">Todas</option>
                                    @foreach($qualityGrades as $quality)
                                        <option
                                            value="{{ $quality->name }}" {{ request('quality') == $quality->name ? 'selected' : '' }}>
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
                                        <option
                                            value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Desde</label>
                                <input type="date" name="date_from" value="{{ request('date_from') }}"
                                       class="form-control" onchange="loadLots()">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Hasta</label>
                                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control"
                                       onchange="loadLots()">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="d-block">
                                    <button type="button" class="btn btn-success" data-toggle="modal"
                                            data-target="#lotModal" onclick="openCreateLotModal()">
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

    <!-- Estadísticas Principales -->
    <div class="row" id="statsRow">
        <div class="col-lg-4 col-6">
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
        <div class="col-lg-4 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="availableWeight">{{ number_format($stats['available_weight'] ?? 0, 0) }}</h3>
                    <p>kg Disponibles</p>
                </div>
                <div class="icon">
                    <i class="fas fa-weight-hanging"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 id="soldWeight">{{ number_format($stats['sold_weight'] ?? 0, 0) }}</h3>
                    <p>kg Vendidos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-shipping-fast"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3 id="avgPurchasePrice">${{ number_format($stats['avg_purchase_price'] ?? 0, 2) }}</h3>
                    <p>Precio Promedio Compra</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3 id="totalInvestment">${{ number_format($stats['total_investment'] ?? 0, 0) }}</h3>
                    <p>Inversión Total</p>
                </div>
                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="pendingDebt">${{ number_format($stats['pending_debt'] ?? 0, 0) }}</h3>
                    <p>Saldo Pendiente</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas por Calidad -->
    <div class="row" id="qualityStatsRow">
        @if(isset($stats['quality_breakdown']) && count($stats['quality_breakdown']) > 0)
            @foreach($stats['quality_breakdown'] as $qualityStats)
                <div class="col-lg-3 col-6">
                    <div class="card card-outline
                        @switch($qualityStats['quality_name'])
                            @case('Primeras') card-success @break
                            @case('Segunda') card-warning @break
                            @case('Tercera') card-info @break
                            @case('Cuarta') card-primary @break
                            @default card-secondary
                        @endswitch
                    ">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-star"></i> {{ $qualityStats['quality_name'] }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="description-block">
                                        <h5 class="description-header">{{ $qualityStats['lots'] }}</h5>
                                        <span class="description-text">LOTES</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="description-block">
                                        <h5 class="description-header">{{ number_format($qualityStats['total_kg'], 0) }}</h5>
                                        <span class="description-text">KG TOTAL</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-6">
                                    <div class="description-block">
                                        <h5 class="description-header text-success">
                                            ${{ number_format($qualityStats['avg_price'], 2) }}</h5>
                                        <span class="description-text">PRECIO PROM.</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="description-block">
                                        <h5 class="description-header text-danger">
                                            ${{ number_format($qualityStats['total_value'], 0) }}</h5>
                                        <span class="description-text">INVERSIÓN</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
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
                            <th>Proveedor</th>
                            <th>Fecha Cosecha</th>
                            <th>Peso Total</th>
                            <th>Calidad</th>
                            <th>Precio/kg</th>
                            <th>Valor Total</th>
                            <th>Estado Pago</th>
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
                        <!-- Estadísticas en tiempo real -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card card-info">
                                    <div class="card-header">
                                        <h3 class="card-title">💰 Costo del Lote</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="info-box bg-info">
                                                    <span class="info-box-icon"><i
                                                            class="fas fa-dollar-sign"></i></span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Costo Total de Compra</span>
                                                        <span class="info-box-number" id="total-cost">$0.00</span>
                                                        <div class="progress">
                                                            <div class="progress-bar" style="width: 0%"></div>
                                                        </div>
                                                        <span class="progress-description">Peso: <span
                                                                id="total-weight">0 kg</span> × $<span
                                                                id="price-per-kg">0.00</span>/kg</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="peso_inicial">Peso Inicial (kg) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="peso_inicial" id="peso_inicial" class="form-control"
                                           step="0.01" min="0.01" max="9999999" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="precio_compra">Precio Compra <span class="text-danger">*</span></label>
                                    <input type="number" name="precio_compra" id="precio_compra" class="form-control"
                                           step="0.01" min="0.01" max="99999" required>
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
                                            <option value="{{ $quality->id }}" title="{{ $quality->description }}">
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
                                    <input type="date" name="fecha_compra" id="fecha_compra" class="form-control"
                                           required>
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

                        <!-- Sección de Pago Inicial -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card card-warning collapsed-card">
                                    <div class="card-header">
                                        <h3 class="card-title">💰 Pago Inicial (Opcional)</h3>
                                        <div class="card-tools">
                                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="monto_pago">Monto del Pago</label>
                                                    <input type="number" name="monto_pago" id="monto_pago"
                                                           class="form-control" step="0.01" min="0">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="fecha_pago">Fecha del Pago</label>
                                                    <input type="date" name="fecha_pago" id="fecha_pago"
                                                           class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="tipo_pago">Tipo de Pago</label>
                                                    <select name="tipo_pago" id="tipo_pago" class="form-control">
                                                        <option value="efectivo">Efectivo</option>
                                                        <option value="transferencia">Transferencia</option>
                                                        <option value="cheque">Cheque</option>
                                                        <option value="deposito">Depósito</option>
                                                        <option value="otro">Otro</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label for="notas_pago">Notas del Pago</label>
                                                    <textarea name="notas_pago" id="notas_pago" class="form-control"
                                                              rows="2"
                                                              placeholder="Referencia, número de transferencia, etc."></textarea>
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

    <!-- Payment Timeline Modal (Following Sales Pattern) -->
    <div class="modal fade" id="lotPaymentTimelineModal" tabindex="-1" aria-labelledby="lotPaymentTimelineModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h4 class="modal-title">
                        <i class="fas fa-history"></i>
                        Timeline de Pagos
                    </h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="lotPaymentTimelineModalBody">
                        <!-- Payment timeline content will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Lot Payment Modal (Following Sales Pattern) -->
    <div class="modal fade" id="lotPaymentModal" tabindex="-1" aria-labelledby="lotPaymentModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white" id="lotPaymentModalTitle">Registrar Pago</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="lotPaymentModalBody">
                        <!-- Payment form content will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let currentLotId = null;
        let lotsTable;

        $(document).ready(function () {
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
                    error: function (xhr, error, thrown) {
                        console.error('DataTables Ajax error:', error, thrown);
                        toastr.error('Error al cargar los datos');
                    }
                },
                columns: [
                    {
                        data: 'supplier',
                        name: 'supplier.name',
                        render: function (data, type, row) {
                            if (data && data.name) {
                                return `<strong>${data.name}</strong>${data.city ? `<br><small class="text-muted"><i class="fas fa-map-marker-alt"></i> ${data.city}</small>` : ''}`;
                            }
                            return '<span class="text-muted"><i class="fas fa-user-secret"></i> 🕶️ Anónimo</span>';
                        }
                    },
                    {
                        data: 'harvest_date',
                        name: 'harvest_date',
                        render: function (data) {
                            return `<span class="badge badge-info">${new Date(data).toLocaleDateString('es-ES')}</span>`;
                        }
                    },
                    {
                        data: 'total_weight',
                        name: 'total_weight',
                        render: function (data) {
                            return `<strong>${parseFloat(data).toFixed(2)} kg</strong>`;
                        }
                    },
                    {
                        data: 'quality_grade',
                        name: 'quality_grade',
                        render: function (data) {
                            const qualityMap = {
                                'Primera': '<span class="badge badge-success"><i class="fas fa-star"></i> Primera</span>',
                                'Segunda': '<span class="badge badge-warning"><i class="fas fa-star-half-alt"></i> Segunda</span>',
                                'Tercera': '<span class="badge badge-danger"><i class="far fa-star"></i> Tercera</span>'
                            };
                            return qualityMap[data] || `<span class="badge badge-secondary">${data}</span>`;
                        }
                    },
                    {
                        data: 'purchase_price_per_kg',
                        name: 'purchase_price_per_kg',
                        render: function (data) {
                            return `<strong class="text-success">$${parseFloat(data).toFixed(2)}</strong>`;
                        }
                    },
                    {
                        data: 'total_purchase_cost',
                        name: 'total_purchase_cost',
                        render: function (data) {
                            return `<strong class="text-primary">$${parseFloat(data).toFixed(2)}</strong>`;
                        }
                    },
                    {
                        data: 'payment_status',
                        name: 'payment_status',
                        render: function (data, type, row) {
                            const paymentStatusBadges = {
                                'paid': '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Pagado</span>',
                                'partial': '<span class="badge badge-warning"><i class="fas fa-clock"></i> Parcial</span>',
                                'pending': '<span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Pendiente</span>'
                            };

                            return paymentStatusBadges[data] || `<span class="badge badge-secondary">${data}</span>`;
                        }
                    },
                    {
                        data: 'id',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            return `
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-info" onclick="openViewLotModal(${data})" title="Ver reporte">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-primary" onclick="openEditLotModal(${data})" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-success" onclick="openLotPaymentTimeline(${data})" title="Timeline de Pagos">
                                <i class="fas fa-money-bill-wave"></i>
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
                responsive: true,
                order: [[1, 'desc']], // Order by harvest_date desc
                pageLength: 20,
                lengthMenu: [[2, 10, 20, 50, 100], [2, 10, 20, 50, 100]],
                drawCallback: function () {
                    $('[data-toggle="tooltip"]').tooltip();
                    // Update statistics after each draw
                    updateStats();
                }
            });

            // Apply filters when changed
            $('#filterForm select, #filterForm input').on('change', function () {
                loadLots();
            });

            // Initialize stats on page load
            updateStats();
        });

        function loadLots() {
            // Reload DataTables data
            lotsTable.draw();

            // Update statistics in real-time
            updateStats();
        }

        function updateStats() {
            console.log('Updating stats...');

            // Add loading indication
            $('#statsRow').addClass('loading-stats');

            // Safety timeout to remove loader after 5 seconds
            const safetyTimeout = setTimeout(() => {
                $('#statsRow').removeClass('loading-stats');
                console.warn('Stats update timeout - removing loader');
            }, 5000);

            // Get current filter values
            const formData = new URLSearchParams();
            formData.append('status', $('#filterForm select[name="status"]').val() || '');
            formData.append('quality', $('#filterForm select[name="quality"]').val() || '');
            formData.append('supplier_id', $('#filterForm select[name="supplier_id"]').val() || '');
            formData.append('date_from', $('#filterForm input[name="date_from"]').val() || '');
            formData.append('date_to', $('#filterForm input[name="date_to"]').val() || '');
            formData.append('ajax', '1');

            console.log('Fetching stats with params:', formData.toString());

            // Fetch updated stats
            fetch('{{ route("lots.index") }}?' + formData.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    console.log('Stats response status:', response.status);
                    clearTimeout(safetyTimeout); // Clear safety timeout on response

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Stats data received:', data);

                    // Remove loading indication first
                    $('#statsRow').removeClass('loading-stats');

                    if (data.stats) {
                        // Update main stats with smooth animation
                        updateStatValue('#totalLots', data.stats.total || 0);
                        updateStatValue('#availableWeight', Number(data.stats.available_weight || 0).toLocaleString());
                        updateStatValue('#soldWeight', Number(data.stats.sold_weight || 0).toLocaleString());
                        updateStatValue('#avgPurchasePrice', '$' + Number(data.stats.avg_purchase_price || 0).toFixed(2));
                        updateStatValue('#totalInvestment', '$' + Number(data.stats.total_investment || 0).toLocaleString());
                        updateStatValue('#pendingDebt', '$' + Number(data.stats.pending_debt || 0).toLocaleString());

                        // Update quality stats
                        if (data.stats.quality_breakdown) {
                            updateQualityStats(data.stats.quality_breakdown);
                        }

                        console.log('Stats updated successfully');
                    } else {
                        console.error('No stats in response:', data);
                    }
                })
                .catch(error => {
                    console.error('Error updating stats:', error);
                    clearTimeout(safetyTimeout); // Clear safety timeout on error
                    $('#statsRow').removeClass('loading-stats');

                    // Show error to user
                    toastr.error('Error al actualizar las estadísticas');
                });
        }

        function updateStatValue(selector, newValue) {
            const element = $(selector);
            const currentValue = element.text();

            if (currentValue !== newValue.toString()) {
                element.addClass('stat-updating').text(newValue);
                setTimeout(() => {
                    element.removeClass('stat-updating');
                }, 300);
            }
        }

        function updateQualityStats(qualityBreakdown) {
            const qualityStatsRow = $('#qualityStatsRow');

            // Add animation class to existing cards
            qualityStatsRow.find('.card').addClass('quality-card-updating');

            setTimeout(() => {
                qualityStatsRow.empty();

                qualityBreakdown.forEach(function (qualityStats, index) {
                    const cardClass = {
                        'Primeras': 'card-success',
                        'Segunda': 'card-warning',
                        'Tercera': 'card-info',
                        'Cuarta': 'card-primary',
                        'Industrial': 'card-secondary'
                    }[qualityStats.quality_name] || 'card-secondary';

                    const cardHtml = `
                <div class="col-lg-3 col-6">
                    <div class="card card-outline ${cardClass}" style="animation-delay: ${index * 0.1}s; animation: fadeInUp 0.5s ease forwards;">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-star"></i> ${qualityStats.quality_name}
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="description-block">
                                        <h5 class="description-header">${qualityStats.lots}</h5>
                                        <span class="description-text">LOTES</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="description-block">
                                        <h5 class="description-header">${Number(qualityStats.total_kg).toLocaleString(undefined, {maximumFractionDigits: 0})}</h5>
                                        <span class="description-text">KG TOTAL</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-6">
                                    <div class="description-block">
                                        <h5 class="description-header text-success">$${Number(qualityStats.avg_price).toFixed(2)}</h5>
                                        <span class="description-text">PRECIO PROM.</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="description-block">
                                        <h5 class="description-header text-danger">$${Number(qualityStats.total_value).toLocaleString(undefined, {maximumFractionDigits: 0})}</h5>
                                        <span class="description-text">INVERSIÓN</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

                    qualityStatsRow.append(cardHtml);
                });
            }, 200);
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

                    // Populate form fields
                    $('#supplier_id').val(data.supplier_id || '');
                    $('#codigo').val(data.codigo || '');
                    $('#peso_inicial').val(data.peso_inicial || '');
                    $('#precio_compra').val(data.precio_compra || '');
                    $('#calidad').val(data.quality_grade_id || data.calidad || ''); // Try ID first, then name for backward compatibility
                    $('#notas').val(data.notas || '');
                    $('#fecha_compra').val(data.fecha_compra || '');

                    // Populate payment fields if payment data exists
                    $('#monto_pago').val(data.amount_paid || '');
                    if (data.latest_payment) {
                        $('#fecha_pago').val(data.latest_payment.payment_date || '');
                        $('#tipo_pago').val(data.latest_payment.payment_type || 'efectivo');
                        $('#notas_pago').val(data.latest_payment.notes || '');
                    }


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

        function deleteLot(id, codigo, force = false) {
            if (!force) {
                // Primera confirmación
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
                        performDelete(id, codigo, false);
                    }
                });
            } else {
                performDelete(id, codigo, true);
            }
        }

        function performDelete(id, codigo, force) {
            const url = force ? `{{ url('lots') }}/${id}?force=1` : `{{ url('lots') }}/${id}`;

            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.warning && !force) {
                        // Mostrar advertencia de déficit
                        Swal.fire({
                            title: '⚠️ ¡ADVERTENCIA DE DÉFICIT!',
                            html: `<div class="text-left">
                    <p>${data.message}</p>
                    <hr>
                    <p><strong>Calidad:</strong> ${data.quality}</p>
                    <p><strong>Déficit resultante:</strong> <span class="text-danger font-weight-bold">${data.deficit} kg</span></p>
                    <hr>
                    <p class="text-danger"><strong>Esto puede causar problemas con las ventas comprometidas.</strong></p>
                </div>`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Eliminar de todos modos',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                deleteLot(id, codigo, true); // Forzar eliminación
                            }
                        });
                    } else if (data.success) {
                        toastr.success('Lote eliminado correctamente');
                        lotsTable.draw();
                        // Actualizar estadísticas inmediatamente
                        updateStats();
                    } else {
                        toastr.error(data.message || 'Error al eliminar el lote');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr.error('Error al eliminar el lote');
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

                .payment-status {
                    border: 1px solid #000;
                    padding: 3px;
                    margin: 3px 0;
                    text-align: center;
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
            </div>

            <div class="section">
                <div class="center bold">INVENTARIO</div>
                <div class="row">
                    <span class="label">Peso Total:</span>
                    <span class="value">${lotData.totalWeight}</span>
                </div>
                <div class="row">
                    <span class="label">Acopio:</span>
                    <span class="value">${lotData.quality}</span>
                </div>
                <div class="separator"></div>
                <div class="center">Contribuye al inventario total</div>
            </div>

            <div class="section">
                <div class="center bold">COMPRA</div>
                <div class="row">
                    <span class="label">Precio/kg:</span>
                    <span class="value">$${lotData.pricePerKg}</span>
                </div>
                <div class="separator"></div>
                <div class="row">
                    <span class="label">COSTO TOTAL:</span>
                    <span class="value bold">$${lotData.totalCost}</span>
                </div>
            </div>

            <div class="section">
                <div class="center bold">ESTADO DE PAGOS</div>
                <div class="payment-status bold">
                    ${lotData.paymentStatus || 'PENDIENTE'}
                </div>
                <div class="row">
                    <span class="label">Total a Pagar:</span>
                    <span class="value">$${lotData.totalCost}</span>
                </div>
                <div class="row">
                    <span class="label">Monto Pagado:</span>
                    <span class="value">$${lotData.amountPaid || '0.00'}</span>
                </div>
                <div class="separator"></div>
                <div class="row">
                    <span class="label">SALDO PENDIENTE:</span>
                    <span class="value bold">$${lotData.amountOwed || lotData.totalCost}</span>
                </div>
                ${lotData.latestPayment ? `
                <div style="font-size: 10px; margin-top: 5px;">
                    <div class="center">Último Pago:</div>
                    <div>${lotData.latestPayment.date} - $${lotData.latestPayment.amount}</div>
                    <div>${lotData.latestPayment.type} - ${lotData.latestPayment.notes || ''}</div>
                </div>
                ` : ''}
            </div>


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

            // Extraer datos de pagos (tabla de estado de pagos)
            const paymentTables = $(reportContent).find('table');
            let amountPaid = '0.00';
            let amountOwed = '0.00';
            let paymentStatus = 'PENDIENTE';
            let latestPayment = null;

            // Buscar tabla de estado de pagos
            paymentTables.each(function () {
                const table = $(this);
                const firstRowText = table.find('tr:first td:first').text();
                if (firstRowText.includes('Total a Pagar')) {
                    amountPaid = table.find('tr:first td:nth-child(4)').text().replace(/[^0-9.]/g, '').trim() || '0.00';
                    amountOwed = table.find('tr:last td:nth-child(2)').text().replace(/[^0-9.]/g, '').trim() || '0.00';
                    const statusBadge = table.find('.badge').first().text().trim();
                    paymentStatus = statusBadge.toUpperCase();
                }
            });

            // Buscar tabla de historial de pagos para obtener último pago
            const paymentHistoryTable = $(reportContent).find('table').filter(function () {
                return $(this).find('th').first().text().includes('Fecha') && $(this).find('th').eq(1).text().includes('Monto');
            });

            if (paymentHistoryTable.length > 0) {
                const lastPaymentRow = paymentHistoryTable.find('tbody tr:first');
                if (lastPaymentRow.length > 0) {
                    latestPayment = {
                        date: lastPaymentRow.find('td:first').text().trim(),
                        amount: lastPaymentRow.find('td:nth-child(2)').text().replace(/[^0-9.]/g, '').trim(),
                        type: lastPaymentRow.find('td:nth-child(3)').text().trim(),
                        notes: lastPaymentRow.find('td:nth-child(5)').text().trim()
                    };
                }
            }

            return {
                supplier: $(reportContent).find('table:first tr:nth-child(2) td:nth-child(2)').text().trim() || 'Anónimo',
                date: $(reportContent).find('table:first tr:nth-child(3) td:nth-child(2)').text().trim(),
                quality: $(reportContent).find('table:first tr:nth-child(4) td:nth-child(2)').text().trim(),
                status: $(reportContent).find('table:first tr:nth-child(5) td:nth-child(2)').text().trim(),
                totalWeight: $(reportContent).find('table:eq(1) tr:nth-child(1) td:nth-child(2)').text().trim(),
                pricePerKg: $(reportContent).find('table:eq(2) tr:nth-child(1) td:nth-child(2)').text().replace('$', '').trim(),
                totalCost: $(reportContent).find('table:eq(2) tr:nth-child(2) td:nth-child(2)').text().replace('$', '').trim(),
                notes: $(reportContent).find('.alert').text().trim() || null,
                // Datos de pagos
                amountPaid: amountPaid,
                amountOwed: amountOwed,
                paymentStatus: paymentStatus,
                latestPayment: latestPayment
            };
        }

        // Form submission
        $('#lotForm').submit(function (e) {
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
                        // Actualizar estadísticas inmediatamente
                        updateStats();
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

        // Calculate cost statistics in real-time
        function updateLotStatistics() {
            const peso = parseFloat($('#peso_inicial').val()) || 0;
            const precioCompra = parseFloat($('#precio_compra').val()) || 0;

            const costoTotal = peso * precioCompra;

            $('#total-cost').text('$' + costoTotal.toLocaleString('es-MX', {minimumFractionDigits: 2}));
            $('#total-weight').text(peso.toFixed(2));
            $('#price-per-kg').text(precioCompra.toFixed(2));

            // Update progress bar as visual indicator
            const maxCost = 50000; // Adjust based on typical lot costs
            const percentage = Math.min((costoTotal / maxCost) * 100, 100);
            //$('.progress-bar').css('width', percentage + '%');
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
        $(document).on('input', '#peso_inicial, #precio_compra', function () {
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

        // Payment Timeline Functions
        function openPaymentTimeline(id) {
            currentLotId = id;

            fetch(`{{ url('lots') }}/${id}/payments`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    displayPaymentTimeline(data);
                    $('#paymentTimelineModal').modal('show');
                })
                .catch(error => {
                    console.error('Error loading payment timeline:', error);
                    toastr.error('Error al cargar el historial de pagos');
                });
        }

        function displayPaymentTimeline(data) {
            const lot = data.lot;
            const payments = data.payments;

            $('#paymentTimelineModalTitle').text(`Historial de Pagos - ${lot.lot_code}`);

            const paymentStatusBadge = {
                'pending': '<span class="badge badge-warning">Pendiente</span>',
                'partial': '<span class="badge badge-info">Parcial</span>',
                'paid': '<span class="badge badge-success">Pagado</span>'
            };

            let html = `
        <div class="row">
            <div class="col-md-4">
                <div class="card bg-info">
                    <div class="card-body text-center">
                        <h4>$${parseFloat(lot.total_purchase_cost).toFixed(2)}</h4>
                        <p class="mb-0">Costo Total</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success">
                    <div class="card-body text-center">
                        <h4>$${parseFloat(lot.amount_paid || 0).toFixed(2)}</h4>
                        <p class="mb-0">Total Pagado</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-warning">
                    <div class="card-body text-center">
                        <h4>$${parseFloat(lot.amount_owed || 0).toFixed(2)}</h4>
                        <p class="mb-0">Pendiente</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            ${paymentStatusBadge[lot.payment_status]}
                            Estado del Pago - ${lot.supplier_name}
                        </h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h5>Timeline de Pagos</h5>
    `;

            if (payments.length === 0) {
                html += `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No se han registrado pagos para este lote.
                </div>
        `;
            } else {
                html += '<div class="timeline">';
                payments.forEach((payment, index) => {
                    const typeIcons = {
                        'efectivo': 'fas fa-money-bill-wave',
                        'transferencia': 'fas fa-exchange-alt',
                        'cheque': 'fas fa-file-invoice',
                        'deposito': 'fas fa-university',
                        'otro': 'fas fa-question-circle'
                    };

                    html += `
                <div class="time-label">
                    <span class="badge badge-primary">${payment.payment_date}</span>
                </div>
                <div>
                    <i class="${typeIcons[payment.payment_type]} bg-green"></i>
                    <div class="timeline-item">
                        <span class="time">
                            <i class="fas fa-clock"></i> ${payment.created_at}
                        </span>
                        <h3 class="timeline-header">
                            <strong>$${parseFloat(payment.amount).toFixed(2)}</strong>
                            <span class="badge badge-info ml-2">${payment.payment_type.toUpperCase()}</span>
                        </h3>
                        <div class="timeline-body">
                            ${payment.notes ? `<p><i class="fas fa-sticky-note"></i> ${payment.notes}</p>` : ''}
                            <small class="text-muted">
                                <i class="fas fa-user"></i> Registrado por: ${payment.paid_by}
                            </small>
                        </div>
                    </div>
                </div>
            `;
                });
                html += '</div>';
            }

            html += `
            </div>
        </div>
    `;

            $('#paymentTimelineContent').html(html);
            $('#paymentLotId').val(lot.id);
        }

        // Add Payment Modal
        $(document).on('click', '#addPaymentBtn', function () {
            $('#addPaymentModal').modal('show');
            $('#paymentDate').val(new Date().toISOString().split('T')[0]);
        });

        // Handle Add Payment Form Submission
        $(document).on('submit', '#addPaymentForm', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const lotId = $('#paymentLotId').val();

            fetch(`{{ url('lots') }}/${lotId}/payments`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        toastr.success(data.message);
                        $('#addPaymentModal').modal('hide');
                        $('#addPaymentForm')[0].reset();

                        // Refresh the payment timeline
                        openPaymentTimeline(lotId);

                        // Refresh the DataTable to show updated payment status
                        lotsTable.ajax.reload();

                        // Actualizar estadísticas inmediatamente
                        updateStats();
                    } else {
                        toastr.error(data.message || 'Error al agregar el pago');
                    }
                })
                .catch(error => {
                    console.error('Error adding payment:', error);
                    toastr.error('Error al agregar el pago');
                });
        });

        // ========================================
        // NEW PAYMENT FUNCTIONS (Following Sales Pattern)
        // ========================================

        function openLotPaymentTimeline(lotId) {
            console.log('Opening lot payment timeline for ID:', lotId);

            $('#lotPaymentTimelineModalBody').html('<div class="text-center p-4"><div class="spinner-border text-primary"></div><p class="mt-2">Cargando timeline de pagos...</p></div>');
            $('#lotPaymentTimelineModal').modal('show');

            fetch(`{{ url('lots') }}/${lotId}/payment-timeline`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    console.log('Payment timeline response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Payment timeline data:', data);
                    if (data.success && data.html) {
                        $('#lotPaymentTimelineModalBody').html(data.html);
                    } else if (data.error) {
                        console.error('Server error:', data.error);
                        toastr.error('Error del servidor: ' + data.error);
                    } else {
                        console.error('No HTML content in response');
                        toastr.error('No se recibió contenido del timeline');
                    }
                })
                .catch(error => {
                    console.error('Error loading lot payment timeline:', error);
                    toastr.error('Error al cargar el timeline: ' + error.message);
                });
        }

        function registerLotPayment(lotId, fromTimeline = false) {
            console.log('Opening lot payment modal for ID:', lotId);

            // Si viene del timeline, cerrar el modal del timeline primero
            if (fromTimeline || $('#lotPaymentTimelineModal').hasClass('show')) {
                $('#lotPaymentTimelineModal').modal('hide');

                // Esperar a que se cierre el modal del timeline antes de abrir el de pagos
                setTimeout(() => {
                    $('#lotPaymentModal').modal('show');
                    loadLotPaymentForm(lotId);
                }, 500);
            } else {
                $('#lotPaymentModal').modal('show');
                loadLotPaymentForm(lotId);
            }
        }

        function loadLotPaymentForm(lotId) {
            $('#lotPaymentModalBody').html('<div class="text-center p-4"><div class="spinner-border text-primary"></div><p class="mt-2">Cargando formulario de pago...</p></div>');

            fetch(`{{ url('lots') }}/${lotId}/payment-form`, {
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
                    console.log('Payment form data:', data);
                    if (data.success && data.html) {
                        $('#lotPaymentModalBody').html(data.html);
                    } else if (data.error) {
                        console.error('Server error:', data.error);
                        toastr.error('Error del servidor: ' + data.error);
                    } else {
                        console.error('No HTML content in response');
                        toastr.error('No se recibió contenido del formulario');
                    }
                })
                .catch(error => {
                    console.error('Error loading lot payment form:', error);
                    toastr.error('Error al cargar formulario: ' + error.message);
                });
        }

        function reloadTable() {
            if (typeof lotsTable !== 'undefined' && lotsTable) {
                lotsTable.ajax.reload();
                updateStats();
            }
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


        /* Animaciones para actualizaciones en tiempo real */
        .loading-stats {
            position: relative;
            pointer-events: none;
            opacity: 0.7;
        }

        .loading-stats .small-box::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 30px;
            height: 30px;
            margin: -15px 0 0 -15px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            z-index: 10;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-updating {
            background-color: #d4edda !important;
            animation: pulse-green 0.3s ease-in-out;
            transition: all 0.3s ease;
            border-radius: 4px;
            padding: 2px 4px;
        }

        @keyframes pulse-green {
            0% {
                background-color: #d1ecf1;
                transform: scale(1);
            }
            50% {
                background-color: #d4edda;
                transform: scale(1.05);
            }
            100% {
                background-color: transparent;
                transform: scale(1);
            }
        }

        /* Efecto visual para las cards de calidad cuando se actualizan */
        .quality-card-updating {
            transform: scale(1.02);
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
            transition: all 0.3s ease;
        }

        .quality-card-updating .card-header {
            background-color: rgba(0, 123, 255, 0.1) !important;
        }

        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.875rem;
            }

            .btn-group-sm .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.775rem;
            }

            .loading-stats::after {
                width: 15px;
                height: 15px;
                margin: -7.5px 0 0 -7.5px;
            }
        }
    </style>
@endpush
