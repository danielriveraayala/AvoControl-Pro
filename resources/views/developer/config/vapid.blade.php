@extends('layouts.developer')

@section('title', 'Gestión de Llaves VAPID')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Gestión de Llaves VAPID</h1>
                        <p class="text-sm text-gray-600">Administra las llaves VAPID para notificaciones push</p>
                    </div>
                    <a href="{{ route('developer.config.notifications') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        ← Volver a Notificaciones
                    </a>
                </div>
            </div>
        </div>

        <!-- VAPID Information -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">¿Qué son las llaves VAPID?</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>VAPID (Voluntary Application Server Identification) son llaves criptográficas que identifican tu aplicación ante los servicios de push de los navegadores. Son necesarias para enviar notificaciones push a los usuarios.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current VAPID Keys -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Llaves VAPID Actuales</h3>
            </div>
            <div class="px-6 py-6">
                @if($vapidConfig['public_key'] && $vapidConfig['private_key'])
                    <div class="space-y-4">
                        <!-- Public Key -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Clave Pública</label>
                            <div class="flex">
                                <input type="text" id="publicKey" value="{{ $vapidConfig['public_key'] }}" readonly
                                       class="flex-1 px-3 py-2 bg-gray-50 border border-gray-300 rounded-l-md text-sm font-mono">
                                <button onclick="copyToClipboard('publicKey')" class="px-3 py-2 bg-gray-200 border border-l-0 border-gray-300 rounded-r-md hover:bg-gray-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Private Key -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Clave Privada</label>
                            <div class="flex">
                                <input type="password" id="privateKey" value="{{ $vapidConfig['private_key'] }}" readonly
                                       class="flex-1 px-3 py-2 bg-gray-50 border border-gray-300 rounded-l-md text-sm font-mono">
                                <button onclick="toggleVisibility('privateKey')" class="px-3 py-2 bg-gray-200 border border-l-0 border-gray-300 hover:bg-gray-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                                <button onclick="copyToClipboard('privateKey')" class="px-3 py-2 bg-gray-200 border border-l-0 border-gray-300 rounded-r-md hover:bg-gray-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Subject -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                            <div class="flex">
                                <input type="text" id="subject" value="{{ $vapidConfig['subject'] }}" readonly
                                       class="flex-1 px-3 py-2 bg-gray-50 border border-gray-300 rounded-l-md text-sm">
                                <button onclick="copyToClipboard('subject')" class="px-3 py-2 bg-gray-200 border border-l-0 border-gray-300 rounded-r-md hover:bg-gray-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="bg-green-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-sm font-medium text-green-900">Llaves VAPID configuradas correctamente</span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Sin llaves VAPID</h3>
                        <p class="mt-1 text-sm text-gray-500">No hay llaves VAPID configuradas. Genera un nuevo par para comenzar.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Acciones</h3>
            </div>
            <div class="px-6 py-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Generate New Keys -->
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h4 class="text-sm font-medium text-blue-900 mb-2">Generar Nuevas Llaves</h4>
                        <p class="text-sm text-blue-700 mb-3">Crea un nuevo par de llaves VAPID. Esto invalidará las llaves actuales.</p>
                        <button onclick="generateNewKeys()" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md text-sm font-medium">
                            Generar Nuevas Llaves
                        </button>
                    </div>

                    <!-- Download Keys -->
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h4 class="text-sm font-medium text-green-900 mb-2">Descargar Llaves</h4>
                        <p class="text-sm text-green-700 mb-3">Descarga las llaves actuales en formato JSON para respaldo.</p>
                        <button onclick="downloadKeys()" class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-md text-sm font-medium" {{ !$vapidConfig['public_key'] ? 'disabled' : '' }}>
                            Descargar JSON
                        </button>
                    </div>
                </div>

                <!-- Security Warning -->
                <div class="mt-6 bg-red-50 border-l-4 border-red-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L4.316 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Advertencia de Seguridad</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>La clave privada VAPID debe mantenerse en secreto</li>
                                    <li>No compartas la clave privada con terceros</li>
                                    <li>Genera nuevas llaves si sospechas que han sido comprometidas</li>
                                    <li>Guarda un respaldo seguro de las llaves</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    element.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        document.execCommand('copy');
        showToast('Copiado al portapapeles');
    } catch (err) {
        console.error('Error al copiar:', err);
        showToast('Error al copiar', 'error');
    }
}

function toggleVisibility(elementId) {
    const element = document.getElementById(elementId);
    if (element.type === 'password') {
        element.type = 'text';
    } else {
        element.type = 'password';
    }
}

function generateNewKeys() {
    if (confirm('⚠️ ¿Estás seguro de que deseas generar nuevas llaves VAPID?\n\nEsto invalidará las llaves actuales y será necesario reconfigurar todas las suscripciones de notificaciones push.')) {
        fetch('{{ route("developer.config.generate-vapid") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Nuevas llaves VAPID generadas exitosamente', 'success');
                // Reload page to show new keys
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast('Error al generar llaves VAPID: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showToast('Error al generar llaves VAPID', 'error');
            console.error('Error:', error);
        });
    }
}

function downloadKeys() {
    const keys = {
        public_key: '{{ $vapidConfig["public_key"] }}',
        private_key: '{{ $vapidConfig["private_key"] }}',
        subject: '{{ $vapidConfig["subject"] }}',
        generated_at: new Date().toISOString(),
        application: 'AvoControl Pro'
    };
    
    const dataStr = JSON.stringify(keys, null, 2);
    const dataBlob = new Blob([dataStr], {type: 'application/json'});
    
    const link = document.createElement('a');
    link.href = URL.createObjectURL(dataBlob);
    link.download = 'vapid-keys-avocontrol-' + new Date().toISOString().split('T')[0] + '.json';
    link.click();
    
    showToast('Llaves VAPID descargadas', 'success');
}

function showToast(message, type = 'info') {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-6 py-3 rounded-md text-white z-50 ${
        type === 'error' ? 'bg-red-500' : 
        type === 'success' ? 'bg-green-500' : 
        'bg-blue-500'
    }`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>
@endsection