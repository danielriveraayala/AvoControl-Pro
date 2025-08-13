@extends('layouts.developer')

@section('title', 'Gestión de Suscripciones')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-credit-card text-indigo-600 mr-3"></i>
                        Gestión de Suscripciones
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">Monitor and manage all tenant subscriptions and billing</p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <button onclick="refreshData()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-refresh mr-2"></i>
                        Refresh
                    </button>
                    <button onclick="exportData()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-download mr-2"></i>
                        Export
                    </button>
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
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-credit-card text-2xl text-blue-500"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Suscripciones</dt>
                                <dd class="text-lg font-semibold text-gray-900">{{ number_format($stats['total']) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-2xl text-green-500"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Activas</dt>
                                <dd class="text-lg font-semibold text-gray-900">{{ number_format($stats['active']) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-pause text-2xl text-yellow-500"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Suspendidas</dt>
                                <dd class="text-lg font-semibold text-gray-900">{{ number_format($stats['suspended']) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-2xl text-red-500"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Pagos Fallidos</dt>
                                <dd class="text-lg font-semibold text-gray-900">{{ number_format($stats['failing_payments']) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Business Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-dollar-sign text-2xl text-green-600"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">MRR</dt>
                                <dd class="text-lg font-semibold text-gray-900">${{ number_format($metrics['mrr'], 2) }}</dd>
                                @if($metrics['mrr_growth'] >= 0)
                                <dd class="text-sm text-green-600">
                                    <i class="fas fa-arrow-up"></i> {{ $metrics['mrr_growth'] }}%
                                </dd>
                                @else
                                <dd class="text-sm text-red-600">
                                    <i class="fas fa-arrow-down"></i> {{ abs($metrics['mrr_growth']) }}%
                                </dd>
                                @endif
                            </dl>
                        </div>
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
    loadSubscriptions();
    
    // Event listeners for filters
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
    document.getElementById('planFilter').addEventListener('change', applyFilters);
});

function loadSubscriptions() {
    // Simulate loading data - in real implementation, this would be an AJAX call
    fetch('{{ route('developer.subscriptions.data') }}')
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
    const statusFilter = document.getElementById('statusFilter').value;
    const planFilter = document.getElementById('planFilter').value;
    
    let filteredData = subscriptionsData;
    
    if (statusFilter) {
        filteredData = filteredData.filter(sub => sub.status === statusFilter);
    }
    
    if (planFilter) {
        filteredData = filteredData.filter(sub => sub.plan === planFilter);
    }
    
    renderSubscriptions(filteredData);
}

function refreshData() {
    loadSubscriptions();
}

function exportData() {
    // Implementation for export
    console.log('Exporting data...');
}

function viewSubscription(id) {
    // Show subscription details modal
    console.log('Viewing subscription:', id);
}

function editSubscription(id) {
    // Show edit subscription modal
    console.log('Editing subscription:', id);
}

function suspendSubscription(id) {
    // Suspend subscription
    console.log('Suspending subscription:', id);
}

function reactivateSubscription(id) {
    // Reactivate subscription
    console.log('Reactivating subscription:', id);
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
</script>
@endpush