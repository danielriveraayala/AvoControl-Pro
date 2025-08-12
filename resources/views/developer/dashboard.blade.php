@extends('layouts.developer')

@section('title', 'Panel de Desarrollador')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Panel de Desarrollador</h1>
                    <p class="text-blue-100">Sistema de administración avanzada - AvoControl Pro</p>
                </div>
                <div class="text-right text-white">
                    <div class="text-sm opacity-90">Acceso Desarrollador</div>
                    <div class="text-lg font-semibold">{{ auth()->user()->name }}</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <a href="{{ route('developer.users.index') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Usuarios</h3>
                        <p class="text-sm text-gray-600">Gestionar usuarios</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('developer.config.index') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Configuración</h3>
                        <p class="text-sm text-gray-600">Sistema y SMTP</p>
                    </div>
                </div>
            </a>

            <button onclick="clearCache()" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow text-left">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Limpiar Cache</h3>
                        <p class="text-sm text-gray-600">Optimizar sistema</p>
                    </div>
                </div>
            </button>

            <button onclick="toggleMaintenance()" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow text-left">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L4.316 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Mantenimiento</h3>
                        <p class="text-sm text-gray-600">Activar/Desactivar</p>
                    </div>
                </div>
            </button>
        </div>

        <!-- System Health -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Estado del Sistema</h3>
                </div>
                <div class="p-6">
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
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Métricas del Sistema</h3>
                </div>
                <div class="p-6">
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
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Información del Sistema</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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

<!-- Scripts -->
<script>
function clearCache() {
    if (confirm('¿Estás seguro de que deseas limpiar toda la caché del sistema?')) {
        fetch('{{ route("developer.clear-cache") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Caché limpiada exitosamente');
                location.reload();
            } else {
                alert('Error al limpiar caché: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error al limpiar caché');
            console.error('Error:', error);
        });
    }
}

function toggleMaintenance() {
    const isDown = {{ app()->isDownForMaintenance() ? 'true' : 'false' }};
    const action = isDown ? 'desactivar' : 'activar';
    
    if (confirm(`¿Estás seguro de que deseas ${action} el modo de mantenimiento?`)) {
        fetch('{{ route("developer.maintenance") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error al cambiar modo de mantenimiento');
            console.error('Error:', error);
        });
    }
}
</script>
@endsection