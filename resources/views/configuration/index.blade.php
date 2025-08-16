@extends('layouts.admin')

@section('title', 'Configuración')
@section('page-title', 'Configuración del Sistema')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Configuración</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-tabs">
                <div class="card-header p-0 pt-1">
                    <ul class="nav nav-tabs" id="custom-tabs-two-tab" role="tablist">
                        <li class="pt-2 px-3">
                            <h3 class="card-title">
                                <i class="fas fa-cog mr-1"></i>
                                Configuraciones
                            </h3>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" id="quality-tab" data-toggle="pill" href="#quality" role="tab"
                               aria-controls="quality" aria-selected="true">
                                <i class="fas fa-star"></i> Calidades
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="company-tab" data-toggle="pill" href="#company" role="tab"
                               aria-controls="company" aria-selected="false">
                                <i class="fas fa-building"></i> Empresa
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="push-tab" data-toggle="pill" href="#push" role="tab"
                               aria-controls="push" aria-selected="false">
                                <i class="fas fa-bell"></i> Push Notifications
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="billing-tab" data-toggle="pill" href="#billing" role="tab"
                               aria-controls="billing" aria-selected="false">
                                <i class="fas fa-credit-card"></i> Facturación
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="custom-tabs-two-tabContent">
                        <!-- Quality Configuration Tab -->
                        <div class="tab-pane fade show active" id="quality" role="tabpanel"
                             aria-labelledby="quality-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4><i class="fas fa-star text-warning"></i> Gestión de Calidades</h4>
                                <button type="button" class="btn btn-success" data-toggle="modal"
                                        data-target="#qualityModal" onclick="openCreateQualityModal()">
                                    <i class="fas fa-plus"></i> Nueva Calidad
                                </button>
                            </div>
                            <div class="table-responsive w-100">
                                <table class="table table-bordered table-striped" id="qualityTable">
                                    <thead>
                                        <tr>
                                            <th>Orden</th>
                                            <th>Nombre</th>
                                            <th>Color</th>
                                            <th>Peso (g)</th>
                                            <th>Estado</th>
                                            <th width="100">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- DataTables content -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Company Configuration Tab -->
                        <div class="tab-pane fade" id="company" role="tabpanel" aria-labelledby="company-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4><i class="fas fa-building text-info"></i> Configuración de la Empresa</h4>
                            </div>
                            
                            <form id="companyForm">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="company_name">Nombre de la Empresa <span class="text-danger">*</span></label>
                                            <input type="text" name="company_name" id="company_name" class="form-control" 
                                                   placeholder="AvoControl-Pro" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="company_rfc">RFC</label>
                                            <input type="text" name="company_rfc" id="company_rfc" class="form-control" 
                                                   placeholder="RFC123456789" maxlength="13">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="company_address">Dirección</label>
                                            <textarea name="company_address" id="company_address" class="form-control" 
                                                      rows="2" placeholder="Dirección completa de la empresa"></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="company_city">Ciudad</label>
                                            <input type="text" name="company_city" id="company_city" class="form-control" 
                                                   placeholder="Uruapan">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="company_state">Estado</label>
                                            <input type="text" name="company_state" id="company_state" class="form-control" 
                                                   value="Michoacán">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="company_postal_code">Código Postal</label>
                                            <input type="text" name="company_postal_code" id="company_postal_code" class="form-control" 
                                                   placeholder="60000">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="company_phone">Teléfono</label>
                                            <input type="text" name="company_phone" id="company_phone" class="form-control" 
                                                   placeholder="(452) 123-4567">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="company_email">Email</label>
                                            <input type="email" name="company_email" id="company_email" class="form-control" 
                                                   placeholder="contacto@empresa.com">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="company_website">Sitio Web</label>
                                            <input type="url" name="company_website" id="company_website" class="form-control" 
                                                   placeholder="https://www.empresa.com">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="company_logo">Logo (URL)</label>
                                            <input type="url" name="company_logo" id="company_logo" class="form-control" 
                                                   placeholder="https://www.empresa.com/logo.png">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="company_description">Descripción</label>
                                    <textarea name="company_description" id="company_description" class="form-control" 
                                              rows="3" placeholder="Descripción de la empresa para usar en reportes"></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Guardar Configuración
                                        </button>
                                        <button type="button" class="btn btn-secondary ml-2" onclick="loadCompanyConfig()">
                                            <i class="fas fa-undo"></i> Recargar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Push Notifications Configuration Tab -->
                        <div class="tab-pane fade" id="push" role="tabpanel" aria-labelledby="push-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4><i class="fas fa-bell text-warning"></i> Notificaciones Push</h4>
                                <div id="push-status" class="ml-2">
                                    <span class="badge badge-secondary">⭕ Cargando...</span>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Información:</strong> Las notificaciones push te permiten recibir alertas importantes del sistema directamente en tu navegador, incluso cuando no estés usando la aplicación.
                            </div>
                            
                            <!-- Push Notification Controls -->
                            <div class="row justify-content-center">
                                <div class="col-md-8">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-bell-o"></i> Suscripción a Notificaciones
                                            </h5>
                                        </div>
                                        <div class="card-body text-center">
                                            <div class="mb-4">
                                                <i class="fas fa-mobile-alt fa-3x text-muted mb-3"></i>
                                                <h6>Estado de tu Suscripción</h6>
                                                <p class="text-muted mb-4">Administra tu suscripción a notificaciones push para este dispositivo.</p>
                                            </div>
                                            
                                            <div class="mb-3" id="push-controls">
                                                <button type="button" id="enable-push-btn" class="btn btn-success btn-lg mb-2" style="display: none;">
                                                    <i class="fas fa-bell"></i> Activar Notificaciones
                                                </button>
                                                
                                                <button type="button" id="disable-push-btn" class="btn btn-outline-danger btn-lg mb-2" style="display: none;">
                                                    <i class="fas fa-bell-slash"></i> Desactivar Notificaciones
                                                </button>
                                                
                                            </div>
                                            
                                            <div id="browser-unsupported" class="alert alert-warning" style="display: none;">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <strong>Navegador no compatible:</strong> Tu navegador no soporta notificaciones push. Considera actualizarlo o usar Chrome, Firefox, Safari o Edge.
                                            </div>
                                            
                                            <div class="mt-4">
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle"></i> 
                                                    Las notificaciones funcionan incluso cuando no tienes la aplicación abierta
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Benefits Section -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card border-left-primary">
                                        <div class="card-body">
                                            <h6 class="text-primary"><i class="fas fa-star"></i> Beneficios de las Notificaciones Push</h6>
                                            <div class="row mt-3">
                                                <div class="col-md-3 text-center">
                                                    <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                                                    <p class="small">Alertas de <strong>Inventario Bajo</strong></p>
                                                </div>
                                                <div class="col-md-3 text-center">
                                                    <i class="fas fa-money-bill-wave fa-2x text-success mb-2"></i>
                                                    <p class="small">Recordatorios de <strong>Pagos Pendientes</strong></p>
                                                </div>
                                                <div class="col-md-3 text-center">
                                                    <i class="fas fa-truck fa-2x text-info mb-2"></i>
                                                    <p class="small">Notificaciones de <strong>Nuevos Lotes</strong></p>
                                                </div>
                                                <div class="col-md-3 text-center">
                                                    <i class="fas fa-chart-line fa-2x text-primary mb-2"></i>
                                                    <p class="small">Reportes <strong>Automáticos</strong></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>

                        <!-- Billing Configuration Tab -->
                        <div class="tab-pane fade" id="billing" role="tabpanel" aria-labelledby="billing-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4><i class="fas fa-credit-card text-success"></i> Facturación de Suscripción</h4>
                            </div>
                            
                            <div class="row">
                                <!-- Current Subscription Card -->
                                <div class="col-lg-8">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-file-invoice-dollar"></i> Suscripción Actual
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div id="subscription-details">
                                                <div class="text-center py-4">
                                                    <div class="spinner-border text-primary" role="status">
                                                        <span class="sr-only">Cargando...</span>
                                                    </div>
                                                    <p class="mt-2 text-muted">Cargando información de suscripción...</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Billing Summary Card -->
                                <div class="col-lg-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-chart-bar"></i> Resumen de Facturación
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div id="billing-summary">
                                                <div class="text-center py-3">
                                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                        <span class="sr-only">Cargando...</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Billing Management Actions -->
                            <div class="row mt-4">
                                <!-- Invoice Downloads Card -->
                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-file-invoice"></i> Facturas y Recibos
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <p class="text-muted mb-3">Descarga tus facturas y recibos de PayPal</p>
                                            <div id="invoices-section">
                                                <div class="text-center py-3">
                                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                        <span class="sr-only">Cargando...</span>
                                                    </div>
                                                    <p class="mt-2 text-muted small">Cargando facturas...</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Plan Management Card -->
                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-exchange-alt"></i> Gestión de Plan
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <p class="text-muted mb-3">Cambia tu plan de suscripción</p>
                                            <div id="plan-management-section">
                                                <div class="text-center py-3">
                                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                        <span class="sr-only">Cargando...</span>
                                                    </div>
                                                    <p class="mt-2 text-muted small">Cargando planes disponibles...</p>
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
        </div>
    </div>
    </div>

    <!-- Quality Modal -->
    <div class="modal fade" id="qualityModal" tabindex="-1" aria-labelledby="qualityModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white" id="qualityModalTitle">Nueva Calidad</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="qualityForm" data-mode="create">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quality_name">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="quality_name" class="form-control"
                                           placeholder="Ej: Primera, Segunda" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sort_order">Orden de Presentación</label>
                                    <input type="number" name="sort_order" id="sort_order" class="form-control"
                                           placeholder="0" min="0">
                                    <small class="text-muted">Orden en que aparece en las listas</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Rango de Peso (gramos)</label>
                                    <div class="input-group">
                                        <input type="number" name="weight_min" id="weight_min" class="form-control"
                                               placeholder="Min" min="1">
                                        <div class="input-group-prepend input-group-append">
                                            <span class="input-group-text">-</span>
                                        </div>
                                        <input type="number" name="weight_max" id="weight_max" class="form-control"
                                               placeholder="Max" min="1">
                                        <div class="input-group-append">
                                            <span class="input-group-text">g</span>
                                        </div>
                                    </div>
                                    <small class="text-muted">Rango de peso en gramos para esta calidad</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="description">Descripción</label>
                                    <textarea name="description" id="description" class="form-control" rows="3"
                                              placeholder="Descripción adicional de la calidad (opcional)"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="color">Color <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="color" name="color" id="color" class="form-control form-control-color"
                                               value="#6c757d" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <i class="fas fa-palette"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <small class="text-muted">Color para identificar visualmente esta calidad</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Vista Previa del Color</label>
                                    <div class="d-flex align-items-center">
                                        <div id="colorPreview" class="color-preview me-3"
                                             style="width: 40px; height: 40px; background-color: #6c757d; border-radius: 8px; border: 2px solid #ddd;"></div>
                                        <span id="colorValue" class="text-muted">#6c757d</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row" id="activeRow" style="display: none;">
                            <div class="col-12">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="active" name="active"
                                               checked>
                                        <label class="custom-control-label" for="active">Calidad Activa</label>
                                    </div>
                                    <small class="text-muted">Las calidades inactivas no aparecen en los
                                        formularios</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let qualityTable;

        $(document).ready(function () {
            // Wait a bit for all scripts to load
            setTimeout(() => {
                initializeQualityTable();
            }, 200);

            // Color picker event handler
            $('#color').on('input change', function() {
                updateColorPreview($(this).val());
            });
        });

        function initializeQualityTable() {
            // Check if table exists
            if (!$('#qualityTable').length) {
                console.warn('Quality table not found');
                return;
            }

            // Initialize DataTable
            if (typeof $.fn.DataTable !== 'undefined') {
                // Destroy existing DataTable if it exists
                if ($.fn.DataTable.isDataTable('#qualityTable')) {
                    $('#qualityTable').DataTable().destroy();
                }

                // Initialize new DataTable
                qualityTable = $('#qualityTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route("configuration.qualities.table") }}',
                        data: function(d) {
                            d.ajax = 1;
                        }
                    },
                    columns: [
                        { data: 'sort_order', name: 'sort_order', width: '80px' },
                        { data: 'name', name: 'name' },
                        { data: 'color', name: 'color', orderable: false },
                        { data: 'weight', name: 'weight', orderable: false },
                        { data: 'status', name: 'active', orderable: false },
                        { data: 'actions', name: 'actions', orderable: false, searchable: false }
                    ],
                    language: getDataTablesSpanishConfig(),
                    responsive: true,
                    pageLength: 10,
                    order: [[0, 'asc']]
                });
            }
        }

        function loadQualityTable() {
            if (qualityTable) {
                qualityTable.ajax.reload();
            }
        }

        function updateColorPreview(color) {
            $('#colorPreview').css('background-color', color);
            $('#colorValue').text(color);
        }

        function openCreateQualityModal() {
            $('#qualityModalTitle').text('Nueva Calidad');
            $('#qualityForm')[0].reset();
            $('#qualityForm').attr('data-mode', 'create');
            $('#qualityForm').attr('data-id', '');
            $('#activeRow').hide();

            // Reset color to default
            $('#color').val('#6c757d');
            updateColorPreview('#6c757d');

            $('#qualityModal').modal('show');
        }

        function editQuality(id) {
            $('#qualityModalTitle').text('Editar Calidad');
            $('#qualityForm').attr('data-mode', 'edit');
            $('#qualityForm').attr('data-id', id);
            $('#activeRow').show();

            // Load quality data
            fetch(`{{ url('configuration/quality') }}/${id}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    $('#quality_name').val(data.name);
                    $('#weight_min').val(data.weight_min);
                    $('#weight_max').val(data.weight_max);
                    $('#description').val(data.description);
                    $('#sort_order').val(data.sort_order);
                    $('#color').val(data.color || '#6c757d');
                    $('#active').prop('checked', data.active);

                    // Update color preview
                    updateColorPreview(data.color || '#6c757d');

                    $('#qualityModal').modal('show');
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr.error('Error al cargar los datos de la calidad');
                });
        }

        function deleteQuality(id, name) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas eliminar la calidad "${name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`{{ url('configuration/quality') }}/${id}`, {
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
                                toastr.success('Calidad eliminada correctamente');
                                loadQualityTable();
                            } else {
                                toastr.error(data.message || 'Error al eliminar la calidad');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            toastr.error('Error al eliminar la calidad');
                        });
                }
            });
        }

        // Form submission
        $('#qualityForm').submit(function (e) {
            e.preventDefault();

            const mode = $(this).attr('data-mode');
            const id = $(this).attr('data-id');
            const formData = new FormData(this);

            let url = '{{ route("configuration.quality.store") }}';

            if (mode === 'edit') {
                url = `{{ url('configuration/quality') }}/${id}`;
                formData.append('_method', 'PUT');
            }

            // Handle checkbox value
            if (!$('#active').is(':checked')) {
                formData.set('active', '0');
            } else {
                formData.set('active', '1');
            }

            // Debug: Log the color value being sent
            console.log('Color value being sent:', formData.get('color'));

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
                    if (!response.ok) {
                        return response.json().then(err => Promise.reject(err));
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        toastr.success(mode === 'create' ? 'Calidad creada correctamente' : 'Calidad actualizada correctamente');
                        $('#qualityModal').modal('hide');
                        loadQualityTable();
                    } else {
                        if (data.errors) {
                            Object.keys(data.errors).forEach(key => {
                                toastr.error(data.errors[key][0]);
                            });
                        } else {
                            toastr.error(data.message || 'Error al guardar la calidad');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error completo:', error);
                    if (error.errors) {
                        Object.keys(error.errors).forEach(key => {
                            console.error(`Error en campo ${key}:`, error.errors[key]);
                            toastr.error(`${key}: ${error.errors[key][0]}`);
                        });
                    } else {
                        toastr.error(error.message || 'Error al guardar la calidad');
                    }
                });
        });

        // Company configuration functions
        function loadCompanyConfig() {
            fetch('{{ route("configuration.company.get") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const config = data.config || {};
                    $('#company_name').val(config.company_name || 'AvoControl-Pro');
                    $('#company_rfc').val(config.company_rfc || '');
                    $('#company_address').val(config.company_address || '');
                    $('#company_city').val(config.company_city || 'Uruapan');
                    $('#company_state').val(config.company_state || 'Michoacán');
                    $('#company_postal_code').val(config.company_postal_code || '');
                    $('#company_phone').val(config.company_phone || '');
                    $('#company_email').val(config.company_email || '');
                    $('#company_website').val(config.company_website || '');
                    $('#company_logo').val(config.company_logo || '');
                    $('#company_description').val(config.company_description || '');
                } else {
                    toastr.error('Error al cargar la configuración de la empresa');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('Error al cargar la configuración de la empresa');
            });
        }

        // Company form submission
        $('#companyForm').submit(function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('{{ route("configuration.company.store") }}', {
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
                    toastr.success('Configuración de empresa guardada correctamente');
                } else {
                    if (data.errors) {
                        Object.keys(data.errors).forEach(key => {
                            toastr.error(data.errors[key][0]);
                        });
                    } else {
                        toastr.error(data.message || 'Error al guardar la configuración');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('Error al guardar la configuración de la empresa');
            });
        });

        // Load company config when company tab is shown
        $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
            if ($(e.target).attr('href') === '#company') {
                loadCompanyConfig();
            } else if ($(e.target).attr('href') === '#push') {
                loadPushNotificationConfig();
            }
        });


        // Push Notification Functions
        function loadPushNotificationConfig() {
            // Check push notification support and status
            checkPushSupport();
            
            // Initialize push notification controls
            initializePushControls();
        }


        function checkPushSupport() {
            if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
                $('#browser-unsupported').show();
                $('#push-status').html('<span class="badge badge-danger">❌ No Soportado</span>');
                $('#push-controls').hide();
                return false;
            }
            
            // Check permission status
            if (Notification.permission === 'denied') {
                $('#push-status').html('<span class="badge badge-danger">🚫 Permisos Denegados</span>');
                showDeniedState();
            } else {
                checkSubscriptionStatus();
            }
            
            return true;
        }

        function checkSubscriptionStatus() {
            // Check if user is already subscribed
            navigator.serviceWorker.ready.then(registration => {
                return registration.pushManager.getSubscription();
            })
            .then(subscription => {
                if (subscription) {
                    // User is subscribed
                    $('#push-status').html('<span class="badge badge-success">✅ Notificaciones Activas</span>');
                    showSubscribedState();
                } else {
                    // User is not subscribed
                    $('#push-status').html('<span class="badge badge-secondary">⭕ Notificaciones Inactivas</span>');
                    showUnsubscribedState();
                }
            })
            .catch(error => {
                console.error('Error checking subscription status:', error);
                $('#push-status').html('<span class="badge badge-warning">⚠️ Error</span>');
                showUnsubscribedState();
            });
        }

        function initializePushControls() {
            // Enable push notification button
            $('#enable-push-btn').click(() => {
                enablePushNotifications();
            });

            // Disable push notification button
            $('#disable-push-btn').click(() => {
                disablePushNotifications();
            });

        }

        function showUnsubscribedState() {
            $('#enable-push-btn').show();
            $('#disable-push-btn').hide();
        }

        function showSubscribedState() {
            $('#enable-push-btn').hide();
            $('#disable-push-btn').show();
        }

        function showDeniedState() {
            $('#enable-push-btn').hide();
            $('#disable-push-btn').hide();
        }

        function enablePushNotifications() {
            if (!checkPushSupport()) return;

            $('#enable-push-btn').html('<i class="fas fa-spinner fa-spin"></i> Activando...').prop('disabled', true);

            // Request permission
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    subscribeUserToPush();
                } else {
                    toastr.error('Necesitas permitir las notificaciones en tu navegador');
                    $('#enable-push-btn').html('<i class="fas fa-bell"></i> Activar Notificaciones').prop('disabled', false);
                }
            });
        }

        function subscribeUserToPush() {
            // Get VAPID public key
            fetch('/push/vapid-key')
                .then(response => response.json())
                .then(data => {
                    if (!data.public_key) {
                        throw new Error('No se pudo obtener la clave VAPID');
                    }

                    return navigator.serviceWorker.ready.then(registration => {
                        const applicationServerKey = urlBase64ToUint8Array(data.public_key);
                        return registration.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: applicationServerKey
                        });
                    });
                })
                .then(subscription => {
                    return fetch('/push/subscribe', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        body: JSON.stringify({ subscription: subscription })
                    });
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        toastr.success('¡Notificaciones activadas correctamente!');
                        showSubscribedState();
                        $('#push-status').html('<span class="badge badge-success">✅ Notificaciones Activas</span>');
                    } else {
                        throw new Error(data.message || 'Error del servidor');
                    }
                })
                .catch(error => {
                    console.error('Error enabling push notifications:', error);
                    toastr.error('Error al activar notificaciones: ' + error.message);
                })
                .finally(() => {
                    $('#enable-push-btn').html('<i class="fas fa-bell"></i> Activar Notificaciones').prop('disabled', false);
                });
        }

        function disablePushNotifications() {
            $('#disable-push-btn').html('<i class="fas fa-spinner fa-spin"></i> Desactivando...').prop('disabled', true);

            navigator.serviceWorker.ready.then(registration => {
                return registration.pushManager.getSubscription();
            })
            .then(subscription => {
                if (subscription) {
                    return subscription.unsubscribe();
                }
            })
            .then(() => {
                return fetch('/push/unsubscribe', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success('Notificaciones desactivadas correctamente');
                    showUnsubscribedState();
                    $('#push-status').html('<span class="badge badge-secondary">⭕ Notificaciones Inactivas</span>');
                } else {
                    throw new Error(data.message || 'Error del servidor');
                }
            })
            .catch(error => {
                console.error('Error disabling push notifications:', error);
                toastr.error('Error al desactivar notificaciones: ' + error.message);
            })
            .finally(() => {
                $('#disable-push-btn').html('<i class="fas fa-bell-slash"></i> Desactivar Notificaciones').prop('disabled', false);
            });
        }


        // Helper function to convert VAPID key
        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding)
                .replace(/-/g, '+')
                .replace(/_/g, '/');

            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);

            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }

        // Billing tab functionality
        function loadBillingInfo() {
            // Load subscription details
            fetch('/configuration/subscription/current')
                .then(response => response.json())
                .then(data => {
                    if (data.subscription) {
                        displaySubscriptionDetails(data.subscription);
                    } else {
                        displayNoSubscription();
                    }
                })
                .catch(error => {
                    console.error('Error loading subscription:', error);
                    displaySubscriptionError();
                });
        }

        function displaySubscriptionDetails(subscription) {
            const subscriptionDetails = document.getElementById('subscription-details');
            const billingSummary = document.getElementById('billing-summary');
            
            // Calculate days until next billing
            const nextBilling = new Date(subscription.next_billing_date);
            const today = new Date();
            const daysUntilBilling = Math.ceil((nextBilling - today) / (1000 * 60 * 60 * 24));
            
            // Format next billing date
            const formattedDate = nextBilling.toLocaleDateString('es-ES', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });

            subscriptionDetails.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-tag"></i> Plan Actual</h6>
                        <p class="lead text-primary">${subscription.plan_name || subscription.plan}</p>
                        
                        <h6><i class="fas fa-circle-dot"></i> Estado</h6>
                        <span class="badge badge-${subscription.status === 'active' ? 'success' : 'warning'} badge-lg">
                            ${subscription.status === 'active' ? 'Activa' : subscription.status}
                        </span>
                        
                        <h6 class="mt-3"><i class="fas fa-calendar-alt"></i> Ciclo de Facturación</h6>
                        <p>${subscription.billing_cycle === 'yearly' ? 'Anual' : 'Mensual'}</p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-dollar-sign"></i> Precio</h6>
                        <p class="lead text-success">$${subscription.amount} ${subscription.currency}</p>
                        
                        <h6><i class="fas fa-calendar-check"></i> Próxima Facturación</h6>
                        <p>${formattedDate}</p>
                        <small class="text-muted">En ${daysUntilBilling} día${daysUntilBilling !== 1 ? 's' : ''}</small>
                        
                        <h6 class="mt-3"><i class="fas fa-credit-card"></i> PayPal ID</h6>
                        <code class="small">${subscription.paypal_subscription_id || 'N/A'}</code>
                    </div>
                </div>
                
                ${subscription.is_trial ? `
                <div class="alert alert-info mt-3">
                    <i class="fas fa-gift"></i> 
                    <strong>Período de Prueba:</strong> ${subscription.trial_days} días restantes
                </div>
                ` : ''}
            `;

            billingSummary.innerHTML = `
                <div class="info-box-content">
                    <div class="text-center mb-3">
                        <i class="fas fa-dollar-sign fa-2x text-success"></i>
                        <h4 class="text-success mt-2">$${subscription.amount}</h4>
                        <p class="text-muted">${subscription.billing_cycle === 'yearly' ? 'por año' : 'por mes'}</p>
                    </div>
                    
                    <hr>
                    
                    <div class="small text-muted text-center">
                        <p><i class="fas fa-check text-success"></i> Renovación Automática</p>
                        <p><i class="fas fa-shield-alt text-primary"></i> Pago Seguro con PayPal</p>
                        <p><i class="fas fa-headset text-info"></i> Soporte 24/7</p>
                    </div>
                </div>
            `;
        }

        function displayNoSubscription() {
            const subscriptionDetails = document.getElementById('subscription-details');
            const billingSummary = document.getElementById('billing-summary');
            
            subscriptionDetails.innerHTML = `
                <div class="alert alert-warning text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                    <h5>No hay suscripción activa</h5>
                    <p>Esta empresa no tiene una suscripción activa asociada.</p>
                    <a href="/subscription/plans" class="btn btn-primary">
                        <i class="fas fa-credit-card"></i> Ver Planes Disponibles
                    </a>
                </div>
            `;

            billingSummary.innerHTML = `
                <div class="text-center text-muted">
                    <i class="fas fa-ban fa-2x"></i>
                    <p class="mt-2">Sin información de facturación</p>
                </div>
            `;
        }

        function displaySubscriptionError() {
            const subscriptionDetails = document.getElementById('subscription-details');
            const billingSummary = document.getElementById('billing-summary');
            
            subscriptionDetails.innerHTML = `
                <div class="alert alert-danger text-center">
                    <i class="fas fa-times-circle fa-2x mb-3"></i>
                    <h5>Error al cargar la información</h5>
                    <p>No se pudo cargar la información de suscripción.</p>
                    <button class="btn btn-outline-danger" onclick="loadBillingInfo()">
                        <i class="fas fa-sync"></i> Reintentar
                    </button>
                </div>
            `;

            billingSummary.innerHTML = `
                <div class="text-center text-danger">
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                    <p class="mt-2">Error de carga</p>
                </div>
            `;
        }

        // Load billing info when tab is shown
        $(document).on('shown.bs.tab', 'a[href="#billing"]', function() {
            loadBillingInfo();
            loadInvoices();
            loadAvailablePlans();
        });

        // Invoice management functions
        function loadInvoices() {
            fetch('/configuration/subscription/invoices')
                .then(response => response.json())
                .then(data => {
                    if (data.invoices && data.invoices.length > 0) {
                        displayInvoices(data.invoices);
                    } else {
                        displayNoInvoices();
                    }
                })
                .catch(error => {
                    console.error('Error loading invoices:', error);
                    displayInvoicesError();
                });
        }

        function displayInvoices(invoices) {
            const invoicesSection = document.getElementById('invoices-section');
            
            let html = '<div class="list-group list-group-flush">';
            
            invoices.forEach(invoice => {
                const date = new Date(invoice.create_time).toLocaleDateString('es-ES');
                const amount = invoice.total_amount ? invoice.total_amount.value + ' ' + invoice.total_amount.currency_code : 'N/A';
                
                html += `
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Factura #${invoice.invoice_number || invoice.id}</h6>
                            <small class="text-muted">${date} - ${amount}</small>
                        </div>
                        <button class="btn btn-sm btn-outline-primary" onclick="downloadInvoice('${invoice.id}')">
                            <i class="fas fa-download"></i> Descargar
                        </button>
                    </div>
                `;
            });
            
            html += '</div>';
            invoicesSection.innerHTML = html;
        }

        function displayNoInvoices() {
            const invoicesSection = document.getElementById('invoices-section');
            invoicesSection.innerHTML = `
                <div class="text-center py-3">
                    <i class="fas fa-file-invoice fa-2x text-muted mb-2"></i>
                    <p class="text-muted">No hay facturas disponibles</p>
                    <small class="text-muted">Las facturas aparecerán después del primer pago</small>
                </div>
            `;
        }

        function displayInvoicesError() {
            const invoicesSection = document.getElementById('invoices-section');
            invoicesSection.innerHTML = `
                <div class="alert alert-warning text-center">
                    <i class="fas fa-exclamation-triangle"></i>
                    <small>Error al cargar facturas</small>
                </div>
            `;
        }

        function downloadInvoice(invoiceId) {
            window.open(`/configuration/subscription/invoices/${invoiceId}/download`, '_blank');
        }

        // Plan management functions
        function loadAvailablePlans() {
            fetch('/configuration/subscription/available-plans')
                .then(response => response.json())
                .then(data => {
                    if (data.current_plan) {
                        displayPlanManagement(data);
                    } else {
                        displayPlanManagementError();
                    }
                })
                .catch(error => {
                    console.error('Error loading plans:', error);
                    displayPlanManagementError();
                });
        }

        function displayPlanManagement(data) {
            const planSection = document.getElementById('plan-management-section');
            const currentPlan = data.current_plan;
            const upgrades = data.upgrades || [];
            const downgrades = data.downgrades || [];
            
            let html = `
                <div class="mb-3">
                    <label class="form-label">Plan Actual:</label>
                    <div class="alert alert-info py-2">
                        <strong>${currentPlan.name}</strong> - $${currentPlan.price}/${currentPlan.billing_cycle === 'yearly' ? 'año' : 'mes'}
                    </div>
                </div>
            `;
            
            // Show upgrades section
            if (upgrades.length > 0) {
                html += `
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-arrow-up text-success"></i> Upgrades Disponibles:
                        </label>
                        <div class="list-group">
                `;
                
                upgrades.forEach(plan => {
                    html += `
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">${plan.name}</h6>
                                <small class="text-muted">$${plan.price}/mes</small>
                                <span class="badge badge-success ml-2">
                                    <i class="fas fa-arrow-up"></i> UPGRADE
                                </span>
                            </div>
                            <button class="btn btn-sm btn-success" onclick="changePlan('${plan.key}', 'upgrade')">
                                Upgrade
                            </button>
                        </div>
                    `;
                });
                
                html += `</div></div>`;
            }
            
            // Show downgrades section
            if (downgrades.length > 0) {
                html += `
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-arrow-down text-info"></i> Downgrades Disponibles:
                        </label>
                        <div class="list-group">
                `;
                
                downgrades.forEach(plan => {
                    html += `
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">${plan.name}</h6>
                                <small class="text-muted">$${plan.price}/mes</small>
                                <span class="badge badge-info ml-2">
                                    <i class="fas fa-arrow-down"></i> DOWNGRADE
                                </span>
                            </div>
                            <button class="btn btn-sm btn-info" onclick="changePlan('${plan.key}', 'downgrade')">
                                Downgrade
                            </button>
                        </div>
                    `;
                });
                
                html += `</div></div>`;
            }
            
            // Show message if no plans available
            if (upgrades.length === 0 && downgrades.length === 0) {
                html += `
                    <div class="alert alert-secondary text-center">
                        <i class="fas fa-info-circle"></i>
                        <small>No hay planes disponibles para cambiar. Tienes acceso a todos los planes adecuados.</small>
                    </div>
                `;
            }
            
            // Info message about proration
            if (upgrades.length > 0 || downgrades.length > 0) {
                html += `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <small>Los cambios de plan incluyen cálculo prorateado automático</small>
                    </div>
                `;
            }
            
            planSection.innerHTML = html;
        }

        function displayPlanManagementError() {
            const planSection = document.getElementById('plan-management-section');
            planSection.innerHTML = `
                <div class="alert alert-warning text-center">
                    <i class="fas fa-exclamation-triangle"></i>
                    <small>Error al cargar planes disponibles</small>
                </div>
            `;
        }

        function changePlan(newPlanKey, changeType) {
            // Get plan name for better UX
            const planName = event.target.closest('.list-group-item').querySelector('h6').textContent;
            
            Swal.fire({
                title: `¿Confirmar ${changeType.toUpperCase()}?`,
                html: `
                    <div class="text-left">
                        <p><strong>Plan seleccionado:</strong> ${planName}</p>
                        <p><strong>Tipo de cambio:</strong> ${changeType.toUpperCase()}</p>
                        <hr>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Se calculará el costo prorateado automáticamente y se aplicará en tu próxima facturación.
                        </small>
                    </div>
                `,
                icon: changeType === 'upgrade' ? 'question' : 'info',
                showCancelButton: true,
                confirmButtonColor: changeType === 'upgrade' ? '#28a745' : '#17a2b8',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Sí, hacer ${changeType}`,
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-' + (changeType === 'upgrade' ? 'success' : 'info'),
                    cancelButton: 'btn btn-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    const button = event.target;
                    const originalText = button.innerHTML;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
                    button.disabled = true;
                    
                    fetch('/configuration/subscription/change-plan', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            new_plan: newPlanKey,
                            change_type: changeType
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: '¡Plan cambiado exitosamente!',
                                html: `
                                    <div class="text-left">
                                        <p><strong>Nuevo plan:</strong> ${data.new_plan || planName}</p>
                                        <p><strong>Monto prorateado:</strong> $${data.prorated_amount || '0.00'}</p>
                                        <hr>
                                        <small class="text-muted">
                                            <i class="fas fa-envelope"></i> 
                                            Se ha enviado una confirmación a tu email.
                                        </small>
                                    </div>
                                `,
                                icon: 'success',
                                confirmButtonColor: '#28a745',
                                confirmButtonText: 'Entendido',
                                customClass: {
                                    confirmButton: 'btn btn-success'
                                },
                                buttonsStyling: false
                            }).then(() => {
                                loadBillingInfo(); // Reload billing info
                                loadAvailablePlans(); // Reload available plans
                            });
                        } else {
                            Swal.fire({
                                title: 'Error al cambiar el plan',
                                text: data.message || 'Error desconocido',
                                icon: 'error',
                                confirmButtonColor: '#dc3545',
                                confirmButtonText: 'Entendido',
                                customClass: {
                                    confirmButton: 'btn btn-danger'
                                },
                                buttonsStyling: false
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error changing plan:', error);
                        Swal.fire({
                            title: 'Error de conexión',
                            text: 'Error al cambiar el plan. Por favor intenta nuevamente.',
                            icon: 'error',
                            confirmButtonColor: '#dc3545',
                            confirmButtonText: 'Entendido',
                            customClass: {
                                confirmButton: 'btn btn-danger'
                            },
                            buttonsStyling: false
                        });
                    })
                    .finally(() => {
                        button.innerHTML = originalText;
                        button.disabled = false;
                    });
                }
            });
        }

    </script>
@endpush

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
@endpush
