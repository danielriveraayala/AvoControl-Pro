@extends('layouts.admin')

@section('title', 'Centro de Notificaciones')
@section('page-title')
<i class="fas fa-bell mr-2"></i>Centro de Notificaciones
@if($notifications->count() > 0)
    @if($notifications->where('read_at', null)->count() > 0)
        <span class="badge badge-warning ml-2">{{ $notifications->where('read_at', null)->count() }} sin leer</span>
    @endif
@endif
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Notificaciones</li>
@endsection

@section('content')
            <!-- Filters -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-filter mr-2"></i>Filtros
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('notifications.index') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status">Estado:</label>
                                    <select name="status" id="status" class="form-control form-control-sm">
                                        <option value="">Todas</option>
                                        <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>No leídas</option>
                                        <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Leídas</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="type">Tipo:</label>
                                    <select name="type" id="type" class="form-control form-control-sm">
                                        <option value="all">Todos</option>
                                        @foreach($types as $type)
                                            <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                                {{ ucfirst(str_replace('_', ' ', $type)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="from">Desde:</label>
                                    <input type="date" name="from" id="from" class="form-control form-control-sm" value="{{ request('from') }}">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="to">Hasta:</label>
                                    <input type="date" name="to" id="to" class="form-control form-control-sm" value="{{ request('to') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-sm mr-2">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                                <a href="{{ route('notifications.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-undo"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Notificaciones</h3>
                    <div class="card-tools">
                        <a href="{{ route('notifications.preferences') }}" class="btn btn-sm btn-info">
                            <i class="fas fa-cog"></i> Preferencias
                        </a>
                        @if($notifications->where('read_at', null)->count() > 0)
                            <button onclick="markAllAsRead()" class="btn btn-sm btn-success">
                                <i class="fas fa-check"></i> Marcar todas como leídas
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($notifications->count() > 0)
                        <div class="timeline timeline-inverse">
                            @foreach($notifications as $notification)
                                @php
                                    $data = is_array($notification->data) ? $notification->data : json_decode($notification->data, true);
                                    $isUnread = !$notification->read_at;
                                @endphp
                                <!-- Timeline time label -->
                                <div class="time-label" id="time-label-{{ $notification->id }}">
                                    <span class="bg-{{ getPriorityBadgeClass($notification->priority ?? 'normal') }}">
                                        {{ $notification->created_at->format('d M') }}
                                    </span>
                                </div>
                                <!-- Timeline item -->
                                <div id="timeline-wrapper-{{ $notification->id }}">
                                    <i class="{{ getNotificationIcon($notification->type) }}"></i>
                                    <div class="timeline-item {{ $isUnread ? 'bg-light border-left-warning' : '' }}" id="notification-{{ $notification->id }}">
                                        <span class="time">
                                            <i class="fas fa-clock"></i> {{ $notification->created_at->diffForHumans() }}
                                            @if($isUnread)
                                                <span class="badge badge-warning ml-2">Nuevo</span>
                                            @endif
                                        </span>
                                        <h3 class="timeline-header">
                                            {{ $data['title'] ?? 'Notificación' }}
                                            <div class="float-right">
                                                <span class="badge badge-{{ getNotificationBadgeClass($notification->type) }}">
                                                    {{ ucfirst(str_replace('_', ' ', $notification->type)) }}
                                                </span>
                                                @if($notification->priority)
                                                    <span class="badge badge-{{ getPriorityBadgeClass($notification->priority) }}">
                                                        {{ ucfirst($notification->priority) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </h3>
                                        <div class="timeline-body">
                                            {{ $data['message'] ?? '' }}
                                        </div>
                                        <div class="timeline-footer">
                                            @if(isset($data['action_url']))
                                                <a href="{{ $data['action_url'] }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-external-link-alt mr-1"></i>{{ $data['action_text'] ?? 'Ver más' }}
                                                </a>
                                            @endif
                                            @if($isUnread)
                                                <button onclick="markAsRead('{{ $notification->id }}')" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check mr-1"></i>Marcar leída
                                                </button>
                                            @endif
                                            <button onclick="deleteNotification('{{ $notification->id }}')" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash mr-1"></i>Eliminar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <!-- Timeline end -->
                            <div>
                                <i class="far fa-clock bg-gray"></i>
                            </div>
                        </div>
                    @else
                        <div class="card-body text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-bell-slash fa-4x text-muted"></i>
                            </div>
                            <h4 class="text-muted">No hay notificaciones</h4>
                            <p class="text-muted">
                                Cuando recibas notificaciones, aparecerán aquí.
                            </p>
                            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                                <i class="fas fa-arrow-left mr-2"></i>Volver al Dashboard
                            </a>
                        </div>
                    @endif
                </div>
                @if($notifications->hasPages())
                    <div class="card-footer">
                        {{ $notifications->withQueryString()->links() }}
                    </div>
                @endif
            </div>

@endsection

@push('styles')
<style>
.timeline-inverse .timeline-item {
    box-shadow: 0 1px 3px rgba(0,0,0,.12), 0 1px 2px rgba(0,0,0,.24);
    transition: all 0.3s cubic-bezier(.25,.8,.25,1);
}

.timeline-inverse .timeline-item:hover {
    box-shadow: 0 14px 28px rgba(0,0,0,.25), 0 10px 10px rgba(0,0,0,.22);
}

.timeline-item.border-left-warning {
    border-left: 4px solid #ffc107 !important;
}

.timeline .time-label > span {
    font-weight: 600;
    padding: 5px 10px;
}

.timeline-header {
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 10px;
    margin-bottom: 10px;
}

.timeline-body {
    padding: 10px 0;
    font-size: 14px;
    line-height: 1.6;
}

.timeline-footer {
    padding-top: 10px;
    border-top: 1px solid #dee2e6;
}

.timeline-footer .btn {
    margin-right: 5px;
    margin-bottom: 5px;
}

.badge {
    font-size: 75%;
}
</style>
@endpush

@push('scripts')
<script>
function markAsRead(notificationId) {
    $.ajax({
        url: `/notifications/${notificationId}/mark-as-read`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                $(`#notification-${notificationId}`).removeClass('bg-light');
                $(`#notification-${notificationId} .btn-success`).remove();
                toastr.success('Notificación marcada como leída');
                // Reload notifications in navbar
                if (typeof loadNotifications === 'function') {
                    loadNotifications();
                }
            }
        },
        error: function() {
            toastr.error('Error al marcar la notificación');
        }
    });
}

function markAllAsRead() {
    $.ajax({
        url: '{{ route("notifications.markAsRead") }}',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                location.reload();
            }
        },
        error: function() {
            toastr.error('Error al marcar las notificaciones');
        }
    });
}

function deleteNotification(notificationId) {
    Swal.fire({
        title: '¿Eliminar notificación?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/notifications/${notificationId}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Remove the entire timeline structure for this notification
                        $(`#time-label-${notificationId}, #timeline-wrapper-${notificationId}`).fadeOut(300, function() {
                            $(this).remove();
                        });
                        toastr.success('Notificación eliminada');
                        // Reload notifications in navbar
                        if (typeof loadNotifications === 'function') {
                            loadNotifications();
                        }
                    }
                },
                error: function() {
                    toastr.error('Error al eliminar la notificación');
                }
            });
        }
    });
}
</script>
@endpush