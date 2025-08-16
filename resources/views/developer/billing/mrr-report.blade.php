@extends('layouts.developer')

@section('title', 'MRR Report')

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
                            Monthly Recurring Revenue Report
                        </h1>
                        <p class="text-xs sm:text-sm text-gray-600">Detailed analysis of monthly recurring revenue metrics</p>
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                        <a href="{{ route('developer.billing.analytics') }}" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            ← <span class="ml-1">Analytics</span>
                        </a>
                        <button onclick="exportMRR()" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                            <i class="fas fa-download mr-1 sm:mr-2"></i>
                            Export MRR
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MRR Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-dollar-sign text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Current MRR</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">$0.00</p>
                        <p class="text-xs sm:text-sm text-green-600">
                            <i class="fas fa-arrow-up"></i> 0.0% vs last month
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-plus text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">New MRR</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">$0.00</p>
                        <p class="text-xs sm:text-sm text-gray-500">This month</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-minus text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Churned MRR</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">$0.00</p>
                        <p class="text-xs sm:text-sm text-gray-500">This month</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- MRR Chart -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                    <i class="fas fa-chart-line text-green-600 mr-2"></i>MRR Trend (Last 12 Months)
                </h3>
            </div>
            <div class="px-4 sm:px-6 py-4 sm:py-6">
                <div class="h-80 flex items-center justify-center text-gray-500">
                    <div class="text-center">
                        <i class="fas fa-chart-line text-6xl mb-4"></i>
                        <p class="text-lg">MRR Chart</p>
                        <p class="text-sm">Chart.js integration pending</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- MRR Breakdown -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                    <i class="fas fa-list text-purple-600 mr-2"></i>MRR Breakdown by Plan
                </h3>
            </div>
            <div class="px-4 sm:px-6 py-4 sm:py-6">
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-table text-4xl mb-4"></i>
                    <p>No MRR data available</p>
                    <p class="text-sm">Data will appear when active subscriptions generate revenue</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function exportMRR() {
    DevAlert.info('Export Feature', 'MRR export functionality will be implemented soon.');
}
</script>
@endpush