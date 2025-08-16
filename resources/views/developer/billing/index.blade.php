@extends('layouts.developer')

@section('title', 'Métricas Financieras')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:py-12 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                    <div class="mb-4 sm:mb-0">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1">
                            <i class="fas fa-chart-bar text-green-600 mr-2"></i>
                            Métricas Financieras
                        </h1>
                        <p class="text-xs sm:text-sm text-gray-600">Panel de métricas y análisis financiero del sistema</p>
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                        <a href="{{ route('developer.billing.analytics') }}" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            <i class="fas fa-analytics mr-1 sm:mr-2"></i>
                            Analytics
                        </a>
                        <button onclick="refreshMetrics()" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                            <i class="fas fa-sync mr-1 sm:mr-2"></i>
                            Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Metrics -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6">
            <!-- MRR Card -->
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-chart-line text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">MRR</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">${{ number_format($mrr, 0) }}</p>
                        <p class="text-xs sm:text-sm text-gray-500">Monthly Revenue</p>
                    </div>
                </div>
            </div>

            <!-- ARR Card -->
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-calendar-alt text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">ARR</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">${{ number_format($arr, 0) }}</p>
                        <p class="text-xs sm:text-sm text-gray-500">Annual Revenue</p>
                    </div>
                </div>
            </div>

            <!-- Paying Customers -->
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-users text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Clientes Pago</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">{{ number_format($payingCustomers) }}</p>
                        <p class="text-xs sm:text-sm text-gray-500">Active</p>
                    </div>
                </div>
            </div>

            <!-- Churn Rate -->
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-percentage text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Churn Rate</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">{{ number_format($churnRate, 1) }}%</p>
                        <p class="text-xs sm:text-sm text-gray-500">30 days</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-indigo-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-building text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Total Clientes</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">{{ number_format($totalCustomers) }}</p>
                        <p class="text-xs sm:text-sm text-gray-500">Registrados</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-gift text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">En Trial</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">{{ number_format($trialCustomers) }}</p>
                        <p class="text-xs sm:text-sm text-gray-500">Periodo prueba</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-check-circle text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Conversión</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">
                            {{ $totalCustomers > 0 ? number_format(($payingCustomers / $totalCustomers) * 100, 1) : 0 }}%
                        </p>
                        <p class="text-xs sm:text-sm text-gray-500">Trial a pago</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6">
            <!-- Revenue by Plan -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                        <i class="fas fa-layer-group text-purple-600 mr-2"></i>Ingresos por Plan
                    </h3>
                </div>
                <div class="px-4 sm:px-6 py-4 sm:py-6">
                    @if($revenueByPlan->count() > 0)
                        <div class="space-y-4">
                            @foreach($revenueByPlan as $plan)
                            <div class="relative">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700">
                                        {{ ucfirst($plan->plan) }}
                                    </span>
                                    <span class="text-sm text-gray-600">
                                        ${{ number_format($plan->total, 0) }} ({{ $plan->count }} clientes)
                                    </span>
                                </div>
                                @php
                                    $percentage = $mrr > 0 ? ($plan->total / $mrr) * 100 : 0;
                                @endphp
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-blue-400 to-blue-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-chart-pie fa-3x mb-3 text-gray-300"></i>
                            <p>No hay ingresos activos</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Monthly Revenue Trend -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                        <i class="fas fa-chart-area text-green-600 mr-2"></i>Tendencia (6 meses)
                    </h3>
                </div>
                <div class="px-4 sm:px-6 py-4 sm:py-6">
                    <canvas id="revenueChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                    <i class="fas fa-bolt text-yellow-600 mr-2"></i>Acciones Rápidas
                </h3>
            </div>
            <div class="px-4 sm:px-6 py-4 sm:py-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="{{ route('developer.subscriptions.index') }}" class="inline-flex items-center justify-center px-4 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-700">
                        <i class="fas fa-credit-card mr-2"></i>
                        Ver Suscripciones
                    </a>
                    
                    <a href="{{ route('developer.billing.analytics') }}" class="inline-flex items-center justify-center px-4 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-700">
                        <i class="fas fa-chart-line mr-2"></i>
                        Analytics
                    </a>
                    
                    <a href="{{ route('developer.billing.mrr-report') }}" class="inline-flex items-center justify-center px-4 py-3 bg-purple-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-purple-700">
                        <i class="fas fa-file-invoice-dollar mr-2"></i>
                        Reporte MRR
                    </a>
                    
                    <a href="{{ route('developer.billing.payment-logs') }}" class="inline-flex items-center justify-center px-4 py-3 bg-gray-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-gray-700">
                        <i class="fas fa-history mr-2"></i>
                        Logs Pagos
                    </a>
                </div>
            </div>
        </div>

        <!-- Revenue Statistics Table -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                    <i class="fas fa-table text-blue-600 mr-2"></i>Estadísticas Detalladas
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Métrica</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Actual</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mes Anterior</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cambio</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">MRR</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($mrr, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="text-green-600">-</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Clientes Activos</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $payingCustomers }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="text-green-600">-</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">ARPU</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${{ $payingCustomers > 0 ? number_format($mrr / $payingCustomers, 2) : 0 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="text-gray-500">-</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Churn Rate</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($churnRate, 1) }}%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="text-gray-500">-</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function refreshMetrics() {
    DevAlert.loading('Actualizando...', 'Obteniendo métricas más recientes');
    
    setTimeout(() => {
        DevAlert.close();
        DevAlert.success('Métricas Actualizadas', 'Las métricas financieras han sido actualizadas.');
        location.reload();
    }, 1500);
}
</script>
<script>
// Revenue Chart
const ctx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($monthlyRevenue, 'month')) !!},
        datasets: [{
            label: 'Ingresos Mensuales',
            data: {!! json_encode(array_column($monthlyRevenue, 'revenue')) !!},
            borderColor: 'rgb(99, 102, 241)',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return '$' + context.parsed.y.toFixed(2);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value;
                    }
                }
            }
        }
    }
});
</script>
@endpush