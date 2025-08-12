@extends('layouts.developer')

@section('title', 'Configuración de Notificaciones Push')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Configuración de Notificaciones Push</h1>
                        <p class="text-sm text-gray-600">Configura las notificaciones push del navegador</p>
                    </div>
                    <a href="{{ route('developer.config.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        ← Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        <!-- Push Notifications Status & Control -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Estado de Notificaciones Push</h3>
                <p class="text-sm text-gray-600 mt-1">Controla las notificaciones push en tu navegador</p>
            </div>
            
            <div class="px-6 py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                🔔
                            </div>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Notificaciones del Navegador</h4>
                            <p class="text-sm text-gray-500">Estado: <span id="push-notification-status">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">⏳ Verificando...</span>
                            </span></p>
                        </div>
                    </div>
                    
                    <div class="flex space-x-2">
                        <button id="toggle-push-notifications" onclick="togglePushNotifications()" 
                                class="bg-purple-600 hover:bg-purple-700 text-white py-2 px-4 rounded-md text-sm font-medium">
                            Configurando...
                        </button>
                        <button onclick="sendTestPushNotification()" 
                                class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md text-sm font-medium">
                            📤 Enviar Prueba
                        </button>
                    </div>
                </div>
                
                <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h5 class="text-sm font-medium text-blue-800">Información Importante</h5>
                            <p class="mt-1 text-sm text-blue-700">
                                • Las notificaciones push requieren HTTPS para funcionar<br>
                                • Debes permitir notificaciones en tu navegador cuando se solicite<br>
                                • Las notificaciones funcionan incluso cuando el sitio está cerrado
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Push Notifications Configuration Form -->
        <div class="bg-white shadow rounded-lg">
            <form action="{{ route('developer.config.notifications.update') }}" method="POST">
                @csrf
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Configuración VAPID</h3>
                    <p class="text-sm text-gray-600 mt-1">Las llaves VAPID son necesarias para enviar notificaciones push</p>
                </div>

                <div class="px-6 py-6">
                    <div class="space-y-6">
                        <!-- Enable/Disable Push Notifications -->
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">Estado de las Notificaciones Push</h4>
                                <p class="text-sm text-gray-600">Activar o desactivar las notificaciones push en todo el sistema</p>
                            </div>
                            <div class="flex items-center">
                                <input type="hidden" name="push_enabled" value="0">
                                <input type="checkbox" name="push_enabled" id="push_enabled" value="1"
                                       {{ old('push_enabled', $pushConfig['enabled']) ? 'checked' : '' }}
                                       class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                                <label for="push_enabled" class="ml-2 text-sm text-gray-900">Habilitado</label>
                            </div>
                        </div>

                        <!-- VAPID Public Key -->
                        <div>
                            <label for="vapid_public_key" class="block text-sm font-medium text-gray-700 mb-2">
                                Clave Pública VAPID <span class="text-red-500">*</span>
                            </label>
                            <textarea name="vapid_public_key" id="vapid_public_key" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 @error('vapid_public_key') border-red-500 @enderror"
                                      placeholder="Clave pública VAPID (formato Base64)">{{ old('vapid_public_key', $pushConfig['public_key']) }}</textarea>
                            @error('vapid_public_key')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- VAPID Private Key -->
                        <div>
                            <label for="vapid_private_key" class="block text-sm font-medium text-gray-700 mb-2">
                                Clave Privada VAPID <span class="text-red-500">*</span>
                            </label>
                            <textarea name="vapid_private_key" id="vapid_private_key" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 @error('vapid_private_key') border-red-500 @enderror"
                                      placeholder="Clave privada VAPID (formato Base64)">{{ old('vapid_private_key') ? old('vapid_private_key') : ($pushConfig['private_key'] ? $pushConfig['private_key'] : '') }}</textarea>
                            @error('vapid_private_key')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- VAPID Subject -->
                        <div>
                            <label for="vapid_subject" class="block text-sm font-medium text-gray-700 mb-2">
                                Subject VAPID <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="vapid_subject" id="vapid_subject"
                                   value="{{ old('vapid_subject', $pushConfig['subject']) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 @error('vapid_subject') border-red-500 @enderror"
                                   placeholder="mailto:admin@avocontrol.com">
                            @error('vapid_subject')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Debe ser una URL válida (https://) o email (mailto:)</p>
                        </div>

                        <!-- Generate VAPID Keys Section -->
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-blue-900 mb-2">¿No tienes llaves VAPID?</h4>
                            <p class="text-sm text-blue-700 mb-3">Puedes generar un nuevo par de llaves VAPID automáticamente.</p>
                            <button type="button" onclick="generateVapidKeys()" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md text-sm font-medium">
                                Generar Nuevas Llaves VAPID
                            </button>
                        </div>

                        <!-- Current Configuration Status -->
                        @if($pushConfig['configured'])
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <h4 class="text-sm font-medium text-green-900">Configuración Válida</h4>
                                </div>
                                <p class="text-sm text-green-700 mt-1">Las notificaciones push están configuradas correctamente.</p>
                                <button type="button" onclick="testNotifications()" class="mt-2 bg-green-600 hover:bg-green-700 text-white py-1 px-3 rounded text-sm">
                                    Probar Notificaciones
                                </button>
                            </div>
                        @else
                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L4.316 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    <h4 class="text-sm font-medium text-yellow-900">Configuración Incompleta</h4>
                                </div>
                                <p class="text-sm text-yellow-700 mt-1">Se requieren las llaves VAPID para activar las notificaciones push.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between">
                    <a href="{{ route('developer.config.vapid') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md text-sm font-medium">
                        Gestionar Llaves VAPID
                    </a>
                    <div class="space-x-3">
                        <a href="{{ route('developer.config.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-4 rounded-md text-sm font-medium">
                            Cancelar
                        </a>
                        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white py-2 px-4 rounded-md text-sm font-medium">
                            Guardar Configuración
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
function generateVapidKeys() {
    DevAlert.confirm(
        '¿Generar Nuevas Llaves VAPID?',
        '⚠️ Esto invalidará las llaves actuales y será necesario reconfigurar todas las suscripciones de notificaciones push.',
        'Sí, generar',
        'Cancelar'
    ).then((result) => {
        if (result.isConfirmed) {
            DevAlert.loading('Generando llaves VAPID...', 'Por favor espera');

            fetch('{{ route("developer.config.vapid.generate") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                DevAlert.close();
                if (data.success) {
                    document.getElementById('vapid_public_key').value = data.public_key;
                    document.getElementById('vapid_private_key').value = data.private_key;
                    DevAlert.success('Llaves VAPID Generadas', 'Nuevas llaves VAPID generadas exitosamente');
                } else {
                    DevAlert.error('Error', 'Error al generar llaves VAPID: ' + data.message);
                }
            })
            .catch(error => {
                DevAlert.close();
                handleFetchError(error);
            });
        }
    });
}

function testNotifications() {
    Swal.fire({
        title: 'Enviar Notificación de Prueba',
        html: `
            <div class="text-left">
                <label class="block text-sm font-medium text-gray-700 mb-2">Título:</label>
                <input type="text" id="testTitle" class="w-full px-3 py-2 border border-gray-300 rounded-md mb-4" placeholder="Título de la notificación">
                <label class="block text-sm font-medium text-gray-700 mb-2">Mensaje:</label>
                <textarea id="testMessage" class="w-full px-3 py-2 border border-gray-300 rounded-md" rows="3" placeholder="Mensaje de la notificación"></textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Enviar Prueba',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#8b5cf6',
        focusConfirm: false,
        preConfirm: () => {
            const title = document.getElementById('testTitle').value;
            const message = document.getElementById('testMessage').value;

            if (!title || !message) {
                Swal.showValidationMessage('Por favor completa todos los campos');
                return false;
            }

            return { title, message };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            DevAlert.loading('Enviando notificación...', 'Por favor espera');

            fetch('{{ route("developer.config.notifications.test") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    test_title: result.value.title,
                    test_message: result.value.message
                })
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

// =======================================================================================
// PUSH NOTIFICATIONS MANAGEMENT
// =======================================================================================

let isSubscribed = false;
let swRegistration = null;

// Initialize push notifications on page load
document.addEventListener('DOMContentLoaded', function() {
    initializePushNotifications();
});

function initializePushNotifications() {
    // Check for browser support
    if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
        console.warn('Push notifications not supported in this browser');
        showBrowserUnsupported();
        return;
    }

    // Register service worker
    navigator.serviceWorker.register('/sw.js')
        .then(function(registration) {
            console.log('Service Worker registered successfully:', registration);
            swRegistration = registration;
            
            // Check current subscription status
            return registration.pushManager.getSubscription();
        })
        .then(function(subscription) {
            isSubscribed = !(subscription === null);
            
            if (isSubscribed) {
                console.log('User IS subscribed to push notifications');
                updateSubscriptionOnServer(subscription);
            } else {
                console.log('User is NOT subscribed to push notifications');
            }
            
            updatePushUI();
        })
        .catch(function(error) {
            console.error('Service Worker registration failed:', error);
            showPushError('Error al registrar el Service Worker: ' + error.message);
        });
}

function updatePushUI() {
    const statusElement = document.getElementById('push-notification-status');
    const toggleButton = document.getElementById('toggle-push-notifications');
    
    if (!statusElement || !toggleButton) return;
    
    if (isSubscribed) {
        statusElement.innerHTML = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">✅ Activas</span>';
        toggleButton.textContent = 'Desactivar Notificaciones';
        toggleButton.className = 'bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-md text-sm font-medium';
    } else {
        statusElement.innerHTML = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">❌ Inactivas</span>';
        toggleButton.textContent = 'Activar Notificaciones';
        toggleButton.className = 'bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-md text-sm font-medium';
    }
}

function togglePushNotifications() {
    if (isSubscribed) {
        unsubscribeUser();
    } else {
        subscribeUser();
    }
}

function subscribeUser() {
    DevAlert.loading('Activando notificaciones...', 'Por favor permite las notificaciones en tu navegador');
    
    // Get VAPID public key from server
    fetch('/push/vapid-key')
        .then(response => response.json())
        .then(data => {
            if (!data.public_key) {
                throw new Error('No se pudo obtener la clave VAPID del servidor');
            }
            
            const applicationServerKey = urlBase64ToUint8Array(data.public_key);
            
            return swRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: applicationServerKey
            });
        })
        .then(function(subscription) {
            console.log('User subscribed to push notifications:', subscription);
            
            return updateSubscriptionOnServer(subscription);
        })
        .then(function() {
            DevAlert.close();
            DevAlert.success('¡Notificaciones Activadas!', 'Ahora recibirás notificaciones push del sistema');
            isSubscribed = true;
            updatePushUI();
        })
        .catch(function(error) {
            console.error('Failed to subscribe user:', error);
            DevAlert.close();
            
            let errorMessage = 'Error al activar notificaciones: ' + error.message;
            
            if (error.name === 'NotAllowedError') {
                errorMessage = 'Las notificaciones fueron bloqueadas. Por favor permite las notificaciones en tu navegador y recarga la página.';
            }
            
            DevAlert.error('Error al inicializar notificaciones push', errorMessage);
        });
}

function unsubscribeUser() {
    DevAlert.loading('Desactivando notificaciones...', 'Por favor espera');
    
    swRegistration.pushManager.getSubscription()
        .then(function(subscription) {
            if (subscription) {
                return subscription.unsubscribe();
            }
        })
        .then(function(successful) {
            console.log('User unsubscribed from push notifications');
            
            return removeSubscriptionFromServer();
        })
        .then(function() {
            DevAlert.close();
            DevAlert.success('Notificaciones Desactivadas', 'Ya no recibirás notificaciones push del sistema');
            isSubscribed = false;
            updatePushUI();
        })
        .catch(function(error) {
            console.error('Failed to unsubscribe user:', error);
            DevAlert.close();
            DevAlert.error('Error', 'Error al desactivar notificaciones: ' + error.message);
        });
}

function updateSubscriptionOnServer(subscription) {
    return fetch('/push/subscribe', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            subscription: subscription
        })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || 'Error del servidor');
        }
        return data;
    });
}

function removeSubscriptionFromServer() {
    return fetch('/push/unsubscribe', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || 'Error del servidor');
        }
        return data;
    });
}

function showBrowserUnsupported() {
    const statusElement = document.getElementById('push-notification-status');
    if (statusElement) {
        statusElement.innerHTML = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">❌ No Soportado</span>';
    }
    
    DevAlert.warning(
        'Navegador No Compatible',
        'Tu navegador no soporta notificaciones push. Por favor usa Chrome, Firefox, Safari o Edge más recientes.'
    );
}

function showPushError(message) {
    const statusElement = document.getElementById('push-notification-status');
    if (statusElement) {
        statusElement.innerHTML = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">❌ Error</span>';
    }
    
    DevAlert.error('Error de Notificaciones Push', message);
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

// Test push notification function
function sendTestPushNotification() {
    if (!isSubscribed) {
        DevAlert.warning('Notificaciones No Activas', 'Primero activa las notificaciones push para poder enviar una prueba.');
        return;
    }
    
    DevAlert.loading('Enviando notificación de prueba...', 'Deberías recibir una notificación en unos segundos');
    
    fetch('{{ route("developer.config.notifications.test") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            test_title: 'Prueba de Notificación - AvoControl Pro',
            test_message: 'Esta es una notificación de prueba desde el panel de desarrollador'
        })
    })
    .then(response => response.json())
    .then(data => {
        DevAlert.close();
        if (data.success) {
            DevAlert.success('Notificación Enviada', 'La notificación de prueba ha sido enviada correctamente');
        } else {
            DevAlert.error('Error', data.message);
        }
    })
    .catch(error => {
        DevAlert.close();
        DevAlert.error('Error', 'Error al enviar notificación de prueba: ' + error.message);
    });
}
</script>
@endsection
