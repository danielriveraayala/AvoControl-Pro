@extends('layouts.developer')

@section('title', 'Panel de Desarrollador')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:py-12 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg p-4 sm:p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-4 sm:mb-0">
                    <h1 class="text-2xl sm:text-3xl font-bold text-white mb-2">Panel de Desarrollador</h1>
                    <p class="text-blue-100 text-sm sm:text-base">Sistema de administración avanzada - AvoControl Pro</p>
                </div>
                <div class="text-left sm:text-right text-white">
                    <div class="text-sm opacity-90">Acceso Desarrollador</div>
                    <div class="text-base sm:text-lg font-semibold truncate">{{ auth()->user()->name }}</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mb-6 sm:mb-8">
            <h2 class="text-lg sm:text-xl font-semibold text-gray-900 mb-4 sm:mb-6">Accesos Rápidos</h2>
            
            <!-- Primary Actions -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-6">
                <a href="{{ route('developer.users.index') }}" class="group bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 hover:shadow-md hover:border-blue-300 transition-all duration-200">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="p-2 sm:p-3 rounded-lg bg-blue-100 text-blue-600 group-hover:bg-blue-200 transition-colors">
                                <svg class="w-5 h-5 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3 sm:ml-4 flex-1 min-w-0">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 group-hover:text-blue-700 transition-colors">Gestión de Usuarios</h3>
                            <p class="text-xs sm:text-sm text-gray-600 mt-1">Administrar usuarios, roles y permisos del sistema</p>
                            <div class="mt-2 sm:mt-3 flex items-center text-xs sm:text-sm text-blue-600">
                                <span>Administrar usuarios</span>
                                <svg class="ml-2 w-3 h-3 sm:w-4 sm:h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('developer.config.index') }}" class="group bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 hover:shadow-md hover:border-green-300 transition-all duration-200">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="p-2 sm:p-3 rounded-lg bg-green-100 text-green-600 group-hover:bg-green-200 transition-colors">
                                <svg class="w-5 h-5 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3 sm:ml-4 flex-1 min-w-0">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 group-hover:text-green-700 transition-colors">Configuración del Sistema</h3>
                            <p class="text-xs sm:text-sm text-gray-600 mt-1">SMTP, notificaciones push y configuraciones globales</p>
                            <div class="mt-2 sm:mt-3 flex items-center text-xs sm:text-sm text-green-600">
                                <span>Configurar sistema</span>
                                <svg class="ml-2 w-3 h-3 sm:w-4 sm:h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>

                <a href="{{ route('developer.backups.index') }}" class="group bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 hover:shadow-md hover:border-indigo-300 transition-all duration-200">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="p-2 sm:p-3 rounded-lg bg-indigo-100 text-indigo-600 group-hover:bg-indigo-200 transition-colors">
                                <svg class="w-5 h-5 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2H7a2 2 0 01-2-2V8z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3 sm:ml-4 flex-1 min-w-0">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 group-hover:text-indigo-700 transition-colors">Gestión de Respaldos</h3>
                            <p class="text-xs sm:text-sm text-gray-600 mt-1">Crear, descargar y restaurar respaldos del sistema</p>
                            <div class="mt-2 sm:mt-3 flex items-center text-xs sm:text-sm text-indigo-600">
                                <span>Administrar respaldos</span>
                                <svg class="ml-2 w-3 h-3 sm:w-4 sm:h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Secondary Actions -->
            <div class="bg-gray-50 rounded-xl p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4">Acciones del Sistema</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <button onclick="clearCache()" class="group bg-white rounded-lg border border-gray-200 p-3 sm:p-4 hover:border-yellow-300 hover:shadow-sm transition-all duration-200 text-left">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="p-2 rounded-md bg-yellow-100 text-yellow-600 group-hover:bg-yellow-200 transition-colors">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3 flex-1 min-w-0">
                                <h4 class="text-xs sm:text-sm font-semibold text-gray-900 group-hover:text-yellow-700 transition-colors">Limpiar Caché</h4>
                                <p class="text-xs text-gray-600">Optimizar rendimiento del sistema</p>
                            </div>
                            <div class="ml-2 hidden sm:block">
                                <svg class="w-4 h-4 text-gray-400 group-hover:text-yellow-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </button>

                    <button onclick="toggleMaintenance()" class="group bg-white rounded-lg border border-gray-200 p-3 sm:p-4 hover:border-red-300 hover:shadow-sm transition-all duration-200 text-left">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="p-2 rounded-md bg-red-100 text-red-600 group-hover:bg-red-200 transition-colors">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L4.316 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3 flex-1 min-w-0">
                                <h4 class="text-xs sm:text-sm font-semibold text-gray-900 group-hover:text-red-700 transition-colors">Modo Mantenimiento Frontend</h4>
                                <p class="text-xs text-gray-600">
                                    @if(\Cache::get('frontend_maintenance', false))
                                        <span class="text-yellow-600 font-medium">⚠️ Frontend en mantenimiento</span>
                                    @else
                                        Activar modo mantenimiento (solo frontend)
                                    @endif
                                </p>
                            </div>
                            <div class="ml-2 hidden sm:block">
                                <svg class="w-4 h-4 text-gray-400 group-hover:text-red-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <!-- System Health -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6 sm:mb-8">
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 sm:p-6 border-b border-gray-200">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">Estado del Sistema</h3>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Base de Datos</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $health['database'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $health['database'] ? 'Conectada' : 'Error' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Cache</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $health['cache'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $health['cache'] ? 'Funcionando' : 'Error' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Almacenamiento</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $health['storage'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $health['storage'] ? 'Disponible' : 'Error' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Cola de Trabajos</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $health['queue'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $health['queue'] ? 'Activa' : 'Inactiva' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow">
                <div class="p-4 sm:p-6 border-b border-gray-200">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">Métricas del Sistema</h3>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Total Usuarios</span>
                            <span class="text-lg font-semibold text-gray-900">{{ $metrics['total_users'] }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Usuarios Activos</span>
                            <span class="text-lg font-semibold text-green-600">{{ $metrics['active_users'] }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Usuarios Suspendidos</span>
                            <span class="text-lg font-semibold text-red-600">{{ $metrics['suspended_users'] }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Último Login</span>
                            <span class="text-sm text-gray-500">{{ $metrics['last_login'] ?? 'Sin datos' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-4 sm:p-6 border-b border-gray-200">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900">Información del Sistema</h3>
            </div>
            <div class="p-4 sm:p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Aplicación</h4>
                        <div class="mt-2 space-y-1">
                            <div class="text-sm"><span class="font-medium">Versión:</span> Laravel {{ app()->version() }}</div>
                            <div class="text-sm"><span class="font-medium">Entorno:</span> {{ config('app.env') }}</div>
                            <div class="text-sm"><span class="font-medium">Debug:</span> {{ config('app.debug') ? 'Activado' : 'Desactivado' }}</div>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Servidor</h4>
                        <div class="mt-2 space-y-1">
                            <div class="text-sm"><span class="font-medium">PHP:</span> {{ PHP_VERSION }}</div>
                            <div class="text-sm"><span class="font-medium">Memoria:</span> {{ ini_get('memory_limit') }}</div>
                            <div class="text-sm"><span class="font-medium">Tiempo límite:</span> {{ ini_get('max_execution_time') }}s</div>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Base de Datos</h4>
                        <div class="mt-2 space-y-1">
                            <div class="text-sm"><span class="font-medium">Conexión:</span> {{ config('database.default') }}</div>
                            <div class="text-sm"><span class="font-medium">Base:</span> {{ config('database.connections.mysql.database') }}</div>
                            <div class="text-sm"><span class="font-medium">Host:</span> {{ config('database.connections.mysql.host') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function clearCache() {
    DevAlert.confirm(
        '¿Limpiar Caché?',
        '¿Estás seguro de que deseas limpiar toda la caché del sistema? Esto puede afectar temporalmente el rendimiento.',
        'Sí, limpiar caché',
        'Cancelar'
    ).then((result) => {
        if (result.isConfirmed) {
            DevAlert.loading('Limpiando caché...', 'Por favor espera mientras se limpia la caché del sistema');
            
            fetch('{{ route("developer.clear-cache") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                DevAlert.close();
                handleAjaxResponse(data);
                if (data.success) {
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .catch(error => {
                DevAlert.close();
                handleFetchError(error);
            });
        }
    });
}

function toggleMaintenance() {
    const isMaintenanceActive = {{ \Cache::get('frontend_maintenance', false) ? 'true' : 'false' }};
    const action = isMaintenanceActive ? 'desactivar' : 'activar';
    const actionText = isMaintenanceActive ? 'Desactivar Mantenimiento' : 'Activar Mantenimiento';
    const confirmText = isMaintenanceActive ? 'Sí, desactivar' : 'Sí, activar';
    const warningText = isMaintenanceActive 
        ? 'El frontend volverá a estar disponible para todos los usuarios. El panel de desarrollador seguirá accesible.'
        : '⚠️ Esto pondrá el frontend en modo mantenimiento. Los usuarios no podrán acceder pero el panel de desarrollador seguirá funcionando.';
    
    DevAlert.confirm(
        actionText,
        warningText,
        confirmText,
        'Cancelar'
    ).then((result) => {
        if (result.isConfirmed) {
            DevAlert.loading(`${action === 'activar' ? 'Activando' : 'Desactivando'} modo mantenimiento...`);
            
            fetch('{{ route("developer.maintenance") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                DevAlert.close();
                handleAjaxResponse(data);
                if (data.success) {
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .catch(error => {
                DevAlert.close();
                handleFetchError(error);
            });
        }
    });
}
</script>
@endpush
@endsection