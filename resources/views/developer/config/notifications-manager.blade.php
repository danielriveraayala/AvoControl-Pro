@extends('layouts.developer')

@section('title', 'Gestión de Notificaciones')
@section('page-title', 'Gestión de Notificaciones')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('developer.index') }}">Panel de Desarrollador</a></li>
    <li class="breadcrumb-item"><a href="{{ route('developer.config.index') }}">Configuración</a></li>
    <li class="breadcrumb-item active">Gestión de Notificaciones</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bell mr-1"></i>
                    Gestión de Notificaciones del Sistema
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Información:</strong> Aquí puedes ver y gestionar todas las notificaciones del sistema, tanto de email como push notifications. Las notificaciones se envían automáticamente según las tareas programadas en el cron.
                </div>

                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="filter-type">Tipo:</label>
                        <select id="filter-type" class="form-control form-control-sm">
                            <option value="">Todos los tipos</option>
                            <option value="inventory">Inventario</option>
                            <option value="sales">Ventas</option>
                            <option value="payment">Pagos</option>
                            <option value="system">Sistema</option>
                            <option value="report">Reportes</option>
                            <option value="test">Prueba</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filter-priority">Prioridad:</label>
                        <select id="filter-priority" class="form-control form-control-sm">
                            <option value="">Todas las prioridades</option>
                            <option value="low">Baja</option>
                            <option value="normal">Normal</option>
                            <option value="high">Alta</option>
                            <option value="critical">Crítica</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filter-channels">Canal:</label>
                        <select id="filter-channels" class="form-control form-control-sm">
                            <option value="">Todos los canales</option>
                            <option value="email">Email</option>
                            <option value="push">Push</option>
                            <option value="database">Base de datos</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filter-status">Estado:</label>
                        <select id="filter-status" class="form-control form-control-sm">
                            <option value="">Todos los estados</option>
                            <option value="pending">Pendiente</option>
                            <option value="sent">Enviado</option>
                            <option value="failed">Fallido</option>
                            <option value="scheduled">Programado</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="filter-date-from">Desde:</label>
                        <input type="date" id="filter-date-from" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4">
                        <label for="filter-date-to">Hasta:</label>
                        <input type="date" id="filter-date-to" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" id="clear-filters" class="btn btn-secondary btn-sm mr-2">
                            <i class="fas fa-eraser"></i> Limpiar Filtros
                        </button>
                        <button type="button" id="refresh-table" class="btn btn-info btn-sm">
                            <i class="fas fa-sync-alt"></i> Actualizar
                        </button>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3 id="total-notifications">-</h3>
                                <p>Total Notificaciones</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-bell"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3 id="sent-notifications">-</h3>
                                <p>Enviadas</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3 id="pending-notifications">-</h3>
                                <p>Pendientes</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3 id="failed-notifications">-</h3>
                                <p>Fallidas</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DataTable -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="notificationsTable">
                        <thead>
                            <tr>
                                <th width="80px">ID</th>
                                <th width="100px">Tipo</th>
                                <th>Título</th>
                                <th>Mensaje</th>
                                <th width="100px">Usuario</th>
                                <th width="80px">Prioridad</th>
                                <th width="120px">Canales</th>
                                <th width="80px">Estado</th>
                                <th width="140px">Fecha</th>
                                <th width="100px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables content -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notification Details Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white" id="notificationModalTitle">Detalles de la Notificación</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="notificationModalBody">
                <!-- Notification details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let notificationsTable;

$(document).ready(function() {
    initializeNotificationsTable();
    loadNotificationsStats();
    
    // Filter change events
    $('#filter-type, #filter-priority, #filter-channels, #filter-status, #filter-date-from, #filter-date-to').change(function() {
        notificationsTable.ajax.reload();
    });
    
    // Clear filters
    $('#clear-filters').click(function() {
        $('#filter-type, #filter-priority, #filter-channels, #filter-status').val('');
        $('#filter-date-from, #filter-date-to').val('');
        notificationsTable.ajax.reload();
    });
    
    // Refresh table
    $('#refresh-table').click(function() {
        notificationsTable.ajax.reload();
        loadNotificationsStats();
    });
});

function initializeNotificationsTable() {
    notificationsTable = $('#notificationsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("developer.config.notifications-manager.data") }}',
            data: function(d) {
                d.type = $('#filter-type').val();
                d.priority = $('#filter-priority').val();
                d.channels = $('#filter-channels').val();
                d.status = $('#filter-status').val();
                d.date_from = $('#filter-date-from').val();
                d.date_to = $('#filter-date-to').val();
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'type', name: 'type', orderable: false },
            { data: 'title', name: 'title' },
            { data: 'message', name: 'message' },
            { data: 'user', name: 'user', orderable: false },
            { data: 'priority', name: 'priority', orderable: false },
            { data: 'channels', name: 'channels', orderable: false },
            { data: 'status', name: 'status', orderable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        responsive: true,
        pageLength: 25,
        order: [[8, 'desc']],
        drawCallback: function() {
            loadNotificationsStats();
        }
    });
}

function loadNotificationsStats() {
    // This would typically be an API call to get statistics
    // For now, we'll use DataTable info
    const info = notificationsTable.page.info();
    $('#total-notifications').text(info.recordsTotal);
    
    // You could add actual API calls here to get real statistics
    // fetch('/developer/config/notifications-manager/stats')...
}

function viewNotification(id) {
    // This would load notification details
    $('#notificationModalBody').html(`
        <div class="text-center">
            <i class="fas fa-spinner fa-spin"></i> Cargando detalles...
        </div>
    `);
    $('#notificationModal').modal('show');
    
    // Here you would fetch the notification details via AJAX
    // For now, showing a placeholder
    setTimeout(() => {
        $('#notificationModalBody').html(`
            <div class="row">
                <div class="col-md-6">
                    <strong>ID:</strong> ${id}<br>
                    <strong>Fecha de creación:</strong> --<br>
                    <strong>Tipo:</strong> --<br>
                    <strong>Prioridad:</strong> --<br>
                </div>
                <div class="col-md-6">
                    <strong>Usuario:</strong> --<br>
                    <strong>Canales:</strong> --<br>
                    <strong>Estado:</strong> --<br>
                    <strong>Intentos de envío:</strong> --<br>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-12">
                    <strong>Título:</strong><br>
                    <p class="text-muted">Título de la notificación</p>
                    
                    <strong>Mensaje:</strong><br>
                    <p class="text-muted">Contenido completo del mensaje de la notificación...</p>
                </div>
            </div>
        `);
    }, 1000);
}

function deleteNotification(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: '¿Deseas eliminar esta notificación? Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`{{ route('developer.config.notifications-manager.delete', '') }}/${id}`, {
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
                    toastr.success('Notificación eliminada correctamente');
                    notificationsTable.ajax.reload();
                    loadNotificationsStats();
                } else {
                    toastr.error(data.message || 'Error al eliminar la notificación');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('Error al eliminar la notificación');
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