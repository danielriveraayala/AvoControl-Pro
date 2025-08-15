<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Suscripción Cancelada - AvoControl Pro</title>
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
        <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
        <h3>Suscripción Cancelada</h3>
    </div>

    <!-- Lockscreen Item -->
    <div class="lockscreen-item">
        <div class="lockscreen-image">
            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=dc3545&color=fff&size=128" alt="User Image">
        </div>

        <div class="lockscreen-credentials">
            <div class="input-group">
                <div class="card card-danger w-100">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-ban"></i>
                            Acceso Denegado
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">{{ $message }}</p>
                        
                        @if(isset($subscription))
                        <div class="alert alert-info">
                            <h5><i class="icon fas fa-info"></i> Detalles de la Suscripción</h5>
                            <p><strong>Plan:</strong> {{ $subscription->getPlanNameFormatted() }}</p>
                            <p><strong>Estado:</strong> {{ ucfirst($subscription->status) }}</p>
                            @if($subscription->cancelled_at)
                            <p><strong>Cancelada:</strong> {{ $subscription->cancelled_at->format('d/m/Y H:i') }}</p>
                            @endif
                            @if($subscription->cancellation_reason)
                            <p><strong>Motivo:</strong> {{ $subscription->cancellation_reason }}</p>
                            @endif
                        </div>
                        @endif

                        <div class="alert alert-warning">
                            <h5><i class="icon fas fa-lightbulb"></i> ¿Qué puedo hacer?</h5>
                            <ul class="mb-0">
                                <li>Seleccionar un nuevo plan de suscripción</li>
                                <li>Contactar a soporte para asistencia</li>
                                <li>Revisar nuestros planes actuales</li>
                            </ul>
                        </div>

                        <div class="alert alert-success">
                            <h5><i class="icon fas fa-gift"></i> ¡Vuelve con nosotros!</h5>
                            <p class="mb-0">Todos los nuevos suscriptores obtienen <strong>7 días de prueba gratis</strong> para explorar todas las funcionalidades.</p>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-6">
                                <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-block">
                                    <i class="fas fa-arrow-left"></i> Volver
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="mailto:avocontrol@kreativos.pro" class="btn btn-info btn-block">
                                    <i class="fas fa-envelope"></i> Contactar Soporte
                                </a>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-6">
                                <a href="{{ route('subscription.register', ['plan' => 'basic']) }}" class="btn btn-success btn-block">
                                    <i class="fas fa-credit-card"></i> Plan Básico
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('subscription.register', ['plan' => 'premium']) }}" class="btn btn-warning btn-block">
                                    <i class="fas fa-star"></i> Plan Premium
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="help-block text-center">
        <p>
            <strong>AvoControl Pro</strong> - Sistema de Gestión de Centros de Acopio<br>
            <small>¿Necesitas ayuda? Contacta: <a href="mailto:avocontrol@kreativos.pro">avocontrol@kreativos.pro</a></small>
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