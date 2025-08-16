@extends('layouts.developer')

@section('title', 'Gestión de Suscripciones')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:py-12 lg:px-8">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                    <div class="mb-4 sm:mb-0">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1">
                            <i class="fas fa-credit-card text-indigo-600 mr-2"></i>
                            Gestión de Suscripciones
                        </h1>
                        <p class="text-xs sm:text-sm text-gray-600">Monitor and manage all tenant subscriptions and billing</p>
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                        <a href="{{ route('developer.index') }}" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            ← <span class="ml-1">Dashboard</span>
                        </a>
                        <button onclick="refreshData()" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 bg-white uppercase tracking-widest hover:bg-gray-50">
                            <i class="fas fa-refresh mr-1 sm:mr-2"></i>
                            <span class="hidden sm:inline">Refresh</span><span class="sm:hidden">Sync</span>
                        </button>
                        <button onclick="exportData()" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            <i class="fas fa-download mr-1 sm:mr-2"></i>
                            Export
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Critical Alerts -->
        @if(!empty($alerts))
        <div class="mb-6">
            @foreach($alerts as $alert)
            <div class="bg-{{ $alert['type'] === 'warning' ? 'yellow' : 'red' }}-50 border border-{{ $alert['type'] === 'warning' ? 'yellow' : 'red' }}-200 rounded-lg p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="{{ $alert['icon'] }} text-{{ $alert['type'] === 'warning' ? 'yellow' : 'red' }}-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-{{ $alert['type'] === 'warning' ? 'yellow' : 'red' }}-800">
                            Critical Alert
                        </h3>
                        <div class="mt-2 text-sm text-{{ $alert['type'] === 'warning' ? 'yellow' : 'red' }}-700">
                            {{ $alert['message'] }}
                        </div>
                        @if(isset($alert['action_url']))
                        <div class="mt-4">
                            <a href="{{ $alert['action_url'] }}" class="text-sm font-medium text-{{ $alert['type'] === 'warning' ? 'yellow' : 'red' }}-800 underline hover:text-{{ $alert['type'] === 'warning' ? 'yellow' : 'red' }}-600">
                                Ver Detalles →
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Statistics Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-credit-card text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Total Suscripciones</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">{{ number_format($stats['total']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-check-circle text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Activas</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">{{ number_format($stats['active']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-pause text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Suspendidas</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">{{ number_format($stats['suspended']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-exclamation-triangle text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Pagos Fallidos</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">{{ number_format($stats['failing_payments']) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Business Metrics -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-dollar-sign text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">MRR</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">${{ number_format($metrics['mrr'], 2) }}</p>
                        @if($metrics['mrr_growth'] >= 0)
                        <p class="text-xs sm:text-sm text-green-600">
                            <i class="fas fa-arrow-up"></i> {{ $metrics['mrr_growth'] }}%
                        </p>
                        @else
                        <p class="text-xs sm:text-sm text-red-600">
                            <i class="fas fa-arrow-down"></i> {{ abs($metrics['mrr_growth']) }}%
                        </p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-chart-line text-2xl text-blue-600"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">ARR</dt>
                                <dd class="text-lg font-semibold text-gray-900">${{ number_format($metrics['arr'], 0) }}</dd>
                                <dd class="text-sm text-gray-500">Anual</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-users text-2xl text-indigo-600"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">ARPU</dt>
                                <dd class="text-lg font-semibold text-gray-900">${{ number_format($metrics['arpu'], 2) }}</dd>
                                <dd class="text-sm text-gray-500">Por usuario</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-user-slash text-2xl text-red-600"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Churn Rate</dt>
                                <dd class="text-lg font-semibold text-gray-900">{{ $metrics['churn_rate'] }}%</dd>
                                <dd class="text-sm text-gray-500">Mensual</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Subscriptions Table -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Suscripciones</h2>
                        <div class="flex space-x-2">
                            <select id="statusFilter" class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Todos los estados</option>
                                <option value="active">Activas</option>
                                <option value="suspended">Suspendidas</option>
                                <option value="cancelled">Canceladas</option>
                                <option value="pending">Pendientes</option>
                            </select>
                            <select id="planFilter" class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Todos los planes</option>
                                <option value="trial">Trial</option>
                                <option value="basic">Basic</option>
                                <option value="premium">Premium</option>
                                <option value="enterprise">Enterprise</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table id="subscriptions-table" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empresa</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Próximo Pago</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ingresos</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="subscriptions-body" class="bg-white divide-y divide-gray-200">
                            <!-- Data will be loaded here -->
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>
                                    Cargando suscripciones...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                
                <!-- Recent Activity -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Actividad Reciente</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @forelse($recentActivity as $activity)
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    @if($activity['status'] === 'active')
                                        <div class="w-2 h-2 bg-green-400 rounded-full mt-2"></div>
                                    @elseif($activity['status'] === 'suspended')
                                        <div class="w-2 h-2 bg-yellow-400 rounded-full mt-2"></div>
                                    @else
                                        <div class="w-2 h-2 bg-red-400 rounded-full mt-2"></div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $activity['tenant_name'] }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ $activity['action'] }} - Plan {{ ucfirst($activity['plan']) }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ \Carbon\Carbon::parse($activity['timestamp'])->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            @empty
                            <p class="text-sm text-gray-500 text-center">No hay actividad reciente</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Acciones Rápidas</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <button onclick="syncPayPal()" class="w-full text-left px-4 py-2 text-sm font-medium text-gray-700 bg-gray-50 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <i class="fab fa-paypal mr-2 text-blue-600"></i>
                                Sincronizar con PayPal
                            </button>
                            <button onclick="generateReport()" class="w-full text-left px-4 py-2 text-sm font-medium text-gray-700 bg-gray-50 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <i class="fas fa-chart-bar mr-2 text-green-600"></i>
                                Generar Reporte
                            </button>
                            <button onclick="checkFailedPayments()" class="w-full text-left px-4 py-2 text-sm font-medium text-gray-700 bg-gray-50 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <i class="fas fa-exclamation-triangle mr-2 text-red-600"></i>
                                Revisar Pagos Fallidos
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<!-- Subscription Detail Modal -->
<div id="subscriptionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Detalles de Suscripción</h3>
                <button onclick="closeModal('subscriptionModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="subscription-details" class="mt-2">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let subscriptionsData = [];

document.addEventListener('DOMContentLoaded', function() {
    // Check for filter in URL
    const urlParams = new URLSearchParams(window.location.search);
    const filterParam = urlParams.get('filter');
    
    if (filterParam) {
        // Set the filter based on URL parameter
        if (filterParam === 'suspended') {
            document.getElementById('statusFilter').value = 'suspended';
        } else if (filterParam === 'trial_ending') {
            document.getElementById('statusFilter').value = 'active';
            // Could add additional logic for trial ending filter
        } else if (filterParam === 'failed_payments') {
            // This would need a custom filter
        }
    }
    
    loadSubscriptions();
    
    // Event listeners for filters
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
    document.getElementById('planFilter').addEventListener('change', applyFilters);
});

function loadSubscriptions() {
    const statusFilter = document.getElementById('statusFilter').value;
    const planFilter = document.getElementById('planFilter').value;
    
    // Build query parameters
    let params = new URLSearchParams();
    if (statusFilter) params.append('status', statusFilter);
    if (planFilter) params.append('plan', planFilter);
    
    const url = '{{ route('developer.subscriptions.data') }}' + (params.toString() ? '?' + params.toString() : '');
    
    // Show loading state
    document.getElementById('subscriptions-body').innerHTML = `
        <tr>
            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                <i class="fas fa-spinner fa-spin mr-2"></i>
                Cargando suscripciones...
            </td>
        </tr>
    `;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            subscriptionsData = data.data || [];
            renderSubscriptions(subscriptionsData);
        })
        .catch(error => {
            console.error('Error loading subscriptions:', error);
            document.getElementById('subscriptions-body').innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-red-500">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Error cargando suscripciones
                    </td>
                </tr>
            `;
        });
}

function renderSubscriptions(data) {
    const tbody = document.getElementById('subscriptions-body');
    
    if (data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                    No hay suscripciones para mostrar
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = data.map(subscription => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${subscription.tenant_name || 'N/A'}</div>
                <div class="text-sm text-gray-500">${subscription.tenant_email || ''}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    ${subscription.plan || 'N/A'}
                </span>
                ${subscription.is_trial ? '<span class="ml-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Trial</span>' : ''}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(subscription.status)}">
                    ${subscription.status || 'unknown'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${subscription.next_billing_date || 'N/A'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                $${subscription.amount || '0.00'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button onclick="viewSubscription(${subscription.id})" class="text-indigo-600 hover:text-indigo-900 mr-3">
                    <i class="fas fa-eye"></i>
                </button>
                <button onclick="editSubscription(${subscription.id})" class="text-yellow-600 hover:text-yellow-900 mr-3">
                    <i class="fas fa-edit"></i>
                </button>
                ${subscription.status === 'active' ? 
                    `<button onclick="suspendSubscription(${subscription.id})" class="text-red-600 hover:text-red-900">
                        <i class="fas fa-pause"></i>
                    </button>` :
                    `<button onclick="reactivateSubscription(${subscription.id})" class="text-green-600 hover:text-green-900">
                        <i class="fas fa-play"></i>
                    </button>`
                }
            </td>
        </tr>
    `).join('');
}

function getStatusColor(status) {
    switch(status) {
        case 'active': return 'bg-green-100 text-green-800';
        case 'suspended': return 'bg-yellow-100 text-yellow-800';
        case 'cancelled': return 'bg-red-100 text-red-800';
        case 'pending': return 'bg-blue-100 text-blue-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function applyFilters() {
    // Reload data with filters applied via AJAX
    loadSubscriptions();
}

function refreshData() {
    loadSubscriptions();
}

function exportData() {
    // Implementation for export
    console.log('Exporting data...');
}

function viewSubscription(id) {
    window.open(`{{ route('developer.subscriptions.index') }}/${id}`, '_blank');
}

function editSubscription(id) {
    Swal.fire({
        title: 'Cambiar Plan de Suscripción',
        html: `
            <div class="text-left">
                <div class="form-group">
                    <label for="newPlan">Nuevo Plan:</label>
                    <select id="newPlan" class="form-control">
                        <option value="basic">Plan Básico - $29/mes</option>
                        <option value="premium">Plan Premium - $79/mes</option>
                        <option value="enterprise">Plan Enterprise - $199/mes</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="changeReason">Motivo del cambio:</label>
                    <textarea id="changeReason" class="form-control" rows="3" placeholder="Describe el motivo del cambio..."></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Cambiar Plan',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#28a745',
        preConfirm: () => {
            const newPlan = document.getElementById('newPlan').value;
            const reason = document.getElementById('changeReason').value;
            
            if (!reason.trim()) {
                Swal.showValidationMessage('El motivo del cambio es requerido');
                return false;
            }
            
            return { newPlan, reason };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            changePlan(id, result.value.newPlan, result.value.reason);
        }
    });
}

function suspendSubscription(id) {
    Swal.fire({
        title: '¿Suspender Suscripción?',
        html: `
            <div class="text-left">
                <p class="mb-3">Esta acción suspenderá la suscripción y bloqueará el acceso del usuario.</p>
                <div class="form-group">
                    <label for="suspendReason">Motivo de suspensión:</label>
                    <textarea id="suspendReason" class="form-control" rows="3" placeholder="Describe el motivo de la suspensión..." required></textarea>
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, Suspender',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        preConfirm: () => {
            const reason = document.getElementById('suspendReason').value;
            if (!reason.trim()) {
                Swal.showValidationMessage('El motivo de suspensión es requerido');
                return false;
            }
            return reason;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            performSuspension(id, result.value);
        }
    });
}

function reactivateSubscription(id) {
    Swal.fire({
        title: '¿Reactivar Suscripción?',
        html: `
            <div class="text-left">
                <p class="mb-3">Esta acción reactivará la suscripción y restaurará el acceso del usuario.</p>
                <div class="form-group">
                    <label for="reactivateReason">Motivo de reactivación:</label>
                    <textarea id="reactivateReason" class="form-control" rows="3" placeholder="Describe el motivo de la reactivación..." required></textarea>
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, Reactivar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#28a745',
        preConfirm: () => {
            const reason = document.getElementById('reactivateReason').value;
            if (!reason.trim()) {
                Swal.showValidationMessage('El motivo de reactivación es requerido');
                return false;
            }
            return reason;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            performReactivation(id, result.value);
        }
    });
}

function syncPayPal() {
    console.log('Syncing with PayPal...');
}

function generateReport() {
    console.log('Generating report...');
}

function checkFailedPayments() {
    console.log('Checking failed payments...');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// AJAX Helper Functions
function changePlan(subscriptionId, newPlan, reason) {
    fetch(`{{ route('developer.subscriptions.index') }}/${subscriptionId}/change-plan`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            new_plan: newPlan,
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('¡Éxito!', data.message, 'success');
            loadSubscriptions(); // Reload the table
        } else {
            Swal.fire('Error', data.message || 'Error al cambiar el plan', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'Error de conexión al cambiar el plan', 'error');
    });
}

function performSuspension(subscriptionId, reason) {
    fetch(`{{ route('developer.subscriptions.index') }}/${subscriptionId}/suspend`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('¡Suspendida!', data.message, 'success');
            loadSubscriptions(); // Reload the table
        } else {
            Swal.fire('Error', data.message || 'Error al suspender la suscripción', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'Error de conexión al suspender la suscripción', 'error');
    });
}

function performReactivation(subscriptionId, reason) {
    fetch(`{{ route('developer.subscriptions.index') }}/${subscriptionId}/reactivate`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('¡Reactivada!', data.message, 'success');
            loadSubscriptions(); // Reload the table
        } else {
            Swal.fire('Error', data.message || 'Error al reactivar la suscripción', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'Error de conexión al reactivar la suscripción', 'error');
    });
}

function syncWithPayPal(subscriptionId) {
    Swal.fire({
        title: 'Sincronizando...',
        text: 'Obteniendo datos de PayPal',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch(`{{ route('developer.subscriptions.index') }}/${subscriptionId}/sync-paypal`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('¡Sincronizado!', data.message, 'success');
            loadSubscriptions(); // Reload the table
        } else {
            Swal.fire('Error', data.message || 'Error al sincronizar con PayPal', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'Error de conexión al sincronizar con PayPal', 'error');
    });
}
</script>
@endpush