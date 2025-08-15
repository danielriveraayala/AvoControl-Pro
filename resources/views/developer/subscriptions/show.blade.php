@extends('layouts.developer')

@section('title', 'Detalles de Suscripción')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="px-6 py-4 flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">Detalles de Suscripción #{{ $subscription->id }}</h1>
                <a href="{{ route('developer.subscriptions.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Column -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Subscription Information Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Información de la Suscripción</h3>
                        @php
                            $statusColors = [
                                'active' => 'bg-green-100 text-green-800',
                                'suspended' => 'bg-yellow-100 text-yellow-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                                'expired' => 'bg-gray-100 text-gray-800',
                                'pending' => 'bg-blue-100 text-blue-800'
                            ];
                            $statusColor = $statusColors[$subscription->status] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                            {{ ucfirst($subscription->status) }}
                        </span>
                    </div>
                    <div class="px-6 py-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dl class="space-y-2">
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">UUID:</dt>
                                        <dd class="text-sm text-gray-900"><code class="text-xs">{{ Str::limit($subscription->uuid, 20) }}</code></dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Plan:</dt>
                                        <dd class="text-sm text-gray-900">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ ucfirst($subscription->plan) }}
                                            </span>
                                            @if($subscription->is_trial)
                                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Trial
                                                </span>
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Monto:</dt>
                                        <dd class="text-sm text-gray-900 font-semibold">
                                            ${{ number_format($subscription->amount, 2) }} {{ $subscription->currency }}
                                            <span class="text-xs text-gray-500">/ {{ $subscription->interval }}</span>
                                        </dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">PayPal ID:</dt>
                                        <dd class="text-sm text-gray-900">
                                            @if($subscription->paypal_subscription_id)
                                                <code class="text-xs">{{ Str::limit($subscription->paypal_subscription_id, 15) }}</code>
                                            @else
                                                <span class="text-gray-400">N/A</span>
                                            @endif
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                            <div>
                                <dl class="space-y-2">
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Creada:</dt>
                                        <dd class="text-sm text-gray-900">
                                            {{ $subscription->created_at ? $subscription->created_at->format('d/m/Y H:i') : 'N/A' }}
                                        </dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Próximo Cobro:</dt>
                                        <dd class="text-sm text-gray-900">
                                            @if($subscription->next_billing_date)
                                                {{ $subscription->next_billing_date->format('d/m/Y') }}
                                            @else
                                                <span class="text-gray-400">N/A</span>
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Ciclos Facturados:</dt>
                                        <dd class="text-sm text-gray-900">{{ $subscription->billing_cycle_count }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Pagos Fallidos:</dt>
                                        <dd class="text-sm">
                                            @if($subscription->failed_payment_count > 0)
                                                <span class="text-red-600 font-semibold">{{ $subscription->failed_payment_count }}</span>
                                            @else
                                                <span class="text-green-600">0</span>
                                            @endif
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        @if($subscription->status === 'suspended')
                        <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">Suscripción Suspendida</h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <p><strong>Fecha:</strong> {{ $subscription->suspended_at ? $subscription->suspended_at->format('d/m/Y H:i') : 'N/A' }}</p>
                                        <p><strong>Razón:</strong> {{ $subscription->suspension_reason ?? 'No especificada' }}</p>
                                        <p><strong>Por:</strong> {{ $subscription->suspended_by ?? 'Sistema' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Tenant Information Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Información del Tenant</h3>
                    </div>
                    <div class="px-6 py-4">
                        @if($subscription->tenant)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <dl class="space-y-2">
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Nombre:</dt>
                                        <dd class="text-sm text-gray-900">{{ $subscription->tenant->name }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Slug:</dt>
                                        <dd class="text-sm text-gray-900"><code class="text-xs">{{ $subscription->tenant->slug }}</code></dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Email:</dt>
                                        <dd class="text-sm text-gray-900">{{ $subscription->tenant->email ?? 'N/A' }}</dd>
                                    </div>
                                </dl>
                                <dl class="space-y-2">
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Estado:</dt>
                                        <dd class="text-sm">
                                            @php
                                                $tenantStatusColor = $subscription->tenant->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $tenantStatusColor }}">
                                                {{ ucfirst($subscription->tenant->status) }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Usuarios:</dt>
                                        <dd class="text-sm text-gray-900">{{ $subscription->tenant->users()->count() }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm font-medium text-gray-500">Creado:</dt>
                                        <dd class="text-sm text-gray-900">
                                            {{ $subscription->tenant->created_at ? $subscription->tenant->created_at->format('d/m/Y') : 'N/A' }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        @else
                            <p class="text-gray-500">No hay información del tenant disponible.</p>
                        @endif
                    </div>
                </div>

                <!-- Payment History Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Historial de Pagos</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $subscription->payments->count() }} pagos
                        </span>
                    </div>
                    <div class="px-6 py-4">
                        @if($subscription->payments->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Detalles</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($subscription->payments as $payment)
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                                {{ $payment->payment_date ? $payment->payment_date->format('d/m/Y H:i') : 'N/A' }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap">
                                                @php
                                                    $typeColors = [
                                                        'initial' => 'bg-blue-100 text-blue-800',
                                                        'recurring' => 'bg-indigo-100 text-indigo-800',
                                                        'refund' => 'bg-yellow-100 text-yellow-800',
                                                        'chargeback' => 'bg-red-100 text-red-800'
                                                    ];
                                                    $typeColor = $typeColors[$payment->type] ?? 'bg-gray-100 text-gray-800';
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $typeColor }}">
                                                    {{ ucfirst($payment->type) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm">
                                                @if($payment->amount < 0)
                                                    <span class="text-red-600 font-semibold">${{ number_format($payment->amount, 2) }}</span>
                                                @else
                                                    <span class="text-green-600 font-semibold">${{ number_format($payment->amount, 2) }}</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap">
                                                @php
                                                    $statusColors = [
                                                        'completed' => 'bg-green-100 text-green-800',
                                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                                        'failed' => 'bg-red-100 text-red-800',
                                                        'refunded' => 'bg-blue-100 text-blue-800',
                                                        'reversed' => 'bg-red-100 text-red-800'
                                                    ];
                                                    $statusColor = $statusColors[$payment->status] ?? 'bg-gray-100 text-gray-800';
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusColor }}">
                                                    {{ ucfirst($payment->status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-500">
                                                {{ $payment->failure_reason ?? $payment->notes ?? '-' }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-gray-500">No hay pagos registrados para esta suscripción.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Actions Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Acciones</h3>
                    </div>
                    <div class="px-6 py-4 space-y-3">
                        @if($subscription->status === 'active')
                            <button onclick="suspendSubscription({{ $subscription->id }})" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                <i class="fas fa-pause mr-2"></i> Suspender
                            </button>
                            <button onclick="cancelSubscription({{ $subscription->id }})" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <i class="fas fa-times mr-2"></i> Cancelar
                            </button>
                        @elseif($subscription->status === 'suspended')
                            <button onclick="reactivateSubscription({{ $subscription->id }})" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <i class="fas fa-play mr-2"></i> Reactivar
                            </button>
                        @endif
                        
                        @if($subscription->paypal_subscription_id && !str_starts_with($subscription->paypal_subscription_id, 'TEST-'))
                            @if(!$paypalError)
                                <button onclick="syncWithPayPal({{ $subscription->id }})" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <i class="fas fa-sync mr-2"></i> Sincronizar con PayPal
                                </button>
                            @else
                                <div class="p-3 bg-red-50 border border-red-200 rounded-md">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-exclamation-circle text-red-400"></i>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-red-800">Suscripción no encontrada en PayPal</h3>
                                            <p class="text-sm text-red-700 mt-1">
                                                Esta suscripción fue eliminada de PayPal (posiblemente por reembolso o cancelación).
                                            </p>
                                            <div class="mt-2">
                                                <button onclick="markAsOrphaned({{ $subscription->id }})" class="text-xs bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700">
                                                    Marcar como Huérfana
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Payment Statistics Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Estadísticas de Pagos</h3>
                    </div>
                    <div class="px-6 py-4">
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Total Pagado:</dt>
                                <dd class="text-sm font-bold text-green-600">${{ number_format($paymentStats['total_paid'], 2) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Pagos Fallidos:</dt>
                                <dd class="text-sm font-semibold">
                                    @if($paymentStats['failed_payments'] > 0)
                                        <span class="text-red-600">{{ $paymentStats['failed_payments'] }}</span>
                                    @else
                                        <span class="text-green-600">0</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Último Pago:</dt>
                                <dd class="text-sm text-gray-900">
                                    @if($paymentStats['last_payment'] && $paymentStats['last_payment']->payment_date)
                                        {{ $paymentStats['last_payment']->payment_date->format('d/m/Y') }}
                                    @else
                                        N/A
                                    @endif
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Próximo Pago:</dt>
                                <dd class="text-sm text-gray-900">
                                    @if($paymentStats['next_payment'])
                                        {{ $paymentStats['next_payment']->format('d/m/Y') }}
                                    @else
                                        N/A
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function suspendSubscription(id) {
    Swal.fire({
        title: '¿Suspender Suscripción?',
        input: 'textarea',
        inputLabel: 'Motivo de suspensión',
        inputPlaceholder: 'Ingrese el motivo...',
        showCancelButton: true,
        confirmButtonText: 'Suspender',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        inputValidator: (value) => {
            if (!value) {
                return 'Debe ingresar un motivo'
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`{{ route('developer.subscriptions.index') }}/${id}/suspend`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ reason: result.value })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Suspendida', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        }
    });
}

function reactivateSubscription(id) {
    Swal.fire({
        title: '¿Reactivar Suscripción?',
        input: 'textarea',
        inputLabel: 'Motivo de reactivación',
        inputPlaceholder: 'Ingrese el motivo...',
        showCancelButton: true,
        confirmButtonText: 'Reactivar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#28a745',
        inputValidator: (value) => {
            if (!value) {
                return 'Debe ingresar un motivo'
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`{{ route('developer.subscriptions.index') }}/${id}/reactivate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ reason: result.value })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Reactivada', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        }
    });
}

function cancelSubscription(id) {
    Swal.fire({
        title: '¿Cancelar Suscripción?',
        text: 'Esta acción no se puede deshacer',
        input: 'textarea',
        inputLabel: 'Motivo de cancelación',
        inputPlaceholder: 'Ingrese el motivo...',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, Cancelar',
        cancelButtonText: 'No',
        confirmButtonColor: '#dc3545',
        inputValidator: (value) => {
            if (!value) {
                return 'Debe ingresar un motivo'
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`{{ route('developer.subscriptions.index') }}/${id}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ reason: result.value })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Cancelada', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        }
    });
}

function syncWithPayPal(id) {
    Swal.fire({
        title: 'Sincronizando...',
        text: 'Sincronizando con PayPal',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch(`{{ route('developer.subscriptions.index') }}/${id}/sync-paypal`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Sincronizado', data.message, 'success').then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    });
}

function markAsOrphaned(id) {
    Swal.fire({
        title: '¿Marcar como Suscripción Huérfana?',
        html: `
            <div class="text-left">
                <p class="mb-3">Esta acción marcará la suscripción como "huérfana" (sin conexión con PayPal).</p>
                <div class="form-group">
                    <label for="orphanReason">Motivo:</label>
                    <textarea id="orphanReason" class="form-control" rows="3" placeholder="Ej: Suscripción eliminada por reembolso en PayPal"></textarea>
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, Marcar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        preConfirm: () => {
            const reason = document.getElementById('orphanReason').value;
            if (!reason.trim()) {
                Swal.showValidationMessage('El motivo es requerido');
                return false;
            }
            return reason;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`{{ route('developer.subscriptions.index') }}/${id}/mark-orphaned`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ reason: result.value })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Marcada', 'Suscripción marcada como huérfana', 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message || 'Error al marcar la suscripción', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Error de conexión', 'error');
            });
        }
    });
}
</script>
@endpush