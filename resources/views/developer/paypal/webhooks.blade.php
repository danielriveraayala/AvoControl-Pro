@extends('layouts.developer')

@section('title', 'Gestión de Webhooks PayPal')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:py-12 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                    <div class="mb-4 sm:mb-0">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1">
                            <i class="fas fa-webhook text-purple-600 mr-2"></i>
                            Gestión de Webhooks PayPal
                        </h1>
                        <p class="text-xs sm:text-sm text-gray-600">Administra los eventos de webhook de PayPal</p>
                    </div>
                    <a href="{{ route('developer.paypal.index') }}" class="inline-flex items-center px-3 sm:px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        ← <span class="ml-1">PayPal Config</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Webhook Configuration -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-2 sm:mb-0">
                        <i class="fas fa-cog text-gray-500 mr-2"></i>Configuración de Webhooks
                    </h3>
                    <button onclick="testWebhook()" class="inline-flex items-center px-3 sm:px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                        <i class="fas fa-vial mr-1 sm:mr-2"></i>
                        <span class="hidden sm:inline">Probar</span> Webhook
                    </button>
                </div>
            </div>
            <div class="px-4 sm:px-6 py-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-500"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">URL del Webhook</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>Configura esta URL en tu panel de PayPal:</p>
                                <code class="bg-blue-100 px-2 py-1 rounded">{{ $webhookConfig['url'] }}</code>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Eventos Configurados -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-2">Eventos Configurados</h4>
                        <div class="space-y-2">
                            @foreach($webhookConfig['events'] as $event)
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                <span class="text-sm text-gray-700">{{ $event }}</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Activo
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Estadísticas Rápidas -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-2">Estadísticas</h4>
                        @php
                            try {
                                $stats = \App\Models\PayPalWebhookLog::getStats();
                            } catch (\Exception $e) {
                                // Fallback if table doesn't exist
                                $stats = [
                                    'total' => 0,
                                    'processed' => 0,
                                    'failed' => 0,
                                    'today' => 0
                                ];
                            }
                        @endphp
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-gray-50 p-3 rounded-lg text-center">
                                <div class="text-lg font-bold text-blue-600">{{ number_format($stats['total'] ?? 0) }}</div>
                                <div class="text-xs text-gray-600">Total</div>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg text-center">
                                <div class="text-lg font-bold text-green-600">{{ number_format($stats['processed'] ?? 0) }}</div>
                                <div class="text-xs text-gray-600">Procesados</div>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg text-center">
                                <div class="text-lg font-bold text-red-600">{{ number_format($stats['failed'] ?? 0) }}</div>
                                <div class="text-xs text-gray-600">Fallidos</div>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg text-center">
                                <div class="text-lg font-bold text-yellow-600">{{ number_format($stats['today'] ?? 0) }}</div>
                                <div class="text-xs text-gray-600">Hoy</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Webhook Logs -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between">
                    <div class="mb-4 sm:mb-0">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-1">
                            <i class="fas fa-history text-gray-500 mr-2"></i>Logs de Webhooks Recientes
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-600">Historial de eventos recibidos de PayPal</p>
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                        <button onclick="refreshWebhooks()" class="inline-flex items-center px-3 sm:px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                            <i class="fas fa-sync-alt mr-1 sm:mr-2"></i>
                            <span class="hidden sm:inline">Actualizar</span> Logs
                        </button>
                        <button onclick="exportLogs()" class="inline-flex items-center px-3 sm:px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            <i class="fas fa-download mr-1 sm:mr-2"></i>
                            <span class="hidden sm:inline">Exportar</span> CSV
                        </button>
                    </div>
                </div>
            </div>
            <div class="px-4 sm:px-6 py-4">
                @php
                    try {
                        $recentWebhooks = \App\Models\PayPalWebhookLog::getRecent(20);
                    } catch (\Exception $e) {
                        // Fallback if table doesn't exist
                        $recentWebhooks = collect([]);
                    }
                @endphp
                @if(count($recentWebhooks) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evento</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Suscripción</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recibido</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($recentWebhooks as $webhook)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        #{{ $webhook->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $webhook->formatted_event_type }}</div>
                                        <div class="text-xs text-gray-500">{{ $webhook->event_type }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {!! $webhook->status_badge !!}
                                        @if($webhook->isCritical())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 ml-1">
                                                Crítico
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($webhook->subscription)
                                            <a href="{{ route('developer.subscriptions.show', $webhook->subscription) }}" class="text-indigo-600 hover:text-indigo-900">
                                                #{{ $webhook->subscription->id }}
                                            </a>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $webhook->received_at->format('d/m/Y H:i:s') }}
                                        @if($webhook->processed_at && $webhook->processing_duration)
                                            <div class="text-xs text-gray-400">{{ $webhook->processing_duration }}s</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button onclick="viewWebhookDetails({{ $webhook->id }})" class="text-indigo-600 hover:text-indigo-900" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if($webhook->status === 'failed')
                                                <button onclick="retryWebhook({{ $webhook->id }})" class="text-green-600 hover:text-green-900" title="Reintentar">
                                                    <i class="fas fa-redo"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-webhook text-gray-400 text-4xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No hay webhooks registrados</h3>
                        <p class="text-sm text-gray-500 mb-4">Los webhooks aparecerán aquí cuando PayPal envíe eventos</p>
                        <button onclick="testWebhook()" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-700">
                            <i class="fas fa-vial mr-2"></i>Probar Webhook
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function testWebhook() {
    // Use SweetAlert for select input since DevAlert.input doesn't exist
    Swal.fire({
        title: 'Test Webhook',
        text: 'Selecciona el tipo de evento a simular:',
        input: 'select',
        inputOptions: {
            'BILLING.SUBSCRIPTION.ACTIVATED': 'BILLING.SUBSCRIPTION.ACTIVATED',
            'BILLING.SUBSCRIPTION.CANCELLED': 'BILLING.SUBSCRIPTION.CANCELLED',
            'BILLING.SUBSCRIPTION.SUSPENDED': 'BILLING.SUBSCRIPTION.SUSPENDED',
            'PAYMENT.SALE.COMPLETED': 'PAYMENT.SALE.COMPLETED'
        },
        inputValue: 'BILLING.SUBSCRIPTION.ACTIVATED',
        showCancelButton: true,
        confirmButtonText: 'Enviar Test',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#3b82f6',
    }).then((result) => {
        if (result.isConfirmed) {
            DevAlert.loading('Enviando webhook...', 'Procesando webhook de prueba');
            
            fetch('{{ route('developer.paypal.test-webhook') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    event_type: result.value,
                    subscription_id: 'I-TEST-' + Date.now()
                })
            })
            .then(response => response.json())
            .then(data => {
                DevAlert.close();
                if (data.success) {
                    DevAlert.success('¡Éxito!', 'Webhook de prueba enviado exitosamente').then(() => {
                        location.reload();
                    });
                } else {
                    DevAlert.error('Error', data.message || 'Error al procesar webhook');
                }
            })
            .catch(error => {
                DevAlert.close();
                DevAlert.error('Error', 'Error al enviar webhook de prueba');
            });
        }
    });
}

function refreshWebhooks() {
    DevAlert.loading('Actualizando...', 'Recargando logs de webhooks');
    setTimeout(() => {
        location.reload();
    }, 500);
}

function viewWebhookDetails(webhookId) {
    DevAlert.loading('Cargando detalles...', 'Obteniendo información del webhook');
    
    fetch(`{{ url('/developer/paypal/webhooks') }}/${webhookId}/details`)
        .then(response => response.json())
        .then(response => {
            DevAlert.close();
            if (response.webhook) {
                const webhook = response.webhook;
                let content = `
                    <div class="space-y-4 text-left">
                        <!-- Basic Info -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-3">Información Básica</h4>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div><span class="font-medium">ID:</span> #${webhook.id}</div>
                                <div><span class="font-medium">Webhook ID:</span> ${webhook.webhook_id || 'N/A'}</div>
                                <div><span class="font-medium">Tipo:</span> ${webhook.formatted_event_type}</div>
                                <div><span class="font-medium">Estado:</span> ${webhook.status_badge}</div>
                                <div><span class="font-medium">Recibido:</span> ${webhook.received_at}</div>
                                <div><span class="font-medium">Procesado:</span> ${webhook.processed_at || 'Pendiente'}</div>
                            </div>
                        </div>

                        <!-- Resource Info -->
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-3">Información del Recurso</h4>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div><span class="font-medium">Tipo:</span> ${webhook.resource_type || 'N/A'}</div>
                                <div><span class="font-medium">Resource ID:</span> ${webhook.resource_id || 'N/A'}</div>
                                <div><span class="font-medium">IP:</span> ${webhook.ip_address || 'N/A'}</div>
                                <div><span class="font-medium">Duración:</span> ${webhook.processing_duration ? webhook.processing_duration + 's' : 'N/A'}</div>
                            </div>
                        </div>
                `;

                if (webhook.subscription) {
                    content += `
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-3">Suscripción</h4>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div><span class="font-medium">ID:</span> #${webhook.subscription.id}</div>
                                <div><span class="font-medium">Plan:</span> ${webhook.subscription.plan}</div>
                                <div><span class="font-medium">Estado:</span> ${webhook.subscription.status}</div>
                                <div><span class="font-medium">Monto:</span> $${webhook.subscription.amount}</div>
                            </div>
                        </div>
                    `;
                }

                if (webhook.processing_notes) {
                    content += `
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-3">Notas de Procesamiento</h4>
                            <p class="text-sm text-gray-700">${webhook.processing_notes}</p>
                        </div>
                    `;
                }

                content += `
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-3">Payload Completo</h4>
                            <pre class="text-xs bg-white p-3 rounded border overflow-auto max-h-40">${JSON.stringify(webhook.payload, null, 2)}</pre>
                        </div>
                    </div>
                `;

                DevAlert.html('Detalles del Webhook', content, {
                    width: '90%',
                    showCloseButton: true,
                    showConfirmButton: false
                });
            }
        })
        .catch(error => {
            DevAlert.close();
            DevAlert.error('Error', 'No se pudieron cargar los detalles del webhook');
        });
}

function retryWebhook(webhookId) {
    DevAlert.confirm(
        '¿Reintentar webhook?',
        '¿Estás seguro de que quieres reintentar el procesamiento de este webhook?',
        'Sí, reintentar',
        'Cancelar'
    ).then((result) => {
        if (result.isConfirmed) {
            DevAlert.loading('Reintentando...', 'Procesando webhook nuevamente');
            
            fetch(`{{ url('/developer/paypal/webhooks') }}/${webhookId}/retry`, {
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
                    DevAlert.success('¡Éxito!', 'Webhook reintentado exitosamente').then(() => {
                        location.reload();
                    });
                } else {
                    DevAlert.error('Error', data.message || 'Error al reintentar webhook');
                }
            })
            .catch(error => {
                DevAlert.close();
                DevAlert.error('Error', 'Error al reintentar webhook');
            });
        }
    });
}

function exportLogs() {
    DevAlert.loading('Exportando logs...', 'Generando archivo CSV con los logs de webhooks');

    // Create form for export
    const form = document.createElement('form');
    form.method = 'GET';
    form.action = '{{ route('developer.paypal.webhooks.export') }}';
    form.style.display = 'none';
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
    
    // Show success message
    setTimeout(() => {
        DevAlert.close();
        DevAlert.success('¡Descarga iniciada!', 'El archivo CSV se está descargando');
    }, 1000);
}
</script>
@endpush