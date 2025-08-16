<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LotController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\AcopioController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Landing Page Routes (Public - redirect if authenticated)
Route::middleware([\App\Http\Middleware\RedirectAuthenticatedFromLanding::class])->group(function () {
    Route::get('/', [App\Http\Controllers\LandingPageController::class, 'index'])->name('home');
    Route::get('/pricing', [App\Http\Controllers\LandingPageController::class, 'pricing'])->name('pricing');
    Route::get('/features', [App\Http\Controllers\LandingPageController::class, 'features'])->name('features');
    Route::get('/contact', [App\Http\Controllers\LandingPageController::class, 'contact'])->name('contact');
    Route::post('/contact', [App\Http\Controllers\LandingPageController::class, 'contactSubmit'])->name('contact.submit');
    Route::get('/plan/{key}', [App\Http\Controllers\LandingPageController::class, 'showPlan'])->name('plan.show');
});

// Ping route for connectivity checks
Route::get('/ping', function () {
    return response()->json(['status' => 'ok']);
});


// Public push notification endpoint (needed before auth)
Route::get('/push/vapid-key', [App\Http\Controllers\PushNotificationController::class, 'getVapidKey']);

// Tenant routes (available for authenticated users)
Route::middleware(['auth'])->prefix('tenant')->name('tenant.')->group(function () {
    Route::get('/select', [App\Http\Controllers\TenantController::class, 'select'])->name('select');
    Route::get('/switch/{tenant:slug}', [App\Http\Controllers\TenantController::class, 'switch'])->name('switch');
    
    // API endpoints
    Route::get('/current', [App\Http\Controllers\TenantController::class, 'current'])->name('current');
    Route::get('/available', [App\Http\Controllers\TenantController::class, 'available'])->name('available');
    Route::get('/stats/{tenant?}', [App\Http\Controllers\TenantController::class, 'stats'])->name('stats');
    
    // Management endpoints (Super Admin only)
    Route::middleware(['role:super_admin'])->group(function () {
        Route::post('/', [App\Http\Controllers\TenantController::class, 'store'])->name('store');
        Route::put('/{tenant}', [App\Http\Controllers\TenantController::class, 'update'])->name('update');
    });
});

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Push Notifications endpoints
    Route::prefix('push')->group(function () {
        Route::post('/subscribe', [App\Http\Controllers\PushNotificationController::class, 'subscribe']);
        Route::post('/unsubscribe', [App\Http\Controllers\PushNotificationController::class, 'unsubscribe']);
        Route::post('/test', [App\Http\Controllers\PushNotificationController::class, 'sendTest']);
        Route::get('/status', [App\Http\Controllers\PushNotificationController::class, 'getStatus']);
        Route::post('/track', [App\Http\Controllers\PushNotificationController::class, 'track']);
        Route::get('/stats', [App\Http\Controllers\PushNotificationController::class, 'getStats']);
        Route::post('/cleanup', [App\Http\Controllers\PushNotificationController::class, 'cleanup']);
    });
    
    // Resources with RBAC permission middleware
    Route::resource('lots', LotController::class)->middleware('rbac:permission,lots.create,lots.read,lots.update,lots.delete');
    Route::resource('sales', SaleController::class)->middleware('rbac:permission,sales.create,sales.read,sales.update,sales.delete');
    Route::resource('payments', PaymentController::class)->middleware('rbac:permission,payments.create,payments.read,payments.update,payments.delete');
    Route::resource('suppliers', SupplierController::class)->middleware('rbac:permission,suppliers.create,suppliers.read,suppliers.update,suppliers.delete');
    Route::resource('customers', CustomerController::class)->middleware('rbac:permission,customers.create,customers.read,customers.update,customers.delete');
    
    // Acopio routes - protected with view reports permission
    Route::prefix('acopio')->middleware('permission:view_reports')->group(function () {
        Route::get('/', [AcopioController::class, 'index'])->name('acopio.index');
        Route::get('/{quality}', [AcopioController::class, 'show'])->name('acopio.show');
    });
    Route::get('acopio-reporte', [AcopioController::class, 'reporte'])->name('acopio.reporte')->middleware('permission:view_reports');
    
    
    // Lots routes - protected with appropriate permissions
    Route::prefix('lots')->middleware('permission:manage_lots')->group(function () {
        Route::get('{lot}/report', [LotController::class, 'report'])->name('lots.report')->middleware('permission:view_reports');
        Route::get('{lot}/pdf', [LotController::class, 'downloadPDF'])->name('lots.pdf')->middleware('permission:export_data');
        Route::get('{lot}/payments', [LotController::class, 'payments'])->name('lots.payments')->middleware('permission:view_payments');
        Route::post('{lot}/payments', [LotController::class, 'addPayment'])->name('lots.payments.add')->middleware('permission:create_payments');
        Route::get('{lot}/payment-timeline', [LotController::class, 'paymentTimeline'])->name('lots.payment-timeline')->middleware('permission:view_payments');
        Route::get('{lot}/payment-form', [LotController::class, 'paymentForm'])->name('lots.payment-form')->middleware('permission:create_payments');
        Route::get('{lot}/debug-payments', [LotController::class, 'debugPayments'])->name('lots.debug-payments')->middleware('role:super_admin');
    });
    
    // Sales AJAX routes - protected with manage sales permission
    Route::prefix('sales')->middleware('permission:manage_sales')->group(function() {
        Route::get('{sale}/details', [SaleController::class, 'details'])->name('sales.details');
        Route::get('{sale}/edit-modal', [SaleController::class, 'editModal'])->name('sales.edit-modal')->middleware('permission:edit_sales');
        Route::post('{sale}/update-modal', [SaleController::class, 'updateModal'])->name('sales.update-modal')->middleware('permission:edit_sales');
        Route::patch('{sale}/status', [SaleController::class, 'updateStatus'])->name('sales.update-status')->middleware('permission:edit_sales');
        Route::get('{sale}/payment-form', [SaleController::class, 'paymentForm'])->name('sales.payment-form')->middleware('permission:create_payments');
        Route::get('{sale}/payment-timeline', [SaleController::class, 'paymentTimeline'])->name('sales.payment-timeline')->middleware('permission:view_payments');
        Route::get('{sale}/invoice', [SaleController::class, 'invoice'])->name('sales.invoice')->middleware('permission:export_data');
        Route::patch('{sale}/deliver', [SaleController::class, 'markDelivered'])->name('sales.deliver')->middleware('permission:edit_sales');
    });
    
    // Payment routes - protected with manage payments permission
    Route::prefix('payments')->middleware('permission:manage_payments')->group(function() {
        Route::get('sales/{sale}/create', [PaymentController::class, 'createSalePayment'])->name('payments.sale.create');
        Route::post('sales/{sale}/store', [PaymentController::class, 'storeSalePayment'])->name('payments.sale.store');
        Route::get('lots/{lot}/create', [PaymentController::class, 'createLotPayment'])->name('payments.lot.create');
        Route::post('lots/{lot}/store', [PaymentController::class, 'storeLotPayment'])->name('payments.lot.store');
        Route::get('cash-flow', [PaymentController::class, 'dailyCashFlow'])->name('payments.cash-flow')->middleware('permission:view_financial_reports');
        Route::post('sale-payment', [PaymentController::class, 'storeSalePayment'])->name('payments.store-sale-payment');
        Route::post('lot-payment', [PaymentController::class, 'storeLotPayment'])->name('payments.store-lot-payment');
    });
    
    // Notification routes - available to all authenticated users
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
        Route::post('/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
        Route::post('/{id}/mark-as-read', [NotificationController::class, 'markOneAsRead'])->name('notifications.markOneAsRead');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
        Route::get('/preferences', [NotificationController::class, 'preferences'])->name('notifications.preferences');
        Route::post('/preferences', [NotificationController::class, 'updatePreferences'])->name('notifications.updatePreferences');
    });
    
    // Configuration routes - protected with configuration view permission
    Route::prefix('configuration')->middleware('permission:configuration.view')->group(function () {
        Route::get('/', [ConfigurationController::class, 'index'])->name('configuration.index');
        Route::get('/qualities-table', [ConfigurationController::class, 'getQualitiesTable'])->name('configuration.qualities.table');
        Route::post('/quality', [ConfigurationController::class, 'storeQuality'])->name('configuration.quality.store');
        Route::get('/quality/{qualityGrade}', [ConfigurationController::class, 'showQuality'])->name('configuration.quality.show');
        Route::put('/quality/{qualityGrade}', [ConfigurationController::class, 'updateQuality'])->name('configuration.quality.update');
        Route::delete('/quality/{qualityGrade}', [ConfigurationController::class, 'destroyQuality'])->name('configuration.quality.destroy');
        
        // Company configuration routes
        Route::get('/company/get', [ConfigurationController::class, 'getCompanyConfig'])->name('configuration.company.get');
        Route::post('/company/store', [ConfigurationController::class, 'storeCompanyConfig'])->name('configuration.company.store');
        
        // Billing/Subscription routes
        Route::get('/subscription/current', [ConfigurationController::class, 'getCurrentSubscription'])->name('configuration.subscription.current');
        Route::get('/subscription/invoices', [ConfigurationController::class, 'getSubscriptionInvoices'])->name('configuration.subscription.invoices');
        Route::get('/subscription/invoices/{id}/download', [ConfigurationController::class, 'downloadInvoice'])->name('configuration.subscription.invoice.download');
        Route::get('/subscription/available-plans', [ConfigurationController::class, 'getAvailablePlans'])->name('configuration.subscription.plans');
        Route::post('/subscription/change-plan', [ConfigurationController::class, 'changePlan'])->name('configuration.subscription.change-plan');
    });
    
    
    // Profile routes
    Route::prefix('perfil')->name('profile.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ProfileController::class, 'index'])->name('index');
        Route::post('/update', [\App\Http\Controllers\ProfileController::class, 'update'])->name('update');
        Route::post('/settings', [\App\Http\Controllers\ProfileController::class, 'updateSettings'])->name('settings');
        Route::post('/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('password');
    });
    
    // Reports routes - protected with view reports permission
    Route::prefix('reportes')->name('reports.')->middleware('permission:view_reports')->group(function () {
        Route::get('rentabilidad', [ReportController::class, 'profitability'])->name('profitability')->middleware('permission:view_financial_reports');
        Route::get('rentabilidad-lotes', [ReportController::class, 'lotProfitability'])->name('lot-profitability')->middleware('permission:view_financial_reports');
        Route::get('analisis-clientes', [ReportController::class, 'customerAnalysis'])->name('customer-analysis');
        Route::get('analisis-proveedores', [ReportController::class, 'supplierAnalysis'])->name('supplier-analysis');
    });
});

// Developer Panel Routes - Exclusive for super_admin
Route::prefix('developer')
    ->middleware(['auth', App\Http\Middleware\DeveloperOnly::class])
    ->name('developer.')
    ->group(function () {
        // Dashboard
        Route::get('/', [App\Http\Controllers\Developer\DeveloperController::class, 'index'])->name('index');
        
        // System Management
        Route::get('/logs', [App\Http\Controllers\Developer\DeveloperController::class, 'logs'])->name('logs');
        Route::post('/cache/clear', [App\Http\Controllers\Developer\DeveloperController::class, 'clearCache'])->name('cache.clear');
        Route::post('/maintenance', [App\Http\Controllers\Developer\DeveloperController::class, 'maintenance'])->name('maintenance');
        
        // User Management
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [App\Http\Controllers\Developer\UserManagementController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Developer\UserManagementController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Developer\UserManagementController::class, 'store'])->name('store');
            Route::get('/{user}', [App\Http\Controllers\Developer\UserManagementController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [App\Http\Controllers\Developer\UserManagementController::class, 'edit'])->name('edit');
            Route::put('/{user}', [App\Http\Controllers\Developer\UserManagementController::class, 'update'])->name('update');
            Route::delete('/{user}', [App\Http\Controllers\Developer\UserManagementController::class, 'destroy'])->name('destroy');
            Route::post('/{user}/roles', [App\Http\Controllers\Developer\UserManagementController::class, 'assignRoles'])->name('assign-roles');
            Route::post('/{user}/suspend', [App\Http\Controllers\Developer\UserManagementController::class, 'suspend'])->name('suspend');
            Route::post('/{user}/activate', [App\Http\Controllers\Developer\UserManagementController::class, 'activate'])->name('activate');
            Route::post('/{user}/reset-password', [App\Http\Controllers\Developer\UserManagementController::class, 'resetPassword'])->name('reset-password');
        });
        
        // System Actions
        Route::post('/cache/clear', [App\Http\Controllers\Developer\DeveloperController::class, 'clearCache'])->name('clear-cache');
        Route::post('/maintenance', [App\Http\Controllers\Developer\DeveloperController::class, 'maintenance'])->name('maintenance');
        Route::get('/logs', [App\Http\Controllers\Developer\DeveloperController::class, 'logs'])->name('logs');
        
        // System Configuration
        Route::prefix('config')->name('config.')->group(function () {
            Route::get('/', [App\Http\Controllers\Developer\SystemConfigController::class, 'index'])->name('index');
            Route::get('/smtp', [App\Http\Controllers\Developer\SystemConfigController::class, 'smtp'])->name('smtp');
            Route::post('/smtp', [App\Http\Controllers\Developer\SystemConfigController::class, 'updateSmtp'])->name('smtp.update');
            Route::post('/smtp/test', [App\Http\Controllers\Developer\SystemConfigController::class, 'testSmtp'])->name('smtp.test');
            Route::get('/notifications', [App\Http\Controllers\Developer\SystemConfigController::class, 'notifications'])->name('notifications');
            Route::post('/notifications', [App\Http\Controllers\Developer\SystemConfigController::class, 'updateNotifications'])->name('notifications.update');
            Route::post('/notifications/test', [App\Http\Controllers\Developer\SystemConfigController::class, 'testNotifications'])->name('notifications.test');
            Route::get('/vapid', [App\Http\Controllers\Developer\SystemConfigController::class, 'vapid'])->name('vapid');
            Route::post('/vapid/generate', [App\Http\Controllers\Developer\SystemConfigController::class, 'generateVapid'])->name('vapid.generate');
            Route::get('/notifications-manager', [App\Http\Controllers\Developer\SystemConfigController::class, 'notificationsManager'])->name('notifications-manager');
            Route::get('/notifications-manager/data', [App\Http\Controllers\Developer\SystemConfigController::class, 'getNotificationsData'])->name('notifications-manager.data');
            Route::delete('/notifications-manager/{notification}', [App\Http\Controllers\Developer\SystemConfigController::class, 'deleteNotification'])->name('notifications-manager.delete');
        });
        
        // Backup Management
        Route::prefix('backups')->name('backups.')->group(function () {
            Route::get('/', [App\Http\Controllers\Developer\BackupController::class, 'index'])->name('index');
            Route::post('/create', [App\Http\Controllers\Developer\BackupController::class, 'create'])->name('create');
            Route::get('/download/{filename}', [App\Http\Controllers\Developer\BackupController::class, 'download'])->name('download');
            Route::delete('/delete/{filename}', [App\Http\Controllers\Developer\BackupController::class, 'delete'])->name('delete');
            Route::post('/restore/{filename}', [App\Http\Controllers\Developer\BackupController::class, 'restore'])->name('restore');
            Route::post('/cleanup', [App\Http\Controllers\Developer\BackupController::class, 'cleanup'])->name('cleanup');
            Route::get('/system-info', [App\Http\Controllers\Developer\BackupController::class, 'systemInfo'])->name('system-info');
        });
        
        // Role Management
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [App\Http\Controllers\Developer\RoleManagementController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Developer\RoleManagementController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Developer\RoleManagementController::class, 'store'])->name('store');
            // More specific routes first
            Route::get('/{role}/details', [App\Http\Controllers\Developer\RoleManagementController::class, 'getDetails'])->name('details');
            Route::get('/{role}/edit', [App\Http\Controllers\Developer\RoleManagementController::class, 'edit'])->name('edit');
            Route::get('/{role}/clone', [App\Http\Controllers\Developer\RoleManagementController::class, 'clone'])->name('clone');
            Route::post('/{role}/permissions', [App\Http\Controllers\Developer\RoleManagementController::class, 'updatePermissions'])->name('update-permissions');
            Route::put('/{role}', [App\Http\Controllers\Developer\RoleManagementController::class, 'update'])->name('update');
            Route::delete('/{role}', [App\Http\Controllers\Developer\RoleManagementController::class, 'destroy'])->name('destroy');
            // General route last
            Route::get('/{role}', [App\Http\Controllers\Developer\RoleManagementController::class, 'show'])->name('show');
        });
        
        // Tenant Management
        Route::prefix('tenants')->name('tenants.')->group(function () {
            Route::get('/', [App\Http\Controllers\Developer\TenantController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Developer\TenantController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Developer\TenantController::class, 'store'])->name('store');
            Route::get('/{tenant}', [App\Http\Controllers\Developer\TenantController::class, 'show'])->name('show');
            Route::get('/{tenant}/edit', [App\Http\Controllers\Developer\TenantController::class, 'edit'])->name('edit');
            Route::put('/{tenant}', [App\Http\Controllers\Developer\TenantController::class, 'update'])->name('update');
            Route::delete('/{tenant}', [App\Http\Controllers\Developer\TenantController::class, 'destroy'])->name('destroy');
            Route::post('/{tenant}/suspend', [App\Http\Controllers\Developer\TenantController::class, 'suspend'])->name('suspend');
            Route::post('/{tenant}/activate', [App\Http\Controllers\Developer\TenantController::class, 'activate'])->name('activate');
            Route::post('/{tenant}/extend-trial', [App\Http\Controllers\Developer\TenantController::class, 'extendTrial'])->name('extend-trial');
            Route::post('/{tenant}/refresh-usage', [App\Http\Controllers\Developer\TenantController::class, 'refreshUsage'])->name('refresh-usage');
        });
        
        // Subscription Management (Enhanced)
        Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
            Route::get('/', [App\Http\Controllers\Developer\SubscriptionController::class, 'index'])->name('index');
            Route::get('/data', [App\Http\Controllers\Developer\SubscriptionController::class, 'getData'])->name('data');
            Route::get('/{id}', [App\Http\Controllers\Developer\SubscriptionController::class, 'show'])->name('show');
            Route::post('/{id}/suspend', [App\Http\Controllers\Developer\SubscriptionController::class, 'suspend'])->name('suspend');
            Route::post('/{id}/reactivate', [App\Http\Controllers\Developer\SubscriptionController::class, 'reactivate'])->name('reactivate');
            Route::post('/{id}/cancel', [App\Http\Controllers\Developer\SubscriptionController::class, 'cancel'])->name('cancel');
            Route::post('/{id}/change-plan', [App\Http\Controllers\Developer\SubscriptionController::class, 'changePlan'])->name('change-plan');
            Route::post('/{id}/sync-paypal', [App\Http\Controllers\Developer\SubscriptionController::class, 'syncPayPal'])->name('sync-paypal');
            Route::post('/{id}/extend-trial', [App\Http\Controllers\Developer\SubscriptionController::class, 'extendTrial'])->name('extend-trial');
            Route::post('/{id}/mark-orphaned', [App\Http\Controllers\Developer\SubscriptionController::class, 'markAsOrphaned'])->name('mark-orphaned');
            // Legacy support
            Route::get('/data/table', [App\Http\Controllers\Developer\SubscriptionController::class, 'getData'])->name('data.table');
        });
        
        // PayPal Management  
        Route::prefix('paypal')->name('paypal.')->group(function () {
            Route::get('/', [App\Http\Controllers\Developer\PayPalController::class, 'index'])->name('index');
            Route::get('/config', [App\Http\Controllers\Developer\PayPalController::class, 'config'])->name('config');
            Route::post('/config', [App\Http\Controllers\Developer\PayPalController::class, 'updateConfig'])->name('config.update');
            Route::post('/sync-plans', [App\Http\Controllers\Developer\PayPalController::class, 'syncPlans'])->name('sync-plans');
            Route::post('/test-connection', [App\Http\Controllers\Developer\PayPalController::class, 'testConnection'])->name('test-connection');
            Route::get('/webhooks', [App\Http\Controllers\Developer\PayPalController::class, 'webhooks'])->name('webhooks');
            Route::post('/test-webhook', [App\Http\Controllers\Developer\PayPalController::class, 'testWebhook'])->name('test-webhook');
            Route::post('/webhooks/{webhookLog}/retry', [App\Http\Controllers\Developer\PayPalController::class, 'retryWebhook'])->name('webhooks.retry');
            Route::get('/webhooks/export', [App\Http\Controllers\Developer\PayPalController::class, 'exportWebhooks'])->name('webhooks.export');
            Route::get('/webhooks/{webhookLog}/details', [App\Http\Controllers\Developer\PayPalController::class, 'webhookDetails'])->name('webhooks.details');
        });
        
        // Plans Management
        Route::prefix('plans')->name('plans.')->group(function () {
            Route::get('/', [App\Http\Controllers\Developer\PlanManagementController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Developer\PlanManagementController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Developer\PlanManagementController::class, 'store'])->name('store');
            Route::get('/{plan}', [App\Http\Controllers\Developer\PlanManagementController::class, 'show'])->name('show');
            Route::get('/{plan}/edit', [App\Http\Controllers\Developer\PlanManagementController::class, 'edit'])->name('edit');
            Route::put('/{plan}', [App\Http\Controllers\Developer\PlanManagementController::class, 'update'])->name('update');
            Route::delete('/{plan}', [App\Http\Controllers\Developer\PlanManagementController::class, 'destroy'])->name('destroy');
            Route::post('/{plan}/sync-paypal', [App\Http\Controllers\Developer\PlanManagementController::class, 'syncWithPayPal'])->name('sync-paypal');
            Route::post('/{plan}/unsync-paypal', [App\Http\Controllers\Developer\PlanManagementController::class, 'unsyncFromPayPal'])->name('unsync-paypal');
            Route::post('/{plan}/toggle-status', [App\Http\Controllers\Developer\PlanManagementController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{plan}/duplicate', [App\Http\Controllers\Developer\PlanManagementController::class, 'duplicate'])->name('duplicate');
        });
        
        // Billing & Analytics
        Route::prefix('billing')->name('billing.')->group(function () {
            Route::get('/', [App\Http\Controllers\Developer\BillingController::class, 'index'])->name('index');
            Route::get('/analytics', [App\Http\Controllers\Developer\BillingController::class, 'analytics'])->name('analytics');
            Route::get('/mrr-report', [App\Http\Controllers\Developer\BillingController::class, 'mrrReport'])->name('mrr-report');
            Route::get('/churn-analysis', [App\Http\Controllers\Developer\BillingController::class, 'churnAnalysis'])->name('churn-analysis');
            Route::get('/payment-logs', [App\Http\Controllers\Developer\BillingController::class, 'paymentLogs'])->name('payment-logs');
        });
    });

// PayPal Subscription Routes
Route::prefix('paypal')->name('paypal.')->group(function () {
    Route::post('/webhook', [App\Http\Controllers\PayPalController::class, 'webhook'])->name('webhook');
});

// Subscription Routes (Public)
Route::prefix('subscription')->name('subscription.')->group(function () {
    Route::get('/success', [App\Http\Controllers\SubscriptionController::class, 'success'])->name('success');
    Route::get('/cancelled', [App\Http\Controllers\SubscriptionController::class, 'cancelled'])->name('cancelled');
    Route::get('/plans', [App\Http\Controllers\SubscriptionController::class, 'plans'])->name('plans');
    
    // Pre-registration for PayPal flow
    Route::get('/register/{plan}', [App\Http\Controllers\SubscriptionController::class, 'showRegister'])->name('register');
    Route::post('/register', [App\Http\Controllers\SubscriptionController::class, 'storeRegister'])->name('register.store');
    
    // Email validation for pre-registration
    Route::post('/check-email', [App\Http\Controllers\SubscriptionController::class, 'checkEmail'])->name('check-email');
    
    // Registration with trial
    Route::post('/register-trial', [App\Http\Controllers\SubscriptionController::class, 'registerWithTrial'])->name('register-trial');
    
    // Authenticated subscription routes
    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\SubscriptionController::class, 'dashboard'])->name('dashboard');
        Route::post('/upgrade', [App\Http\Controllers\SubscriptionController::class, 'upgrade'])->name('upgrade');
        Route::post('/cancel', [App\Http\Controllers\SubscriptionController::class, 'cancel'])->name('cancel');
    });
});

require __DIR__.'/auth.php';
