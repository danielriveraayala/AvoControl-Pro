@extends('layouts.admin')

@section('title', 'Seleccionar Tenant')

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Seleccionar Tenant</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Seleccionar Tenant</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        
        @if($availableTenants->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-building"></i> Tenants Disponibles</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Selecciona el tenant al que deseas acceder:</p>
                    
                    <div class="row">
                        @foreach($availableTenants as $tenant)
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card {{ $tenant->id == auth()->user()->current_tenant_id ? 'card-primary' : 'card-outline card-secondary' }}">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            @if($tenant->logo_path)
                                                <img src="{{ asset($tenant->logo_path) }}" alt="{{ $tenant->name }}" class="img-size-32 mr-2">
                                            @else
                                                <i class="fas fa-building mr-2"></i>
                                            @endif
                                            {{ $tenant->name }}
                                            @if($tenant->id == auth()->user()->current_tenant_id)
                                                <span class="badge badge-primary ml-2">Actual</span>
                                            @endif
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <small class="text-muted">Plan</small>
                                                <div class="font-weight-bold text-capitalize">{{ $tenant->plan }}</div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Estado</small>
                                                <div>
                                                    <span class="badge badge-{{ $tenant->status == 'active' ? 'success' : 'warning' }}">
                                                        {{ ucfirst($tenant->status) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        @if($tenant->domain)
                                            <div class="mt-2">
                                                <small class="text-muted">Dominio</small>
                                                <div class="text-info">{{ $tenant->domain }}</div>
                                            </div>
                                        @endif
                                        
                                        <div class="mt-3">
                                            <small class="text-muted">Tu rol</small>
                                            <div class="text-success">
                                                @php
                                                    $userRole = auth()->user()->getRoleInTenant($tenant->id);
                                                @endphp
                                                {{ \App\Models\TenantUser::getAvailableRoles()[$userRole] ?? $userRole }}
                                            </div>
                                        </div>

                                        @if($tenant->isOnTrial())
                                            <div class="alert alert-info mt-3 mb-0">
                                                <i class="fas fa-clock"></i>
                                                <small>Período de prueba hasta: {{ $tenant->trial_ends_at->format('d/m/Y') }}</small>
                                            </div>
                                        @endif

                                        @if($tenant->hasExpired())
                                            <div class="alert alert-warning mt-3 mb-0">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <small>Suscripción expirada</small>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="card-footer">
                                        @if($tenant->id == auth()->user()->current_tenant_id)
                                            <button class="btn btn-primary btn-block" disabled>
                                                <i class="fas fa-check"></i> Tenant Actual
                                            </button>
                                        @else
                                            <a href="{{ route('tenant.switch', $tenant->slug) }}" class="btn btn-success btn-block">
                                                <i class="fas fa-sign-in-alt"></i> Cambiar a este Tenant
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-building text-muted" style="font-size: 4rem;"></i>
                    <h4 class="mt-3">No hay tenants disponibles</h4>
                    <p class="text-muted">
                        No tienes acceso a ningún tenant actualmente. 
                        Contacta al administrador del sistema para obtener acceso.
                    </p>
                    
                    <div class="mt-4">
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al Dashboard
                        </a>
                        
                        @if(auth()->user()->hasRole('super_admin'))
                            <a href="{{ route('developer.index') }}" class="btn btn-warning">
                                <i class="fas fa-tools"></i> Panel de Desarrollador
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Information Card -->
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Información sobre Tenants</h3>
            </div>
            <div class="card-body">
                <p><strong>¿Qué es un Tenant?</strong></p>
                <p>Un tenant representa una instancia separada de AvoControl Pro, permitiendo que múltiples organizaciones utilicen el mismo sistema manteniendo sus datos completamente aislados.</p>
                
                <p><strong>Características de los Tenants:</strong></p>
                <ul>
                    <li><i class="fas fa-shield-alt text-success"></i> Aislamiento completo de datos entre tenants</li>
                    <li><i class="fas fa-users text-primary"></i> Gestión independiente de usuarios y permisos</li>
                    <li><i class="fas fa-cog text-info"></i> Configuraciones personalizables por tenant</li>
                    <li><i class="fas fa-chart-line text-warning"></i> Límites de uso basados en el plan contratado</li>
                </ul>
                
                <div class="alert alert-light">
                    <i class="fas fa-lightbulb text-warning"></i>
                    <strong>Tip:</strong> Puedes cambiar entre tenants en cualquier momento usando el selector en la barra de navegación.
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.card-outline {
    border-width: 2px;
}

.card-primary {
    border-color: #007bff;
}

.img-size-32 {
    width: 32px;
    height: 32px;
    object-fit: cover;
}
</style>
@endpush