@extends('layouts.admin')

@section('title', 'Dashboard de Suscripción')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Dashboard de Suscripción</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Suscripción</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        
        <!-- Subscription Status Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card {{ $subscription->isOnTrial() ? 'card-warning' : ($subscription->isActive() ? 'card-success' : 'card-danger') }}">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-{{ $subscription->isOnTrial() ? 'gift' : ($subscription->isActive() ? 'check-circle' : 'exclamation-triangle') }} mr-2"></i>
                            Estado de la Suscripción
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="display-4 mb-2">
                                        @if($subscription->isOnTrial())
                                            <i class="fas fa-gift text-warning"></i>
                                        @elseif($subscription->isActive())
                                            <i class="fas fa-check-circle text-success"></i>
                                        @else
                                            <i class="fas fa-exclamation-triangle text-danger"></i>
                                        @endif
                                    </div>
                                    <h4 class="text-{{ $subscription->isOnTrial() ? 'warning' : ($subscription->isActive() ? 'success' : 'danger') }}">
                                        {{ strtoupper($subscription->getStatusLabel()) }}
                                    </h4>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>Plan Actual:</strong><br>
                                        <span class="h5">{{ $subscription->getPlanNameFormatted() }}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Precio:</strong><br>
                                        <span class="h5">${{ number_format($subscription->amount, 2) }} USD/mes</span>
                                    </div>
                                    <div class="col-md-4">
                                        @if($subscription->isOnTrial())
                                            <strong>Trial expira:</strong><br>
                                            <span class="h5 text-warning">
                                                {{ $subscription->trial_ends_at->format('d/m/Y H:i') }}
                                                <br><small>({{ $subscription->getDaysUntilTrialEnd() }} días restantes)</small>
                                            </span>
                                        @else
                                            <strong>Próximo cobro:</strong><br>
                                            <span class="h5">{{ $subscription->getNextBillingDateFormatted() }}</span>
                                        @endif
                                    </div>
                                </div>
                                
                                @if($subscription->isOnTrial() && $subscription->getDaysUntilTrialEnd() <= 3)
                                    <div class="alert alert-warning mt-3 mb-0">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        <strong>¡Tu trial está próximo a vencer!</strong> 
                                        Actualiza tu plan para continuar usando AvoControl Pro sin interrupciones.
                                        <a href="{{ route('subscription.plans') }}" class="btn btn-warning btn-sm ml-2">
                                            <i class="fas fa-arrow-up mr-1"></i> Actualizar Plan
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usage Statistics -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card card-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="card-title mb-0">{{ $usageStats['users'] }}</h3>
                                <p class="text-muted mb-0">Usuarios</p>
                            </div>
                            <div class="text-primary">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                        <div class="progress mt-2" style="height: 5px;">
                            @php
                                $maxUsers = $planLimits['max_users'] ?? 1;
                                $userPercent = ($usageStats['users'] / $maxUsers) * 100;
                            @endphp
                            <div class="progress-bar bg-primary" style="width: {{ min($userPercent, 100) }}%"></div>
                        </div>
                        <small class="text-muted">Límite: {{ $maxUsers }}</small>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card card-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="card-title mb-0">{{ $usageStats['lots_this_month'] }}</h3>
                                <p class="text-muted mb-0">Lotes este mes</p>
                            </div>
                            <div class="text-info">
                                <i class="fas fa-boxes fa-2x"></i>
                            </div>
                        </div>
                        <div class="progress mt-2" style="height: 5px;">
                            @php
                                $maxLots = $planLimits['max_lots_per_month'] ?? 50;
                                $lotsPercent = ($usageStats['lots_this_month'] / $maxLots) * 100;
                            @endphp
                            <div class="progress-bar bg-info" style="width: {{ min($lotsPercent, 100) }}%"></div>
                        </div>
                        <small class="text-muted">Límite: {{ $maxLots === -1 ? 'Ilimitado' : number_format($maxLots) }}</small>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card card-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="card-title mb-0">{{ $usageStats['sales_this_month'] }}</h3>
                                <p class="text-muted mb-0">Ventas este mes</p>
                            </div>
                            <div class="text-success">
                                <i class="fas fa-shopping-cart fa-2x"></i>
                            </div>
                        </div>
                        <div class="progress mt-2" style="height: 5px;">
                            <div class="progress-bar bg-success" style="width: 100%"></div>
                        </div>
                        <small class="text-muted">Sin límite</small>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card card-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="card-title mb-0">${{ number_format($usageStats['revenue_this_month'], 2) }}</h3>
                                <p class="text-muted mb-0">Ingresos este mes</p>
                            </div>
                            <div class="text-warning">
                                <i class="fas fa-dollar-sign fa-2x"></i>
                            </div>
                        </div>
                        <div class="progress mt-2" style="height: 5px;">
                            <div class="progress-bar bg-warning" style="width: 100%"></div>
                        </div>
                        <small class="text-muted">Sin límite</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Payments -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-credit-card mr-2"></i>
                            Historial de Pagos
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        @if($recentPayments->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Monto</th>
                                            <th>Estado</th>
                                            <th>Tipo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentPayments as $payment)
                                        <tr>
                                            <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                                            <td>${{ number_format($payment->amount, 2) }}</td>
                                            <td>
                                                <span class="badge badge-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'failed' ? 'danger' : 'warning') }}">
                                                    {{ ucfirst($payment->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ ucfirst($payment->type) }}</span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="p-4 text-center text-muted">
                                <i class="fas fa-credit-card fa-3x mb-3 opacity-50"></i>
                                <p>No hay pagos registrados aún.</p>
                                @if($subscription->isOnTrial())
                                    <p><small>Los pagos comenzarán cuando termine tu trial.</small></p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Plan Features -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list mr-2"></i>
                            Características del Plan
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="fas fa-users text-primary mr-2"></i> Usuarios máximos</span>
                                <span class="badge badge-primary">{{ $planLimits['max_users'] ?? 1 }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="fas fa-boxes text-info mr-2"></i> Lotes por mes</span>
                                <span class="badge badge-info">
                                    {{ ($planLimits['max_lots_per_month'] ?? 50) === -1 ? 'Ilimitado' : number_format($planLimits['max_lots_per_month'] ?? 50) }}
                                </span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="fas fa-hdd text-success mr-2"></i> Almacenamiento</span>
                                <span class="badge badge-success">{{ $planLimits['storage_gb'] ?? '0.5' }}GB</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="fas fa-chart-bar text-warning mr-2"></i> Reportes avanzados</span>
                                <span class="badge badge-{{ ($planLimits['advanced_reports'] ?? false) ? 'success' : 'secondary' }}">
                                    {{ ($planLimits['advanced_reports'] ?? false) ? 'Incluido' : 'No incluido' }}
                                </span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="fas fa-headset text-danger mr-2"></i> Soporte</span>
                                <span class="badge badge-info">{{ ucfirst($planLimits['support_level'] ?? 'Ninguno') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        @if($subscription->isOnTrial())
                            <a href="{{ route('subscription.plans') }}" class="btn btn-success btn-lg mr-3">
                                <i class="fas fa-arrow-up mr-2"></i>
                                Actualizar Plan
                            </a>
                        @else
                            <a href="{{ route('subscription.plans') }}" class="btn btn-info btn-lg mr-3">
                                <i class="fas fa-exchange-alt mr-2"></i>
                                Cambiar Plan
                            </a>
                        @endif
                        
                        @if(!$subscription->isOnTrial())
                            <button class="btn btn-danger btn-lg" onclick="cancelSubscription()">
                                <i class="fas fa-times mr-2"></i>
                                Cancelar Suscripción
                            </button>
                        @endif
                        
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-lg ml-3">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Volver al Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Subscription Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Cancelar Suscripción</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('subscription.cancel') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>¿Estás seguro?</strong> Esta acción cancelará tu suscripción.
                    </div>
                    <p>Tu acceso continuará hasta el final del período facturado actual: <strong>{{ $subscription->getNextBillingDateFormatted() }}</strong></p>
                    
                    <div class="form-group">
                        <label for="cancellation_reason">Motivo de cancelación (opcional):</label>
                        <select name="reason" id="cancellation_reason" class="form-control">
                            <option value="">Seleccionar motivo...</option>
                            <option value="too_expensive">Muy costoso</option>
                            <option value="not_using">No lo estoy usando</option>
                            <option value="missing_features">Faltan características</option>
                            <option value="switching_service">Cambiando de servicio</option>
                            <option value="other">Otro</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No, mantener suscripción</button>
                    <button type="submit" class="btn btn-danger">Sí, cancelar suscripción</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function cancelSubscription() {
    $('#cancelModal').modal('show');
}
</script>
@endpush