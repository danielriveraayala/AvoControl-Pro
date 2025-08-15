@extends('layouts.admin')

@section('title', 'Preferencias de Notificaciones')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Preferencias de Notificaciones</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('notifications.index') }}">Notificaciones</a></li>
                        <li class="breadcrumb-item active">Preferencias</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <!-- Success Message -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <i class="icon fas fa-check"></i> {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('notifications.updatePreferences') }}" method="POST">
                        @csrf
                        
                        <!-- General Settings -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Configuración General</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="custom-control custom-switch custom-switch-lg">
                                        <input type="checkbox" class="custom-control-input" id="email_enabled" 
                                               name="email_enabled" value="1" 
                                               {{ $preferences['email_enabled'] ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="email_enabled">
                                            <i class="fas fa-envelope mr-2"></i> Recibir notificaciones por Email
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Las notificaciones se enviarán a: {{ auth()->user()->email }}
                                    </small>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch custom-switch-lg">
                                        <input type="checkbox" class="custom-control-input" id="push_enabled" 
                                               name="push_enabled" value="1" 
                                               {{ $preferences['push_enabled'] ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="push_enabled">
                                            <i class="fas fa-bell mr-2"></i> Recibir notificaciones Push del navegador
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Las notificaciones aparecerán en tu navegador aunque no estés en la aplicación
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Notification Types -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Tipos de Notificaciones</h3>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Selecciona qué tipos de notificaciones deseas recibir:</p>
                                
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="notify_test_daily" 
                                               name="notification_types[test_daily]" value="1" 
                                               {{ $preferences['notification_types']['test_daily'] ?? true ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="notify_test_daily">
                                            <i class="fas fa-info-circle text-info mr-2"></i>
                                            <strong>Notificaciones de Prueba</strong>
                                            <br>
                                            <small class="text-muted">Notificaciones diarias de prueba del sistema (8:00 AM y 5:30 PM)</small>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="notify_inventory_low" 
                                               name="notification_types[inventory_low]" value="1" 
                                               {{ $preferences['notification_types']['inventory_low'] ?? true ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="notify_inventory_low">
                                            <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                                            <strong>Inventario Bajo</strong>
                                            <br>
                                            <small class="text-muted">Alertas cuando el inventario de alguna calidad esté por debajo del mínimo</small>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="notify_payment_overdue" 
                                               name="notification_types[payment_overdue]" value="1" 
                                               {{ $preferences['notification_types']['payment_overdue'] ?? true ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="notify_payment_overdue">
                                            <i class="fas fa-dollar-sign text-danger mr-2"></i>
                                            <strong>Pagos Vencidos</strong>
                                            <br>
                                            <small class="text-muted">Recordatorios de pagos pendientes de clientes</small>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="notify_sale_completed" 
                                               name="notification_types[sale_completed]" value="1" 
                                               {{ $preferences['notification_types']['sale_completed'] ?? true ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="notify_sale_completed">
                                            <i class="fas fa-check-circle text-success mr-2"></i>
                                            <strong>Ventas Completadas</strong>
                                            <br>
                                            <small class="text-muted">Notificaciones cuando se complete una venta importante</small>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="notify_system" 
                                               name="notification_types[system]" value="1" 
                                               {{ $preferences['notification_types']['system'] ?? true ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="notify_system">
                                            <i class="fas fa-cog text-secondary mr-2"></i>
                                            <strong>Notificaciones del Sistema</strong>
                                            <br>
                                            <small class="text-muted">Actualizaciones importantes y mantenimiento del sistema</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="card">
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-2"></i> Guardar Preferencias
                                </button>
                                <a href="{{ route('notifications.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-2"></i> Volver
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Info Sidebar -->
                <div class="col-md-4">
                    <div class="card bg-info">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle"></i> Información
                            </h3>
                        </div>
                        <div class="card-body">
                            <h6><strong>Sobre las Notificaciones</strong></h6>
                            <p class="small">
                                El sistema de notificaciones te mantiene informado sobre eventos importantes 
                                en tiempo real. Puedes personalizar qué notificaciones recibir y cómo recibirlas.
                            </p>
                            
                            <h6 class="mt-3"><strong>Canales de Notificación</strong></h6>
                            <ul class="small">
                                <li><strong>Email:</strong> Recibirás las notificaciones en tu correo electrónico registrado.</li>
                                <li><strong>Push:</strong> Las notificaciones aparecerán en tu navegador, incluso cuando no estés usando la aplicación.</li>
                            </ul>
                            
                            <h6 class="mt-3"><strong>Frecuencia</strong></h6>
                            <p class="small">
                                Las notificaciones se envían según la configuración del sistema:
                            </p>
                            <ul class="small">
                                <li>Pruebas diarias: 8:00 AM y 5:30 PM</li>
                                <li>Inventario: Cada 4 horas</li>
                                <li>Pagos vencidos: Diario a las 9:00 AM</li>
                                <li>Reportes: Según configuración</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle all notification types when main switches are toggled
    $('#email_enabled, #push_enabled').on('change', function() {
        const isChecked = $(this).is(':checked');
        if (!isChecked) {
            // Optional: disable notification types when channel is disabled
            // $('.custom-control-input[id^="notify_"]').prop('disabled', !isChecked);
        }
    });
});
</script>
@endpush