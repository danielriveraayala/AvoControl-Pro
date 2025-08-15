@extends('layouts.developer')

@section('title', 'Configuración del Sistema')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:py-12 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                    <div class="mb-4 sm:mb-0">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1">Configuración del Sistema</h1>
                        <p class="text-xs sm:text-sm text-gray-600">Gestiona la configuración global del sistema</p>
                    </div>
                    <a href="{{ route('developer.index') }}" class="inline-flex items-center px-3 sm:px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 self-start">
                        ← <span class="ml-1">Dashboard</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Configuration Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <!-- SMTP Configuration -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-2 sm:mb-0">Configuración SMTP</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $configs['smtp']['configured'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} self-start">
                            {{ $configs['smtp']['configured'] ? 'Configurado' : 'Sin Configurar' }}
                        </span>
                    </div>
                </div>
                <div class="px-4 sm:px-6 py-4">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Servidor:</span>
                            <span class="text-sm text-gray-900">{{ $configs['smtp']['host'] ?? 'No configurado' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Puerto:</span>
                            <span class="text-sm text-gray-900">{{ $configs['smtp']['port'] ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Encriptación:</span>
                            <span class="text-sm text-gray-900">{{ strtoupper($configs['smtp']['encryption'] ?? 'Ninguna') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Remitente:</span>
                            <span class="text-sm text-gray-900">{{ $configs['smtp']['from_address'] ?? 'No configurado' }}</span>
                        </div>
                    </div>
                    <div class="mt-4 sm:mt-6 flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                        <a href="{{ route('developer.config.smtp') }}" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-3 sm:px-4 rounded-md text-xs sm:text-sm font-medium">
                            Configurar SMTP
                        </a>
                        @if($configs['smtp']['configured'])
                            <button onclick="testSmtp()" class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 px-3 sm:px-4 rounded-md text-xs sm:text-sm font-medium">
                                Probar SMTP
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Push Notifications Configuration -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-2 sm:mb-0">Notificaciones Push</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $configs['push']['configured'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} self-start">
                            {{ $configs['push']['configured'] ? 'Configurado' : 'Sin Configurar' }}
                        </span>
                    </div>
                </div>
                <div class="px-4 sm:px-6 py-4">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Estado:</span>
                            <span class="text-sm text-gray-900">{{ $configs['push']['enabled'] ? 'Habilitado' : 'Deshabilitado' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Clave Pública:</span>
                            <span class="text-sm text-gray-900">{{ $configs['push']['public_key'] ? 'Configurada' : 'No configurada' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Clave Privada:</span>
                            <span class="text-sm text-gray-900">{{ $configs['push']['private_key'] ? 'Configurada' : 'No configurada' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Subject:</span>
                            <span class="text-sm text-gray-900">{{ $configs['push']['subject'] ?? 'No configurado' }}</span>
                        </div>
                    </div>
                    <div class="mt-4 sm:mt-6 flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                        <a href="{{ route('developer.config.notifications') }}" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white text-center py-2 px-3 rounded-md text-xs sm:text-sm font-medium">
                            Configurar Push
                        </a>
                        <a href="{{ route('developer.config.vapid') }}" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-center py-2 px-3 rounded-md text-xs sm:text-sm font-medium">
                            Gestionar VAPID
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications Management -->
        <div class="mt-4 sm:mt-6 bg-white shadow rounded-lg">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between">
                    <div class="mb-4 sm:mb-0">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-1">Gestión de Notificaciones</h3>
                        <p class="text-xs sm:text-sm text-gray-600">Administra todas las notificaciones del sistema</p>
                    </div>
                    <a href="{{ route('developer.config.notifications-manager') }}" class="inline-flex items-center px-3 sm:px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 self-start">
                        <i class="fas fa-bell mr-1 sm:mr-2"></i>
                        <span class="hidden sm:inline">Gestionar</span> Notificaciones
                    </a>
                </div>
            </div>
            <div class="px-4 sm:px-6 py-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">--</div>
                        <div class="text-sm text-gray-600">Total de Notificaciones</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">--</div>
                        <div class="text-sm text-gray-600">Enviadas Correctamente</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-600">--</div>
                        <div class="text-sm text-gray-600">Pendientes</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-red-600">--</div>
                        <div class="text-sm text-gray-600">Fallidas</div>
                    </div>
                </div>
                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-500">
                        Ver y administrar todas las notificaciones enviadas por email y push notifications
                    </p>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="mt-4 sm:mt-6 bg-white shadow rounded-lg">
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900">Información del Sistema</h3>
            </div>
            <div class="px-4 sm:px-6 py-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-2">Aplicación</h4>
                        <div class="space-y-1">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Nombre:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $configs['system']['app_name'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Entorno:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $configs['system']['app_env'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Debug:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $configs['system']['app_debug'] ? 'Activo' : 'Inactivo' }}</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-2">Localización</h4>
                        <div class="space-y-1">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Zona Horaria:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $configs['system']['timezone'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Idioma:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $configs['system']['locale'] }}</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-2">URL</h4>
                        <div class="space-y-1">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">URL Base:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $configs['system']['app_url'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@push('scripts')
<script>
function testSmtp() {
    DevAlert.confirm(
        'Probar Configuración SMTP',
        'Ingresa el email donde quieres recibir la prueba:',
        'Enviar Prueba',
        'Cancelar'
    ).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Email de Prueba',
                input: 'email',
                inputPlaceholder: 'ejemplo@correo.com',
                showCancelButton: true,
                confirmButtonText: 'Enviar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#3b82f6',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Por favor ingresa un email válido';
                    }
                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                        return 'El formato del email no es válido';
                    }
                }
            }).then((emailResult) => {
                if (emailResult.isConfirmed) {
                    DevAlert.loading('Enviando email...', 'Probando la configuración SMTP');
                    
                    fetch('{{ route("developer.config.smtp.test") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ test_email: emailResult.value })
                    })
                    .then(response => response.json())
                    .then(data => {
                        DevAlert.close();
                        handleAjaxResponse(data);
                    })
                    .catch(error => {
                        DevAlert.close();
                        handleFetchError(error);
                    });
                }
            });
        }
    });
}
</script>
@endpush
@endsection