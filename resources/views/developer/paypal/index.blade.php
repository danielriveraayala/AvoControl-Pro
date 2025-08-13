@extends('layouts.developer')

@section('title', 'Configuración PayPal')

@section('content')
<div class="bg-white shadow-sm">
    <div class="px-4 sm:px-6 lg:mx-auto lg:max-w-7xl lg:px-8">
        <div class="py-6">
            <h1 class="text-2xl font-semibold text-gray-900">Configuración PayPal</h1>
        </div>
    </div>
</div>

<div class="mt-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        
        <!-- Connection Status -->
        <div class="mb-8">
            <div class="bg-white shadow-lg rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Estado de Conexión</h2>
                    <button onclick="testConnection()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm">
                        <i class="fas fa-sync-alt mr-2"></i>Probar Conexión
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 border rounded-lg {{ $connectionStatus['connected'] ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-{{ $connectionStatus['connected'] ? 'check-circle text-green-500' : 'times-circle text-red-500' }} text-2xl"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Estado</p>
                                <p class="text-sm text-gray-500">{{ $connectionStatus['message'] }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-4 border rounded-lg bg-gray-50">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-server text-gray-500 text-2xl"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Entorno</p>
                                <p class="text-sm text-gray-500">{{ ucfirst($connectionStatus['environment']) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-4 border rounded-lg bg-gray-50">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-key text-gray-500 text-2xl"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">API Keys</p>
                                <p class="text-sm text-gray-500">
                                    {{ $config['client_id'] !== 'Not configured' ? 'Configuradas' : 'No configuradas' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mb-8">
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Acciones Rápidas</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <a href="{{ route('developer.paypal.config') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-3 rounded-md text-center">
                        <i class="fas fa-cog mr-2"></i>Configurar API
                    </a>
                    
                    <button onclick="syncPlans()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-md">
                        <i class="fas fa-sync mr-2"></i>Sincronizar Planes
                    </button>
                    
                    <a href="{{ route('developer.paypal.webhooks') }}" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-3 rounded-md text-center">
                        <i class="fas fa-webhook mr-2"></i>Ver Webhooks
                    </a>
                    
                    <button onclick="openTestWebhook()" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-3 rounded-md">
                        <i class="fas fa-vial mr-2"></i>Test Webhook
                    </button>
                </div>
            </div>
        </div>

        <!-- Plans Status -->
        <div class="mb-8">
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Estado de Planes</h2>
                
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
            </div>
        </div>

        <!-- Configuration Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Sandbox Config -->
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-flask mr-2 text-yellow-500"></i>Sandbox
                </h3>
                <div class="space-y-2">
                    <div class="flex items-center">
                        <span class="text-sm text-gray-500 w-24">Estado:</span>
                        @if($config['sandbox_configured'])
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Configurado
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                No configurado
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center">
                        <span class="text-sm text-gray-500 w-24">Client ID:</span>
                        <span class="text-sm text-gray-900">{{ $config['sandbox_configured'] ? '••••••••••••' : 'No configurado' }}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-sm text-gray-500 w-24">Secret:</span>
                        <span class="text-sm text-gray-900">{{ $config['sandbox_configured'] ? '••••••••••••' : 'No configurado' }}</span>
                    </div>
                </div>
            </div>

            <!-- Live Config -->
            <div class="bg-white shadow-lg rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-globe mr-2 text-green-500"></i>Producción
                </h3>
                <div class="space-y-2">
                    <div class="flex items-center">
                        <span class="text-sm text-gray-500 w-24">Estado:</span>
                        @if($config['live_configured'])
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Configurado
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                No configurado
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center">
                        <span class="text-sm text-gray-500 w-24">Client ID:</span>
                        <span class="text-sm text-gray-900">{{ $config['live_configured'] ? '••••••••••••' : 'No configurado' }}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-sm text-gray-500 w-24">Secret:</span>
                        <span class="text-sm text-gray-900">{{ $config['live_configured'] ? '••••••••••••' : 'No configurado' }}</span>
                    </div>
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
    $.post('{{ route('developer.paypal.test-connection') }}', {
        _token: '{{ csrf_token() }}'
    }).done(function(response) {
        if (response.success) {
            toastr.success(response.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            toastr.error(response.message);
        }
    }).fail(function() {
        toastr.error('Error al probar la conexión');
    });
}

function syncPlans() {
    if (!confirm('¿Sincronizar planes con PayPal?')) return;
    
    $.post('{{ route('developer.paypal.sync-plans') }}', {
        _token: '{{ csrf_token() }}'
    }).done(function(response) {
        if (response.success) {
            toastr.success(response.message);
            if (response.output) {
                console.log(response.output);
            }
            setTimeout(() => location.reload(), 1500);
        } else {
            toastr.error(response.message);
        }
    }).fail(function() {
        toastr.error('Error al sincronizar planes');
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
    
    $.post('{{ route('developer.paypal.test-webhook') }}', {
        _token: '{{ csrf_token() }}',
        event_type: eventType,
        subscription_id: subscriptionId
    }).done(function(response) {
        if (response.success) {
            toastr.success(response.message);
            closeTestWebhook();
        } else {
            toastr.error(response.message);
        }
    }).fail(function() {
        toastr.error('Error al enviar webhook de prueba');
    });
}
</script>
@endpush