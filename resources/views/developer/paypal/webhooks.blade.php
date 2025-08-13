@extends('layouts.developer')

@section('title', 'Gestión de Webhooks PayPal')

@section('content')
<div class="bg-white shadow-sm">
    <div class="px-4 sm:px-6 lg:mx-auto lg:max-w-7xl lg:px-8">
        <div class="py-6">
            <h1 class="text-2xl font-semibold text-gray-900">Gestión de Webhooks PayPal</h1>
        </div>
    </div>
</div>

<div class="mt-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        
        <!-- Webhook Configuration -->
        <div class="bg-white shadow-lg rounded-lg mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-cog mr-2 text-gray-500"></i>
                    Configuración de Webhooks
                </h3>
            </div>
            <div class="p-6">
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
                        <h4 class="text-md font-semibold text-gray-900 mb-3">Eventos Configurados</h4>
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

                    <!-- Acciones Rápidas -->
                    <div>
                        <h4 class="text-md font-semibold text-gray-900 mb-3">Acciones</h4>
                        <div class="space-y-3">
                            <button onclick="testWebhook()" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-vial mr-2"></i>
                                Probar Webhook
                            </button>
                            
                            <button onclick="refreshWebhooks()" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-sync-alt mr-2"></i>
                                Actualizar Logs
                            </button>
                            
                            <a href="{{ route('developer.paypal.index') }}" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Volver a PayPal Config
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Webhook Statistics -->
        @php
            $stats = \App\Models\PayPalWebhookLog::getStats();
        @endphp
        
        <div class="grid grid-cols-2 gap-5 sm:grid-cols-3 lg:grid-cols-6 mb-8">
            <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-blue-500 rounded-md p-3">
                                <i class="fas fa-webhook text-white text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total</dt>
                                <dd class="text-lg font-semibold text-gray-900">{{ number_format($stats['total']) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-green-500 rounded-md p-3">
                                <i class="fas fa-check-circle text-white text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Procesados</dt>
                                <dd class="text-lg font-semibold text-gray-900">{{ number_format($stats['processed']) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-red-500 rounded-md p-3">
                                <i class="fas fa-times-circle text-white text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Fallidos</dt>
                                <dd class="text-lg font-semibold text-gray-900">{{ number_format($stats['failed']) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-yellow-500 rounded-md p-3">
                                <i class="fas fa-clock text-white text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Pendientes</dt>
                                <dd class="text-lg font-semibold text-gray-900">{{ number_format($stats['pending']) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-indigo-500 rounded-md p-3">
                                <i class="fas fa-calendar-day text-white text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Hoy</dt>
                                <dd class="text-lg font-semibold text-gray-900">{{ number_format($stats['today']) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-purple-500 rounded-md p-3">
                                <i class="fas fa-calendar-week text-white text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Esta Semana</dt>
                                <dd class="text-lg font-semibold text-gray-900">{{ number_format($stats['this_week']) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Webhook Logs -->
        <div class="bg-white shadow-lg rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-history mr-2 text-gray-500"></i>
                        Logs de Webhooks Recientes
                    </h3>
                    <div class="flex space-x-2">
                        <div class="relative inline-block text-left">
                            <button type="button" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" id="filter-menu">
                                <i class="fas fa-filter mr-2"></i>
                                Filtros
                                <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <button onclick="exportLogs()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fas fa-download mr-2"></i>
                            Exportar
                        </button>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table id="webhooks-table" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Suscripción</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recibido</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Procesado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            $recentWebhooks = \App\Models\PayPalWebhookLog::getRecent(20);
                        @endphp
                        @forelse($recentWebhooks as $webhook)
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($webhook->tenant)
                                    {{ $webhook->tenant->name }}
                                @elseif($webhook->subscription && $webhook->subscription->tenant)
                                    {{ $webhook->subscription->tenant->name }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $webhook->received_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($webhook->processed_at)
                                    {{ $webhook->processed_at->format('d/m/Y H:i:s') }}
                                    @if($webhook->processing_duration)
                                        <div class="text-xs text-gray-400">{{ $webhook->processing_duration }}s</div>
                                    @endif
                                @else
                                    <span class="text-gray-400">Pendiente</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button onclick="viewWebhookDetails({{ $webhook->id }})" class="text-indigo-600 hover:text-indigo-900">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if($webhook->status === 'failed')
                                        <button onclick="retryWebhook({{ $webhook->id }})" class="text-green-600 hover:text-green-900">
                                            <i class="fas fa-redo"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-webhook fa-3x mb-3 text-gray-300"></i>
                                <p>No hay webhooks registrados aún</p>
                                <p class="text-sm">Los webhooks aparecerán aquí cuando PayPal envíe eventos</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Webhook Details Modal -->
<div id="webhookDetailsModal" class="hidden fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full sm:p-6">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Detalles del Webhook</h3>
                <div id="webhookDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
            <div class="mt-5 sm:mt-6">
                <button type="button" onclick="closeWebhookDetails()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function testWebhook() {
    // Use SweetAlert for input
    Swal.fire({
        title: 'Test Webhook',
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
        showLoaderOnConfirm: true,
        preConfirm: (eventType) => {
            return fetch('{{ route('developer.paypal.test-webhook') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    event_type: eventType,
                    subscription_id: 'I-TEST-' + Date.now()
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Error al procesar webhook');
                }
                return data;
            })
            .catch(error => {
                Swal.showValidationMessage(`Error: ${error.message}`);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: '¡Éxito!',
                text: 'Webhook de prueba enviado exitosamente',
                icon: 'success'
            }).then(() => {
                location.reload();
            });
        }
    });
}

function refreshWebhooks() {
    location.reload();
}

function viewWebhookDetails(webhookId) {
    // Show loading with SweetAlert
    Swal.fire({
        title: 'Cargando detalles...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch(`{{ url('/developer/paypal/webhooks') }}/${webhookId}/details`)
        .then(response => response.json())
        .then(response => {
            if (response.webhook) {
                const webhook = response.webhook;
                let content = `
                    <div class="space-y-4">
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

                Swal.fire({
                    title: 'Detalles del Webhook',
                    html: content,
                    width: '90%',
                    showCloseButton: true,
                    showConfirmButton: false,
                    customClass: {
                        popup: 'text-left'
                    }
                });
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Error',
                text: 'No se pudieron cargar los detalles del webhook',
                icon: 'error'
            });
        });
}

function closeWebhookDetails() {
    Swal.close();
}

function retryWebhook(webhookId) {
    Swal.fire({
        title: '¿Reintentar webhook?',
        text: '¿Estás seguro de que quieres reintentar el procesamiento de este webhook?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, reintentar',
        cancelButtonText: 'Cancelar',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return fetch(`{{ url('/developer/paypal/webhooks') }}/${webhookId}/retry`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Error al reintentar webhook');
                }
                return data;
            })
            .catch(error => {
                Swal.showValidationMessage(`Error: ${error.message}`);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: '¡Éxito!',
                text: 'Webhook reintentado exitosamente',
                icon: 'success'
            }).then(() => {
                location.reload();
            });
        }
    });
}

function exportLogs() {
    Swal.fire({
        title: 'Exportando logs...',
        text: 'Generando archivo CSV con los logs de webhooks',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Create form for export with filters
    const form = document.createElement('form');
    form.method = 'GET';
    form.action = '{{ route('developer.paypal.webhooks.export') }}';
    form.style.display = 'none';
    
    // Add current filters if any
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.forEach((value, key) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
    
    // Close loading and show success
    setTimeout(() => {
        Swal.fire({
            title: '¡Descarga iniciada!',
            text: 'El archivo CSV se está descargando',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    }, 1000);
}
</script>
@endpush