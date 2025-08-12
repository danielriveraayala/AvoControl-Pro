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

Route::get('/', function () {
    return redirect('/dashboard');
})->name('home');

// Ping route for connectivity checks
Route::get('/ping', function () {
    return response()->json(['status' => 'ok']);
});


// Public push notification endpoint (needed before auth)
Route::get('/push/vapid-key', [App\Http\Controllers\PushNotificationController::class, 'getVapidKey']);

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
    
    // Configuration routes - protected with manage company config permission
    Route::prefix('configuration')->middleware('permission:manage_company_config')->group(function () {
        Route::get('/', [ConfigurationController::class, 'index'])->name('configuration.index');
        Route::get('/qualities-table', [ConfigurationController::class, 'getQualitiesTable'])->name('configuration.qualities.table');
        Route::post('/quality', [ConfigurationController::class, 'storeQuality'])->name('configuration.quality.store');
        Route::get('/quality/{qualityGrade}', [ConfigurationController::class, 'showQuality'])->name('configuration.quality.show');
        Route::put('/quality/{qualityGrade}', [ConfigurationController::class, 'updateQuality'])->name('configuration.quality.update');
        Route::delete('/quality/{qualityGrade}', [ConfigurationController::class, 'destroyQuality'])->name('configuration.quality.destroy');
        
        // Company configuration routes
        Route::get('/company/get', [ConfigurationController::class, 'getCompanyConfig'])->name('configuration.company.get');
        Route::post('/company/store', [ConfigurationController::class, 'storeCompanyConfig'])->name('configuration.company.store');
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
    });

require __DIR__.'/auth.php';
