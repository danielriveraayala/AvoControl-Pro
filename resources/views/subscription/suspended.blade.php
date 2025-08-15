<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Suscripción Suspendida - AvoControl Pro</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
</head>

<body class="hold-transition lockscreen">
<div class="lockscreen-wrapper">
    <!-- Header -->
    <div class="lockscreen-logo">
        <b>AvoControl</b> Pro
    </div>

    <!-- Main Content -->
    <div class="lockscreen-name text-center">
        <i class="fas fa-pause-circle fa-3x text-warning mb-3"></i>
        <h3>Suscripción Suspendida</h3>
    </div>

    <!-- Lockscreen Item -->
    <div class="lockscreen-item">
        <div class="lockscreen-image">
            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=ffc107&color=fff&size=128" alt="User Image">
        </div>

        <div class="lockscreen-credentials">
            <div class="input-group">
                <div class="card card-warning w-100">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-exclamation-triangle"></i>
                            Acceso Restringido
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">{{ $message }}</p>
                        
                        @if(isset($subscription))
                        <div class="alert alert-info">
                            <h5><i class="icon fas fa-info"></i> Detalles de la Suscripción</h5>
                            <p><strong>Plan:</strong> {{ $subscription->getPlanNameFormatted() }}</p>
                            <p><strong>Estado:</strong> {{ ucfirst($subscription->status) }}</p>
                            @if($subscription->suspended_at)
                            <p><strong>Suspendida:</strong> {{ $subscription->suspended_at->format('d/m/Y H:i') }}</p>
                            @endif
                            @if($subscription->suspension_reason)
                            <p><strong>Motivo:</strong> {{ $subscription->suspension_reason }}</p>
                            @endif
                        </div>
                        @endif

                        @if($canReactivate ?? false)
                        <div class="alert alert-success">
                            <h5><i class="icon fas fa-lightbulb"></i> ¿Cómo reactivar?</h5>
                            <ul class="mb-0">
                                <li>Contacta a nuestro equipo de soporte</li>
                                <li>Actualiza tu método de pago si es necesario</li>
                                <li>Resuelve cualquier pago pendiente</li>
                            </ul>
                        </div>
                        @else
                        <div class="alert alert-danger">
                            <h5><i class="icon fas fa-ban"></i> Suspensión Permanente</h5>
                            <p class="mb-0">Esta suscripción no puede ser reactivada automáticamente. Contacta a soporte para más información.</p>
                        </div>
                        @endif
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-6">
                                <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-block">
                                    <i class="fas fa-arrow-left"></i> Volver
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="mailto:avocontrol@kreativos.pro" class="btn btn-warning btn-block">
                                    <i class="fas fa-envelope"></i> Contactar Soporte
                                </a>
                            </div>
                        </div>
                        @if($canReactivate ?? false)
                        <div class="row mt-2">
                            <div class="col-12">
                                <a href="{{ route('subscription.register', ['plan' => 'basic']) }}" class="btn btn-success btn-block">
                                    <i class="fas fa-credit-card"></i> Renovar Suscripción
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="help-block text-center">
        <p>
            <strong>AvoControl Pro</strong> - Sistema de Gestión de Centros de Acopio<br>
            <small>Para reactivar tu suscripción, contacta a: <a href="mailto:avocontrol@kreativos.pro">avocontrol@kreativos.pro</a></small>
        </p>
    </div>

    <div class="lockscreen-footer text-center">
        Copyright &copy; {{ date('Y') }} <b><a href="https://kreativos.pro" class="text-black">Kreativos Pro</a></b><br>
        Todos los derechos reservados
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
</body>
</html>