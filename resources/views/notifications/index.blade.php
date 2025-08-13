@extends('layouts.admin')

@section('title', 'Centro de Notificaciones')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Centro de Notificaciones</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Notificaciones</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Filters -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filtros</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('notifications.index') }}" class="form-inline">
                        <div class="form-group mr-3">
                            <label for="status" class="mr-2">Estado:</label>
                            <select name="status" id="status" class="form-control form-control-sm">
                                <option value="">Todas</option>
                                <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>No leídas</option>
                                <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Leídas</option>
                            </select>
                        </div>

                        <div class="form-group mr-3">
                            <label for="type" class="mr-2">Tipo:</label>
                            <select name="type" id="type" class="form-control form-control-sm">
                                <option value="all">Todos</option>
                                @foreach($types as $type)
                                    <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $type)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mr-3">
                            <label for="from" class="mr-2">Desde:</label>
                            <input type="date" name="from" id="from" class="form-control form-control-sm" value="{{ request('from') }}">
                        </div>

                        <div class="form-group mr-3">
                            <label for="to" class="mr-2">Hasta:</label>
                            <input type="date" name="to" id="to" class="form-control form-control-sm" value="{{ request('to') }}">
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm mr-2">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <a href="{{ route('notifications.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-undo"></i> Limpiar
                        </a>
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
                        <div class="list-group list-group-flush">
                            @foreach($notifications as $notification)
                                @php
                                    $data = json_decode($notification->data, true);
                                    $isUnread = !$notification->read_at;
                                @endphp
                                <div class="list-group-item {{ $isUnread ? 'bg-light' : '' }}" id="notification-{{ $notification->id }}">
                                    <div class="row">
                                        <div class="col-auto">
                                            <i class="{{ getNotificationIcon($notification->type) }} fa-2x"></i>
                                        </div>
                                        <div class="col">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h5 class="mb-1">{{ $data['title'] ?? 'Notificación' }}</h5>
                                                <small class="text-muted">
                                                    {{ $notification->created_at->diffForHumans() }}
                                                </small>
                                            </div>
                                            <p class="mb-1">{{ $data['message'] ?? '' }}</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small>
                                                    <span class="badge badge-{{ getNotificationBadgeClass($notification->type) }}">
                                                        {{ ucfirst(str_replace('_', ' ', $notification->type)) }}
                                                    </span>
                                                    @if($notification->priority)
                                                        <span class="badge badge-{{ getPriorityBadgeClass($notification->priority) }}">
                                                            {{ ucfirst($notification->priority) }}
                                                        </span>
                                                    @endif
                                                </small>
                                                <div>
                                                    @if(isset($data['action_url']))
                                                        <a href="{{ $data['action_url'] }}" class="btn btn-sm btn-primary">
                                                            {{ $data['action_text'] ?? 'Ver más' }}
                                                        </a>
                                                    @endif
                                                    @if($isUnread)
                                                        <button onclick="markAsRead('{{ $notification->id }}')" class="btn btn-sm btn-success">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    @endif
                                                    <button onclick="deleteNotification('{{ $notification->id }}')" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No hay notificaciones</p>
                        </div>
                    @endif
                </div>
                @if($notifications->hasPages())
                    <div class="card-footer">
                        {{ $notifications->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>

@php
function getNotificationIcon($type) {
    $icons = [
        'test_daily' => 'fas fa-info-circle text-info',
        'inventory_low' => 'fas fa-exclamation-triangle text-warning',
        'payment_overdue' => 'fas fa-dollar-sign text-danger',
        'sale_completed' => 'fas fa-check-circle text-success',
        'system' => 'fas fa-cog text-secondary'
    ];
    return $icons[$type] ?? 'fas fa-bell text-primary';
}

function getNotificationBadgeClass($type) {
    $classes = [
        'test_daily' => 'info',
        'inventory_low' => 'warning',
        'payment_overdue' => 'danger',
        'sale_completed' => 'success',
        'system' => 'secondary'
    ];
    return $classes[$type] ?? 'primary';
}

function getPriorityBadgeClass($priority) {
    $classes = [
        'critical' => 'danger',
        'high' => 'warning',
        'normal' => 'info',
        'low' => 'secondary'
    ];
    return $classes[$priority] ?? 'primary';
}
@endphp
@endsection

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
                        $(`#notification-${notificationId}`).fadeOut(300, function() {
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