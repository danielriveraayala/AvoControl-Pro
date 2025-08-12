@extends('layouts.developer')

@section('title', 'Configuración del Sistema')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Configuración del Sistema</h1>
                        <p class="text-sm text-gray-600">Gestiona la configuración global del sistema</p>
                    </div>
                    <a href="{{ route('developer.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        ← Volver al Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Configuration Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- SMTP Configuration -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Configuración SMTP</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $configs['smtp']['configured'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $configs['smtp']['configured'] ? 'Configurado' : 'Sin Configurar' }}
                        </span>
                    </div>
                </div>
                <div class="px-6 py-4">
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
                    <div class="mt-6 flex space-x-3">
                        <a href="{{ route('developer.config.smtp') }}" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-4 rounded-md text-sm font-medium">
                            Configurar SMTP
                        </a>
                        @if($configs['smtp']['configured'])
                            <button onclick="testSmtp()" class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-md text-sm font-medium">
                                Probar SMTP
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Push Notifications Configuration -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Notificaciones Push</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $configs['push']['configured'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $configs['push']['configured'] ? 'Configurado' : 'Sin Configurar' }}
                        </span>
                    </div>
                </div>
                <div class="px-6 py-4">
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
                    <div class="mt-6 flex space-x-3">
                        <a href="{{ route('developer.config.notifications') }}" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white text-center py-2 px-4 rounded-md text-sm font-medium">
                            Configurar Push
                        </a>
                        <a href="{{ route('developer.config.vapid') }}" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-center py-2 px-4 rounded-md text-sm font-medium">
                            Gestionar VAPID
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="mt-6 bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Información del Sistema</h3>
            </div>
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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

<!-- Test SMTP Modal -->
<div id="testSmtpModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Probar Configuración SMTP</h3>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Email de prueba:</label>
                <input type="email" id="testEmail" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="test@example.com">
            </div>
            <div class="flex justify-end space-x-3">
                <button onclick="closeTestSmtpModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                    Cancelar
                </button>
                <button onclick="sendTestEmail()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Enviar Prueba
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function testSmtp() {
    document.getElementById('testSmtpModal').classList.remove('hidden');
}

function closeTestSmtpModal() {
    document.getElementById('testSmtpModal').classList.add('hidden');
}

function sendTestEmail() {
    const email = document.getElementById('testEmail').value;
    if (!email) {
        alert('Por favor ingresa un email válido');
        return;
    }

    fetch('{{ route("developer.config.test-smtp") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ test_email: email })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            closeTestSmtpModal();
        }
    })
    .catch(error => {
        alert('Error al enviar email de prueba');
        console.error('Error:', error);
    });
}
</script>
@endsection