<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant No Encontrado - {{ config('app.name') }}</title>
    
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">
    
    <style>
        .error-page {
            margin: 20px auto 0 auto;
        }
        .error-content {
            padding: 20px;
        }
        .btn-tenant {
            margin: 5px;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">

<div class="wrapper">
    <div class="content-wrapper" style="margin-left: 0;">
        <section class="content">
            <div class="error-page">
                <h2 class="headline text-warning">404</h2>
                
                <div class="error-content">
                    <h3><i class="fas fa-building text-warning"></i> Tenant No Encontrado</h3>
                    
                    <p>
                        No se encontró un tenant activo para el dominio <strong>{{ $domain ?? request()->getHost() }}</strong>.
                    </p>
                    
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> ¿Qué significa esto?</h5>
                        <p>{{ $message ?? 'El dominio o subdominio que intentas acceder no está configurado o no está activo en nuestro sistema.' }}</p>
                    </div>
                    
                    @auth
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-users"></i> Seleccionar Tenant</h3>
                            </div>
                            <div class="card-body">
                                <p>Como usuario autenticado, puedes acceder a los siguientes tenants:</p>
                                
                                @php
                                    $availableTenants = \App\Providers\TenantServiceProvider::getAvailableTenantsForUser();
                                @endphp
                                
                                @if($availableTenants->count() > 0)
                                    <div class="row">
                                        @foreach($availableTenants as $tenant)
                                            <div class="col-md-4">
                                                <div class="card card-outline card-primary">
                                                    <div class="card-header">
                                                        <h5 class="card-title">{{ $tenant->name }}</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <p class="card-text">
                                                            <small class="text-muted">Plan: {{ ucfirst($tenant->plan) }}</small><br>
                                                            <small class="text-muted">Estado: {{ ucfirst($tenant->status) }}</small>
                                                        </p>
                                                        <a href="{{ route('tenant.switch', $tenant->slug) }}" class="btn btn-primary">
                                                            <i class="fas fa-sign-in-alt"></i> Acceder
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        No tienes acceso a ningún tenant actualmente. Contacta al administrador del sistema.
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="card">
                            <div class="card-body">
                                <h5><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</h5>
                                <p>Si tienes una cuenta, inicia sesión para acceder a tus tenants disponibles:</p>
                                <a href="{{ route('login') }}" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                                </a>
                            </div>
                        </div>
                    @endauth
                    
                    <div class="mt-4">
                        <h5><i class="fas fa-question-circle"></i> Posibles Soluciones</h5>
                        <ul>
                            <li>Verifica que el dominio o subdominio sea correcto</li>
                            <li>Contacta al administrador del sistema si crees que esto es un error</li>
                            <li>Si eres administrador, verifica que el tenant esté configurado correctamente</li>
                        </ul>
                        
                        <div class="mt-3">
                            <a href="{{ url('/') }}" class="btn btn-default btn-tenant">
                                <i class="fas fa-home"></i> Ir al Inicio
                            </a>
                            
                            @if(auth()->check() && auth()->user()->hasRole('super_admin'))
                                <a href="{{ route('developer.index') }}" class="btn btn-warning btn-tenant">
                                    <i class="fas fa-tools"></i> Panel de Desarrollador
                                </a>
                            @endif
                            
                            <button onclick="history.back()" class="btn btn-secondary btn-tenant">
                                <i class="fas fa-arrow-left"></i> Regresar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- AdminLTE JS -->
<script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>

</body>
</html>