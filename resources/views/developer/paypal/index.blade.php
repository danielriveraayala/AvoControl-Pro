@extends('layouts.developer')

@section('title', 'Configuración PayPal')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:py-12 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                    <div class="mb-4 sm:mb-0">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1">
                            <i class="fab fa-paypal text-blue-600 mr-2"></i>
                            Configuración PayPal
                        </h1>
                        <p class="text-xs sm:text-sm text-gray-600">Gestiona la integración de PayPal para suscripciones</p>
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                        <a href="{{ route('developer.index') }}" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            ← <span class="ml-1">Dashboard</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Connection Status & Environment Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 mb-6">
            <!-- Connection Status -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-2 sm:mb-0">Estado de Conexión</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $connectionStatus['connected'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $connectionStatus['connected'] ? 'Conectado' : 'Desconectado' }}
                        </span>
                    </div>
                </div>
                <div class="px-4 sm:px-6 py-4">
                    <div class="flex items-center mb-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-{{ $connectionStatus['connected'] ? 'check-circle text-green-500' : 'times-circle text-red-500' }} text-2xl"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-gray-900">{{ $connectionStatus['message'] }}</p>
                        </div>
                    </div>
                    <button onclick="testConnection()" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-3 rounded-md text-xs sm:text-sm font-medium">
                        <i class="fas fa-sync-alt mr-2"></i>Probar Conexión
                    </button>
                </div>
            </div>

            <!-- Environment -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-2 sm:mb-0">Entorno Activo</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $config['environment'] === 'sandbox' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                            {{ ucfirst($config['environment']) }}
                        </span>
                    </div>
                </div>
                <div class="px-4 sm:px-6 py-4">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Modo:</span>
                            <span class="text-sm text-gray-900">{{ $config['environment'] === 'sandbox' ? 'Pruebas' : 'Producción' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">API URL:</span>
                            <span class="text-sm text-gray-900">{{ $config['environment'] === 'sandbox' ? 'sandbox.paypal.com' : 'paypal.com' }}</span>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('developer.paypal.config') }}" class="w-full bg-purple-600 hover:bg-purple-700 text-white text-center py-2 px-3 rounded-md text-xs sm:text-sm font-medium block">
                            <i class="fas fa-cog mr-2"></i>Configurar Entorno
                        </a>
                    </div>
                </div>
            </div>

            <!-- API Keys -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-2 sm:mb-0">API Keys</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $config['api_keys_configured'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $config['api_keys_configured'] ? 'Configuradas' : 'Sin Configurar' }}
                        </span>
                    </div>
                </div>
                <div class="px-4 sm:px-6 py-4">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Client ID:</span>
                            <span class="text-sm text-gray-900">{{ $config['client_id'] !== 'Not configured' ? $config['client_id'] : 'No configurado' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Client Secret:</span>
                            <span class="text-sm text-gray-900">{{ $config['client_secret'] !== 'Not configured' ? '••••••••••••••••' : 'No configurado' }}</span>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('developer.paypal.config') }}" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-center py-2 px-3 rounded-md text-xs sm:text-sm font-medium block">
                            <i class="fas fa-key mr-2"></i>Configurar Keys
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuration Details -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6">
            <!-- Sandbox Configuration -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-2 sm:mb-0">
                            <i class="fas fa-flask text-yellow-500 mr-2"></i>Sandbox (Pruebas)
                        </h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $config['sandbox_configured'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $config['sandbox_configured'] ? 'Configurado' : 'Sin Configurar' }}
                        </span>
                    </div>
                </div>
                <div class="px-4 sm:px-6 py-4">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Estado:</span>
                            <span class="text-sm text-gray-900">{{ $config['sandbox_configured'] ? 'Activo' : 'Inactivo' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Client ID:</span>
                            <span class="text-sm text-gray-900">{{ $config['sandbox_configured'] ? 'Configurado' : 'No configurado' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Client Secret:</span>
                            <span class="text-sm text-gray-900">{{ $config['sandbox_configured'] ? 'Configurado' : 'No configurado' }}</span>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('developer.paypal.config') }}" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white text-center py-2 px-3 rounded-md text-xs sm:text-sm font-medium block">
                            <i class="fas fa-flask mr-2"></i>Configurar Sandbox
                        </a>
                    </div>
                </div>
            </div>

            <!-- Live Configuration -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-2 sm:mb-0">
                            <i class="fas fa-globe text-green-500 mr-2"></i>Producción (Live)
                        </h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $config['live_configured'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $config['live_configured'] ? 'Configurado' : 'Sin Configurar' }}
                        </span>
                    </div>
                </div>
                <div class="px-4 sm:px-6 py-4">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Estado:</span>
                            <span class="text-sm text-gray-900">{{ $config['live_configured'] ? 'Activo' : 'Inactivo' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Client ID:</span>
                            <span class="text-sm text-gray-900">{{ $config['live_configured'] ? 'Configurado' : 'No configurado' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Client Secret:</span>
                            <span class="text-sm text-gray-900">{{ $config['live_configured'] ? 'Configurado' : 'No configurado' }}</span>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('developer.paypal.config') }}" class="w-full bg-green-600 hover:bg-green-700 text-white text-center py-2 px-3 rounded-md text-xs sm:text-sm font-medium block">
                            <i class="fas fa-globe mr-2"></i>Configurar Producción
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Plans Management -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between">
                    <div class="mb-4 sm:mb-0">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-1">
                            <i class="fas fa-list-ul text-gray-500 mr-2"></i>Gestión de Planes
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-600">Administra los planes de suscripción PayPal</p>
                    </div>
                    <button onclick="syncPlans()" class="inline-flex items-center px-3 sm:px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                        <i class="fas fa-sync mr-1 sm:mr-2"></i>
                        Sincronizar Planes
                    </button>
                </div>
            </div>
            <div class="px-4 sm:px-6 py-4">
                @if(count($plansStatus) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PayPal ID</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($plansStatus as $key => $plan)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-medium text-gray-900">{{ $plan['name'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-500">${{ $plan['price'] }}/mes</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($plan['synced'])
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Sincronizado
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                No sincronizado
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $plan['local_id'] ?: 'No configurado' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-list-ul text-gray-400 text-4xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No hay planes configurados</h3>
                        <p class="text-sm text-gray-500 mb-4">Los planes de suscripción no están definidos en la configuración.</p>
                        <button onclick="syncPlans()" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-700">
                            <i class="fas fa-sync mr-2"></i>Sincronizar Planes
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Webhooks Management -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between">
                    <div class="mb-4 sm:mb-0">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-1">
                            <i class="fas fa-webhook text-gray-500 mr-2"></i>Gestión de Webhooks
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-600">Administra los eventos de webhook de PayPal</p>
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                        <a href="{{ route('developer.paypal.webhooks') }}" class="inline-flex items-center px-3 sm:px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700">
                            <i class="fas fa-list mr-1 sm:mr-2"></i>
                            <span class="hidden sm:inline">Ver</span> Webhooks
                        </a>
                        <button onclick="openTestWebhook()" class="inline-flex items-center px-3 sm:px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700">
                            <i class="fas fa-vial mr-1 sm:mr-2"></i>
                            <span class="hidden sm:inline">Test</span> Webhook
                        </button>
                    </div>
                </div>
            </div>
            <div class="px-4 sm:px-6 py-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">--</div>
                        <div class="text-sm text-gray-600">Total Webhooks</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">--</div>
                        <div class="text-sm text-gray-600">Procesados</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-600">--</div>
                        <div class="text-sm text-gray-600">Pendientes</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-red-600">--</div>
                        <div class="text-sm text-gray-600">Fallidos</div>
                    </div>
                </div>
                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-500">
                        URL del webhook: <code class="bg-gray-100 px-2 py-1 rounded text-xs">{{ url('/paypal/webhook') }}</code>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test Webhook Modal -->
<div id="testWebhookModal" class="hidden fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Test Webhook</h3>
                <div class="mt-2">
                    <div class="mb-4">
                        <label for="event_type" class="block text-sm font-medium text-gray-700">Tipo de Evento</label>
                        <select id="event_type" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="BILLING.SUBSCRIPTION.ACTIVATED">BILLING.SUBSCRIPTION.ACTIVATED</option>
                            <option value="BILLING.SUBSCRIPTION.CANCELLED">BILLING.SUBSCRIPTION.CANCELLED</option>
                            <option value="BILLING.SUBSCRIPTION.SUSPENDED">BILLING.SUBSCRIPTION.SUSPENDED</option>
                            <option value="PAYMENT.SALE.COMPLETED">PAYMENT.SALE.COMPLETED</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="subscription_id" class="block text-sm font-medium text-gray-700">ID de Suscripción (opcional)</label>
                        <input type="text" id="subscription_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="I-XXXXXXXXXXXX">
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                <button type="button" onclick="sendTestWebhook()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm">
                    Enviar Test
                </button>
                <button type="button" onclick="closeTestWebhook()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function testConnection() {
    DevAlert.loading('Probando conexión...', 'Verificando credenciales PayPal');

    fetch('{{ route('developer.paypal.test-connection') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        DevAlert.close();
        if (data.success) {
            DevAlert.success('¡Conexión exitosa!', data.message).then(() => {
                location.reload();
            });
        } else {
            DevAlert.error('Error de conexión', data.message);
        }
    })
    .catch(error => {
        DevAlert.close();
        DevAlert.error('Error', 'No se pudo conectar con el servidor');
    });
}

function syncPlans() {
    DevAlert.confirm(
        '¿Sincronizar planes?',
        '¿Estás seguro de que quieres sincronizar los planes con PayPal?',
        'Sí, sincronizar',
        'Cancelar'
    ).then((result) => {
        if (result.isConfirmed) {
            DevAlert.loading('Sincronizando...', 'Conectando con PayPal y sincronizando planes');

            fetch('{{ route('developer.paypal.sync-plans') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                DevAlert.close();
                if (data.success) {
                    DevAlert.success('¡Éxito!', 'Planes sincronizados exitosamente').then(() => {
                        location.reload();
                    });
                } else {
                    DevAlert.error('Error', data.message || 'Error al sincronizar planes');
                }
            })
            .catch(error => {
                DevAlert.close();
                DevAlert.error('Error', 'Error al sincronizar planes');
            });
        }
    });
}

function openTestWebhook() {
    document.getElementById('testWebhookModal').classList.remove('hidden');
}

function closeTestWebhook() {
    document.getElementById('testWebhookModal').classList.add('hidden');
}

function sendTestWebhook() {
    const eventType = document.getElementById('event_type').value;
    const subscriptionId = document.getElementById('subscription_id').value;

    DevAlert.loading('Enviando webhook...', 'Procesando webhook de prueba');

    fetch('{{ route('developer.paypal.test-webhook') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            event_type: eventType,
            subscription_id: subscriptionId
        })
    })
    .then(response => response.json())
    .then(data => {
        DevAlert.close();
        closeTestWebhook();
        if (data.success) {
            DevAlert.success('¡Éxito!', data.message);
        } else {
            DevAlert.error('Error', data.message);
        }
    })
    .catch(error => {
        DevAlert.close();
        closeTestWebhook();
        DevAlert.error('Error', 'Error al enviar webhook de prueba');
    });
}
</script>
@endpush
