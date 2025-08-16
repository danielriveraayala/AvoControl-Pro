@extends('layouts.developer')

@section('title', 'Detalles del Tenant')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:py-12 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col space-y-4 sm:flex-row sm:justify-between sm:items-center sm:space-y-0">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 sm:h-12 sm:w-12">
                            <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-full bg-gradient-to-r from-indigo-500 to-blue-600 flex items-center justify-center">
                                <span class="text-sm sm:text-lg font-bold text-white">
                                    {{ substr($tenant->name, 0, 2) }}
                                </span>
                            </div>
                        </div>
                        <div class="ml-3 sm:ml-4 min-w-0">
                            <h1 class="text-lg sm:text-2xl font-bold text-gray-900 truncate">{{ $tenant->name }}</h1>
                            <p class="text-xs sm:text-sm text-gray-600 truncate">{{ $tenant->slug }}.avocontrol.com</p>
                        </div>
                    </div>
                    <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-3">
                        <a href="{{ route('developer.tenants.edit', $tenant) }}" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Editar Tenant
                        </a>
                        <a href="{{ route('developer.tenants.index') }}" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Volver a Tenants
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Banner -->
        @if($tenant->status == 'suspended')
            <div class="bg-red-100 border-l-4 border-red-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L4.316 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Tenant Suspendido</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p>Este tenant está actualmente suspendido y no puede acceder al sistema.</p>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($tenant->plan == 'trial')
            <div class="bg-yellow-100 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Plan de Prueba</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>Este tenant está en período de prueba.
                            @if($tenant->trial_ends_at)
                                Expira el {{ $tenant->trial_ends_at->format('d/m/Y') }}
                            @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Usuarios</dt>
                                <dd class="text-lg font-semibold text-gray-900">{{ $tenant->users()->count() }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-2 0H7m5 0v-5a2 2 0 00-2-2H8a2 2 0 00-2 2v5m3 0h4z"></path>
                            </svg>
                        </div>
                        <div class="ml-3 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Suscripciones</dt>
                                <dd class="text-lg font-semibold text-gray-900">{{ $tenant->subscriptions()->count() }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                @if($tenant->plan == 'trial') bg-yellow-100 text-yellow-800
                                @elseif($tenant->plan == 'basic') bg-blue-100 text-blue-800
                                @elseif($tenant->plan == 'premium') bg-purple-100 text-purple-800
                                @elseif($tenant->plan == 'enterprise') bg-indigo-100 text-indigo-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($tenant->plan) }}
                            </span>
                        </div>
                        <div class="ml-3 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Plan Actual</dt>
                                <dd class="text-lg font-semibold text-gray-900">
                                    @if($tenant->plan == 'trial') Prueba
                                    @elseif($tenant->plan == 'basic') Básico
                                    @elseif($tenant->plan == 'premium') Premium
                                    @elseif($tenant->plan == 'enterprise') Empresarial
                                    @else {{ ucfirst($tenant->plan) }}
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                @if($tenant->status == 'active') bg-green-100 text-green-800
                                @elseif($tenant->status == 'suspended') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($tenant->status) }}
                            </span>
                        </div>
                        <div class="ml-3 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Estado</dt>
                                <dd class="text-lg font-semibold text-gray-900">
                                    @if($tenant->status == 'active') Activo
                                    @elseif($tenant->status == 'suspended') Suspendido
                                    @elseif($tenant->status == 'inactive') Inactivo
                                    @else {{ ucfirst($tenant->status) }}
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tenant Details -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Basic Information -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Información Básica
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <dl class="space-y-4">
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">ID del Sistema</dt>
                            <dd class="text-sm text-gray-900 font-mono bg-gray-100 px-2 py-1 rounded">#{{ $tenant->id }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Nombre Completo</dt>
                            <dd class="text-sm text-gray-900 font-medium">{{ $tenant->name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Identificador (Slug)</dt>
                            <dd class="text-sm text-gray-900 font-mono">{{ $tenant->slug }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">URL del Tenant</dt>
                            <dd class="text-sm">
                                <a href="http://{{ $tenant->slug }}.avocontrol.com" target="_blank" 
                                   class="text-blue-600 hover:text-blue-800 font-medium">
                                    {{ $tenant->slug }}.avocontrol.com
                                    <svg class="w-3 h-3 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                </a>
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Email de Contacto</dt>
                            <dd class="text-sm">
                                <a href="mailto:{{ $tenant->email }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $tenant->email }}
                                </a>
                            </dd>
                        </div>
                        @if($tenant->phone)
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Teléfono</dt>
                            <dd class="text-sm text-gray-900">{{ $tenant->phone }}</dd>
                        </div>
                        @endif
                        @if($tenant->address)
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Dirección</dt>
                            <dd class="text-sm text-gray-900">{{ $tenant->address }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Usage Information -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Información de Uso</h3>
                </div>
                <div class="px-6 py-4">
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Usuarios</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $tenant->users()->count() }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Suscripciones</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $tenant->subscriptions()->count() }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Creado</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $tenant->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Actualizado</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $tenant->updated_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        @if($tenant->last_activity_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Última Actividad</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $tenant->last_activity_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Acciones</h3>
            </div>
            <div class="px-6 py-4">
                <div class="flex flex-wrap gap-4">
                    @if($tenant->status == 'active')
                        <button onclick="suspendTenant({{ $tenant->id }})" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded text-sm">
                            Suspender Tenant
                        </button>
                    @elseif($tenant->status == 'suspended')
                        <button onclick="activateTenant({{ $tenant->id }})" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm">
                            Activar Tenant
                        </button>
                    @endif
                    
                    <button onclick="refreshUsage({{ $tenant->id }})" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm">
                        Actualizar Estadísticas
                    </button>
                    
                    <a href="http://{{ $tenant->slug }}.avocontrol.com" target="_blank" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded text-sm">
                        Ver Sitio Tenant
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Add your JavaScript functions here for actions
function suspendTenant(tenantId) {
    // Implementation needed
    alert('Función suspender tenant - implementar');
}

function activateTenant(tenantId) {
    // Implementation needed  
    alert('Función activar tenant - implementar');
}

function refreshUsage(tenantId) {
    // Implementation needed
    alert('Función actualizar estadísticas - implementar');
}
</script>
@endpush
@endsection
