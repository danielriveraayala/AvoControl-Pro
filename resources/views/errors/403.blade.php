@extends('layouts.admin')

@section('title', 'Acceso Denegado')
@section('page-title', 'Acceso Denegado')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Error 403</li>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Main Error Section -->
        <div class="row">
            <div class="col-md-12">
                <div class="error-content text-center">
                    <!-- Error Icon and Code -->
                    <div class="mb-4">
                        <div class="error-icon mb-3">
                            <i class="fas fa-ban text-danger" style="font-size: 5rem; opacity: 0.8;"></i>
                        </div>
                        <h2 class="headline text-danger" style="font-size: 4rem; font-weight: bold;">403</h2>
                        <h3 class="text-muted">Acceso Denegado</h3>
                    </div>

                    <p class="lead mb-4">
                        No tienes los permisos necesarios para acceder a esta página o realizar esta acción.
                    </p>
                </div>
            </div>
        </div>

        <!-- User Information Section -->


        <!-- Action Buttons Section -->
        <div class="row justify-content-center mb-4">
            <div class="col-auto">
                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg mr-3">
                    <i class="fas fa-home mr-2"></i>
                    Ir al Dashboard
                </a>
                <button onclick="window.history.back()" class="btn btn-secondary btn-lg">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Regresar
                </button>
            </div>
        </div>

        <!-- Help Section for Non-Admin Users -->
        @auth
            @if(!auth()->user()->isSuperAdmin() && !auth()->user()->hasRole('admin'))
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6">
                        <div class="callout callout-info">
                            <h5><i class="fas fa-question-circle mr-2"></i>¿Necesitas acceso a esta función?</h5>
                            <p class="mb-2">
                                Si consideras que deberías tener acceso a esta funcionalidad, puedes:
                            </p>
                            <ul class="mb-3">
                                <li>Contactar al administrador del sistema</li>
                                <li>Solicitar permisos específicos para tu rol</li>
                                <li>Verificar que estás usando la cuenta correcta</li>
                            </ul>
                            <div class="alert alert-warning mb-0">
                                <small>
                                    <i class="fas fa-info-circle mr-1"></i>
                                    <strong>Tip:</strong> Proporciona detalles sobre qué funcionalidad necesitas y el
                                    motivo de tu solicitud para agilizar el proceso.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endauth

        <!-- Additional Information Section -->
        <div class="row justify-content-center mt-4">
            <div class="col-md-8">
                <div class="card card-outline card-secondary">
                    <div class="card-header text-center">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-info-circle mr-2"></i>
                            Información Adicional
                        </h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="info-box bg-light">
                                <span class="info-box-icon bg-primary">
                                    <i class="fas fa-shield-alt"></i>
                                </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Sistema de Permisos</span>
                                        <span class="info-box-number">Activo</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box bg-light">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Código de Error</span>
                                        <span class="info-box-number">403</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box bg-light">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-home"></i>
                                </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Dashboard</span>
                                        <span class="info-box-number">Disponible</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
