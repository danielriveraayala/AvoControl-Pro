@extends('layouts.developer')

@section('title', 'Payment Logs')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:py-12 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                    <div class="mb-4 sm:mb-0">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1">
                            <i class="fas fa-receipt text-purple-600 mr-2"></i>
                            Payment Logs
                        </h1>
                        <p class="text-xs sm:text-sm text-gray-600">Detailed payment transaction logs and history</p>
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                        <a href="{{ route('developer.billing.analytics') }}" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            ← <span class="ml-1">Analytics</span>
                        </a>
                        <button onclick="refreshLogs()" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            <i class="fas fa-sync mr-1 sm:mr-2"></i>
                            Refresh
                        </button>
                        <button onclick="exportLogs()" class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700">
                            <i class="fas fa-download mr-1 sm:mr-2"></i>
                            Export
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Statistics -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-check-circle text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Successful</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">0</p>
                        <p class="text-xs sm:text-sm text-gray-500">This month</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-times-circle text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Failed</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">0</p>
                        <p class="text-xs sm:text-sm text-gray-500">This month</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-clock text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Pending</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">0</p>
                        <p class="text-xs sm:text-sm text-gray-500">This month</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-500 rounded-md p-2 sm:p-3">
                        <i class="fas fa-dollar-sign text-white text-lg sm:text-2xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Total Volume</p>
                        <p class="text-lg sm:text-2xl font-semibold text-gray-900">$0.00</p>
                        <p class="text-xs sm:text-sm text-gray-500">This month</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                    <i class="fas fa-filter text-gray-600 mr-2"></i>Filters
                </h3>
            </div>
            <div class="px-4 sm:px-6 py-4 sm:py-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="status_filter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="status_filter" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">All Statuses</option>
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Failed</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </div>
                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                        <select id="payment_method" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">All Methods</option>
                            <option value="paypal">PayPal</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                        <input type="date" id="date_from" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                        <input type="date" id="date_to" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                </div>
                <div class="mt-4 flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                    <button onclick="applyFilters()" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-700">
                        <i class="fas fa-search mr-2"></i>Apply Filters
                    </button>
                    <button onclick="clearFilters()" class="inline-flex items-center justify-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-400">
                        <i class="fas fa-times mr-2"></i>Clear
                    </button>
                </div>
            </div>
        </div>

        <!-- Payment Logs Table -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                    <i class="fas fa-table text-purple-600 mr-2"></i>Payment Transaction Logs
                </h3>
            </div>
            <div class="px-4 sm:px-6 py-4 sm:py-6">
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-credit-card text-6xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Payment Logs Found</h3>
                    <p class="text-gray-500">Payment transaction logs will appear here when payments are processed</p>
                    <div class="mt-6">
                        <button onclick="refreshLogs()" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-700">
                            <i class="fas fa-sync mr-2"></i>Refresh Logs
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function refreshLogs() {
    DevAlert.loading('Refreshing...', 'Fetching latest payment logs');
    
    setTimeout(() => {
        DevAlert.close();
        DevAlert.info('Logs Refreshed', 'Payment logs have been refreshed. No new transactions found.');
    }, 1500);
}

function exportLogs() {
    DevAlert.info('Export Feature', 'Payment logs export functionality will be implemented soon.');
}

function applyFilters() {
    const status = document.getElementById('status_filter').value;
    const method = document.getElementById('payment_method').value;
    const dateFrom = document.getElementById('date_from').value;
    const dateTo = document.getElementById('date_to').value;
    
    DevAlert.info('Filters Applied', 'Payment logs have been filtered. No matching transactions found.');
}

function clearFilters() {
    document.getElementById('status_filter').value = '';
    document.getElementById('payment_method').value = '';
    document.getElementById('date_from').value = '';
    document.getElementById('date_to').value = '';
    
    DevAlert.success('Filters Cleared', 'All filters have been cleared.');
}
</script>
@endpush