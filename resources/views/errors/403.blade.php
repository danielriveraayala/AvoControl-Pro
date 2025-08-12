@extends('layouts.admin')

@section('title', 'Acceso Denegado')
@section('page-title', 'Acceso Denegado')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Error 403</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="error-page text-center">
                <!-- Error Icon -->
                <div class="error-icon mb-4">
                    <i class="fas fa-shield-alt text-danger" style="font-size: 4rem;"></i>
                </div>
                
                <!-- Error Code and Title -->
                <h2 class="headline text-danger font-weight-bold">403</h2>
                <h3>Acceso Denegado</h3>
                <p class="text-muted mb-4">
                    No tienes los permisos necesarios para acceder a esta página o realizar esta acción.
                </p>

                <!-- User Information Card -->
                @auth
                <div class="card card-outline card-warning mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user mr-2"></i>
                            Información del Usuario
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <strong>Usuario:</strong> {{ auth()->user()->name }}
                            </div>
                            <div class="col-sm-6">
                                <strong>Roles:</strong>
                                @if(auth()->user()->roles->count() > 0)
                                    <span class="badge badge-info">
                                        {{ auth()->user()->roles->pluck('display_name')->join(', ') }}
                                    </span>
                                @else
                                    <span class="badge badge-secondary">
                                        {{ ucfirst(auth()->user()->role) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endauth

                <!-- Action Buttons -->
                <div class="row">
                    <div class="col-sm-6 mb-3">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-home mr-2"></i>
                            Ir al Dashboard
                        </a>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <button onclick="window.history.back()" class="btn btn-secondary btn-lg btn-block">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Regresar
                        </button>
                    </div>
                </div>

                <!-- Help Text for Non-Admin Users -->
                @auth
                @if(!auth()->user()->isSuperAdmin() && !auth()->user()->hasRole('admin'))
                <div class="alert alert-info mt-4">
                    <h6><i class="fas fa-info-circle mr-2"></i>¿Necesitas acceso a esta función?</h6>
                    <small>
                        Contacta al administrador del sistema para solicitar permisos adicionales.
                        Tu solicitud será revisada y procesada según las políticas de la empresa.
                    </small>
                </div>
                @endif
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection