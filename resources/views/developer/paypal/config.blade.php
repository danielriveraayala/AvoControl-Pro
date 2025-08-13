@extends('layouts.developer')

@section('title', 'Configuración PayPal API')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i class="fab fa-paypal text-blue-600 mr-3"></i>
                        Configuración PayPal API
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">Configure las credenciales de PayPal para sandbox y producción</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('developer.paypal.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver al Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Configuration Form -->
        <form method="POST" action="{{ route('developer.paypal.config.update') }}" class="space-y-6">
            @csrf
            
            <!-- Environment Selection -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-server text-gray-500 mr-2"></i>
                    Entorno Activo
                </h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 {{ $config['environment'] === 'sandbox' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200' }}">
                        <input type="radio" name="environment" value="sandbox" {{ $config['environment'] === 'sandbox' ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                        <div class="ml-3">
                            <div class="flex items-center">
                                <i class="fas fa-flask text-yellow-500 mr-2"></i>
                                <span class="text-sm font-medium text-gray-900">Sandbox (Pruebas)</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Entorno de desarrollo para pruebas</p>
                        </div>
                    </label>
                    
                    <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 {{ $config['environment'] === 'live' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200' }}">
                        <input type="radio" name="environment" value="live" {{ $config['environment'] === 'live' ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                        <div class="ml-3">
                            <div class="flex items-center">
                                <i class="fas fa-globe text-green-500 mr-2"></i>
                                <span class="text-sm font-medium text-gray-900">Producción (Live)</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Entorno de producción real</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Sandbox Configuration -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-flask text-yellow-500 mr-2"></i>
                    Configuración Sandbox
                </h2>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-yellow-600"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Cómo obtener credenciales Sandbox</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <ol class="list-decimal list-inside space-y-1">
                                    <li>Ingresa a <a href="https://developer.paypal.com/developer/applications/" target="_blank" class="underline">PayPal Developer Dashboard</a></li>
                                    <li>Crea una nueva aplicación en modo Sandbox</li>
                                    <li>Copia las credenciales Client ID y Client Secret</li>
                                    <li>Configura los webhooks y copia el Webhook ID</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="sandbox_client_id" class="block text-sm font-medium text-gray-700">Client ID</label>
                        <input type="text" name="sandbox_client_id" id="sandbox_client_id" 
                               value="{{ $config['sandbox']['client_id'] }}"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="AVxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                        @error('sandbox_client_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="sandbox_client_secret" class="block text-sm font-medium text-gray-700">Client Secret</label>
                        <input type="password" name="sandbox_client_secret" id="sandbox_client_secret" 
                               value="{{ $config['sandbox']['client_secret'] }}"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="ELxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                        @error('sandbox_client_secret')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="sandbox_webhook_id" class="block text-sm font-medium text-gray-700">Webhook ID</label>
                        <input type="text" name="sandbox_webhook_id" id="sandbox_webhook_id" 
                               value="{{ $config['sandbox']['webhook_id'] }}"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="WH-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                        @error('sandbox_webhook_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">URL del webhook: {{ url('/paypal/webhook') }}</p>
                    </div>
                </div>
            </div>

            <!-- Live Configuration -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-globe text-green-500 mr-2"></i>
                    Configuración Producción (Live)
                </h2>
                
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">¡Atención!</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p>Estas credenciales se utilizarán para transacciones reales con dinero real. Asegúrate de que:</p>
                                <ul class="list-disc list-inside mt-2 space-y-1">
                                    <li>Las credenciales sean correctas</li>
                                    <li>Tu aplicación PayPal esté aprobada para producción</li>
                                    <li>Los webhooks estén configurados correctamente</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="live_client_id" class="block text-sm font-medium text-gray-700">Client ID</label>
                        <input type="text" name="live_client_id" id="live_client_id" 
                               value="{{ $config['live']['client_id'] }}"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="AYxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                        @error('live_client_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="live_client_secret" class="block text-sm font-medium text-gray-700">Client Secret</label>
                        <input type="password" name="live_client_secret" id="live_client_secret" 
                               value="{{ $config['live']['client_secret'] }}"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="EMxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                        @error('live_client_secret')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="live_webhook_id" class="block text-sm font-medium text-gray-700">Webhook ID</label>
                        <input type="text" name="live_webhook_id" id="live_webhook_id" 
                               value="{{ $config['live']['webhook_id'] }}"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="WH-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                        @error('live_webhook_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">URL del webhook: {{ url('/paypal/webhook') }}</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Guardar Configuración</h3>
                        <p class="text-sm text-gray-500">Los cambios se aplicarán inmediatamente al sistema</p>
                    </div>
                    <div class="flex space-x-3">
                        <button type="button" onclick="testCurrentConfig()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fas fa-vial mr-2"></i>
                            Probar Configuración
                        </button>
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fas fa-save mr-2"></i>
                            Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function testCurrentConfig() {
    const formData = new FormData(document.querySelector('form'));
    
    // Show loading
    DevAlert.loading('Probando configuración...', 'Verificando credenciales PayPal');
    
    fetch('{{ route('developer.paypal.test-connection') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            environment: formData.get('environment'),
            sandbox_client_id: formData.get('sandbox_client_id'),
            sandbox_client_secret: formData.get('sandbox_client_secret'),
            live_client_id: formData.get('live_client_id'),
            live_client_secret: formData.get('live_client_secret'),
        })
    })
    .then(response => response.json())
    .then(data => {
        DevAlert.close();
        if (data.success) {
            DevAlert.success('¡Conexión exitosa!', 'Las credenciales PayPal son válidas');
        } else {
            DevAlert.error('Error de conexión', data.message || 'No se pudo conectar con PayPal');
        }
    })
    .catch(error => {
        DevAlert.close();
        DevAlert.error('Error', 'No se pudo verificar la conexión');
        console.error('Error:', error);
    });
}

// Auto-save on environment change
document.querySelectorAll('input[name="environment"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Visual feedback for environment change
        if (this.value === 'live') {
            DevAlert.warning('Modo Producción', 'Has seleccionado el entorno de producción. Las transacciones serán reales.');
        } else {
            DevAlert.info('Modo Sandbox', 'Has seleccionado el entorno de pruebas. Las transacciones son simuladas.');
        }
    });
});

// Toggle password visibility
document.querySelectorAll('input[type="password"]').forEach(input => {
    const container = input.parentElement;
    const toggleBtn = document.createElement('button');
    toggleBtn.type = 'button';
    toggleBtn.className = 'absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5';
    toggleBtn.innerHTML = '<i class="fas fa-eye text-gray-400"></i>';
    
    input.style.paddingRight = '40px';
    container.style.position = 'relative';
    container.appendChild(toggleBtn);
    
    toggleBtn.addEventListener('click', function() {
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        this.innerHTML = type === 'password' ? '<i class="fas fa-eye text-gray-400"></i>' : '<i class="fas fa-eye-slash text-gray-400"></i>';
    });
});
</script>
@endpush