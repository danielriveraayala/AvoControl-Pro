@extends('layouts.developer')

@section('title', 'Métricas Financieras')

@section('content')
<div class="bg-white shadow-sm">
    <div class="px-4 sm:px-6 lg:mx-auto lg:max-w-7xl lg:px-8">
        <div class="py-6">
            <h1 class="text-2xl font-semibold text-gray-900">Métricas Financieras</h1>
        </div>
    </div>
</div>

<div class="mt-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        
        <!-- Main Metrics -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <!-- MRR Card -->
            <div class="bg-gradient-to-r from-green-400 to-green-600 overflow-hidden shadow-lg rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-white bg-opacity-30 rounded-md p-3">
                                <i class="fas fa-chart-line text-white text-2xl"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-green-50 truncate">MRR (Monthly Recurring Revenue)</dt>
                                <dd class="text-3xl font-bold text-white">${{ number_format($mrr, 0) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ARR Card -->
            <div class="bg-gradient-to-r from-blue-400 to-blue-600 overflow-hidden shadow-lg rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-white bg-opacity-30 rounded-md p-3">
                                <i class="fas fa-calendar-alt text-white text-2xl"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-blue-50 truncate">ARR (Annual Recurring Revenue)</dt>
                                <dd class="text-3xl font-bold text-white">${{ number_format($arr, 0) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Paying Customers -->
            <div class="bg-gradient-to-r from-purple-400 to-purple-600 overflow-hidden shadow-lg rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-white bg-opacity-30 rounded-md p-3">
                                <i class="fas fa-users text-white text-2xl"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-purple-50 truncate">Clientes de Pago</dt>
                                <dd class="text-3xl font-bold text-white">{{ number_format($payingCustomers) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Churn Rate -->
            <div class="bg-gradient-to-r from-red-400 to-red-600 overflow-hidden shadow-lg rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-white bg-opacity-30 rounded-md p-3">
                                <i class="fas fa-percentage text-white text-2xl"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-red-50 truncate">Tasa de Abandono (30d)</dt>
                                <dd class="text-3xl font-bold text-white">{{ number_format($churnRate, 1) }}%</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Metrics -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 lg:grid-cols-3 mb-8">
            <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-indigo-500 rounded-md p-3">
                                <i class="fas fa-building text-white text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Clientes</dt>
                                <dd class="text-2xl font-semibold text-gray-900">{{ number_format($totalCustomers) }}</dd>
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
                                <i class="fas fa-gift text-white text-xl"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">En Trial</dt>
                                <dd class="text-2xl font-semibold text-gray-900">{{ number_format($trialCustomers) }}</dd>
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
                                <dt class="text-sm font-medium text-gray-500 truncate">Conversión Rate</dt>
                                <dd class="text-2xl font-semibold text-gray-900">
                                    {{ $totalCustomers > 0 ? number_format(($payingCustomers / $totalCustomers) * 100, 1) : 0 }}%
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Revenue by Plan -->
            <div class="bg-white shadow-lg rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-layer-group mr-2 text-gray-500"></i>
                        Ingresos por Plan
                    </h3>
                </div>
                <div class="p-6">
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
            <div class="bg-white shadow-lg rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-chart-area mr-2 text-gray-500"></i>
                        Tendencia de Ingresos (6 meses)
                    </h3>
                </div>
                <div class="p-6">
                    <canvas id="revenueChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white shadow-lg rounded-lg mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-bolt mr-2 text-yellow-500"></i>
                    Acciones Rápidas
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="{{ route('developer.subscriptions.index') }}" class="inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-credit-card mr-2"></i>
                        Ver Suscripciones
                    </a>
                    
                    <a href="{{ route('developer.billing.analytics') }}" class="inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <i class="fas fa-analytics mr-2"></i>
                        Análisis Detallado
                    </a>
                    
                    <a href="{{ route('developer.billing.mrr-report') }}" class="inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        <i class="fas fa-file-invoice-dollar mr-2"></i>
                        Reporte MRR
                    </a>
                    
                    <a href="{{ route('developer.billing.payment-logs') }}" class="inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        <i class="fas fa-history mr-2"></i>
                        Logs de Pagos
                    </a>
                </div>
            </div>
        </div>

        <!-- Revenue Statistics Table -->
        <div class="bg-white shadow-lg rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-table mr-2 text-gray-500"></i>
                    Estadísticas Detalladas
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