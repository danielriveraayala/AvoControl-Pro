@extends('layouts.developer')

@section('title', 'Churn Analysis')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:py-12 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                    <div class="mb-4 sm:mb-0">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1">
                            <i class="fas fa-user-times text-red-600 mr-2"></i>
                            Churn Analysis
                        </h1>
                        <p class="text-xs sm:text-sm text-gray-600">Customer retention and churn metrics analysis</p>
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                        <a href="{{ route('developer.billing.analytics') }}" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            ← <span class="ml-1">Analytics</span>
                        </a>
                        <button onclick="exportChurn()" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                            <i class="fas fa-download mr-1 sm:mr-2"></i>
                            Export
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Churn Metrics -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-percentage text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Monthly Churn Rate</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">0.0%</p>
                        <p class="text-xs sm:text-sm text-gray-500">This month</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-orange-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-user-minus text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Churned Customers</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">0</p>
                        <p class="text-xs sm:text-sm text-gray-500">This month</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-clock text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Avg. Lifetime</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">0</p>
                        <p class="text-xs sm:text-sm text-gray-500">Days</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-dollar-sign text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">LTV</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">$0.00</p>
                        <p class="text-xs sm:text-sm text-gray-500">Lifetime Value</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6">
            <!-- Churn Rate Trend -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                        <i class="fas fa-chart-line text-red-600 mr-2"></i>Churn Rate Trend
                    </h3>
                </div>
                <div class="px-4 sm:px-6 py-4 sm:py-6">
                    <div class="h-64 flex items-center justify-center text-gray-500">
                        <div class="text-center">
                            <i class="fas fa-chart-line text-4xl mb-4"></i>
                            <p>Churn rate chart will be displayed here</p>
                            <p class="text-sm">Integration with Chart.js pending</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Churn by Plan -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                        <i class="fas fa-chart-pie text-orange-600 mr-2"></i>Churn by Plan
                    </h3>
                </div>
                <div class="px-4 sm:px-6 py-4 sm:py-6">
                    <div class="h-64 flex items-center justify-center text-gray-500">
                        <div class="text-center">
                            <i class="fas fa-chart-pie text-4xl mb-4"></i>
                            <p>Plan churn distribution chart will be displayed here</p>
                            <p class="text-sm">Integration with Chart.js pending</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Churn Analysis Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <!-- Top Churn Reasons -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                        <i class="fas fa-list-alt text-yellow-600 mr-2"></i>Top Churn Reasons
                    </h3>
                </div>
                <div class="px-4 sm:px-6 py-4 sm:py-6">
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-question-circle text-4xl mb-4"></i>
                        <p>No churn data available</p>
                        <p class="text-sm">Churn reasons will appear when customers cancel</p>
                    </div>
                </div>
            </div>

            <!-- At-Risk Customers -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                        <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>At-Risk Customers
                    </h3>
                </div>
                <div class="px-4 sm:px-6 py-4 sm:py-6">
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-shield-alt text-4xl mb-4"></i>
                        <p>No at-risk customers identified</p>
                        <p class="text-sm">Machine learning analysis will identify customers at risk of churning</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function exportChurn() {
    DevAlert.info('Export Feature', 'Churn analysis export functionality will be implemented soon.');
}
</script>
@endpush