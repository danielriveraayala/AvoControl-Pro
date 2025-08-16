@extends('layouts.developer')

@section('title', 'Billing Analytics')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:py-12 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                    <div class="mb-4 sm:mb-0">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1">
                            <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                            Billing Analytics
                        </h1>
                        <p class="text-xs sm:text-sm text-gray-600">Advanced billing metrics and revenue analytics</p>
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                        <a href="{{ route('developer.billing.index') }}" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            ← <span class="ml-1">Billing</span>
                        </a>
                        <button onclick="exportAnalytics()" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            <i class="fas fa-download mr-1 sm:mr-2"></i>
                            Export
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Metrics -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-dollar-sign text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Monthly Revenue</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">$0.00</p>
                        <p class="text-xs sm:text-sm text-green-600">
                            <i class="fas fa-arrow-up"></i> 0.0%
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-chart-line text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Annual Revenue</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">$0.00</p>
                        <p class="text-xs sm:text-sm text-gray-500">ARR</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-indigo-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-users text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">ARPU</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">$0.00</p>
                        <p class="text-xs sm:text-sm text-gray-500">Per User</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-percentage text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Churn Rate</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">0.0%</p>
                        <p class="text-xs sm:text-sm text-gray-500">Monthly</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6">
            <!-- Revenue Chart -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                        <i class="fas fa-chart-area text-green-600 mr-2"></i>Revenue Trend
                    </h3>
                </div>
                <div class="px-4 sm:px-6 py-4 sm:py-6">
                    <div class="h-64 flex items-center justify-center text-gray-500">
                        <div class="text-center">
                            <i class="fas fa-chart-area text-4xl mb-4"></i>
                            <p>Revenue chart will be displayed here</p>
                            <p class="text-sm">Integration with Chart.js pending</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscription Growth -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                        <i class="fas fa-users text-blue-600 mr-2"></i>Subscription Growth
                    </h3>
                </div>
                <div class="px-4 sm:px-6 py-4 sm:py-6">
                    <div class="h-64 flex items-center justify-center text-gray-500">
                        <div class="text-center">
                            <i class="fas fa-chart-line text-4xl mb-4"></i>
                            <p>Subscription growth chart will be displayed here</p>
                            <p class="text-sm">Integration with Chart.js pending</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <!-- Top Plans by Revenue -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                        <i class="fas fa-trophy text-yellow-600 mr-2"></i>Top Plans by Revenue
                    </h3>
                </div>
                <div class="px-4 sm:px-6 py-4 sm:py-6">
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-layer-group text-4xl mb-4"></i>
                        <p>No revenue data available</p>
                        <p class="text-sm">Data will appear when subscriptions are active</p>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-2 sm:mb-0">
                            <i class="fas fa-receipt text-purple-600 mr-2"></i>Recent Transactions
                        </h3>
                        <a href="{{ route('developer.billing.payment-logs') }}" class="inline-flex items-center justify-center px-3 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700">
                            View All
                        </a>
                    </div>
                </div>
                <div class="px-4 sm:px-6 py-4 sm:py-6">
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-credit-card text-4xl mb-4"></i>
                        <p>No transactions found</p>
                        <p class="text-sm">Payment transactions will appear here</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function exportAnalytics() {
    DevAlert.info('Export Feature', 'Analytics export functionality will be implemented soon.');
}
</script>
@endpush