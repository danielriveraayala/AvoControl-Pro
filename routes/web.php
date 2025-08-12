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
    
    // Resources
    Route::resource('lots', LotController::class);
    Route::resource('sales', SaleController::class);
    Route::resource('payments', PaymentController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('customers', CustomerController::class);
    
    // Acopio routes
    Route::get('acopio', [AcopioController::class, 'index'])->name('acopio.index');
    Route::get('acopio/{quality}', [AcopioController::class, 'show'])->name('acopio.show');
    Route::get('acopio-reporte', [AcopioController::class, 'reporte'])->name('acopio.reporte');
    
    
    // Lots routes
    Route::get('lots/{lot}/report', [LotController::class, 'report'])->name('lots.report');
    Route::get('lots/{lot}/pdf', [LotController::class, 'downloadPDF'])->name('lots.pdf');
    Route::get('lots/{lot}/payments', [LotController::class, 'payments'])->name('lots.payments');
    Route::post('lots/{lot}/payments', [LotController::class, 'addPayment'])->name('lots.payments.add');
    Route::get('lots/{lot}/payment-timeline', [LotController::class, 'paymentTimeline'])->name('lots.payment-timeline');
    Route::get('lots/{lot}/payment-form', [LotController::class, 'paymentForm'])->name('lots.payment-form');
    Route::get('lots/{lot}/debug-payments', [LotController::class, 'debugPayments'])->name('lots.debug-payments');
    
    // Sales AJAX routes
    Route::group(['prefix' => 'sales'], function() {
        Route::get('{sale}/details', [SaleController::class, 'details'])->name('sales.details');
        Route::get('{sale}/edit-modal', [SaleController::class, 'editModal'])->name('sales.edit-modal');
        Route::post('{sale}/update-modal', [SaleController::class, 'updateModal'])->name('sales.update-modal');
        Route::patch('{sale}/status', [SaleController::class, 'updateStatus'])->name('sales.update-status');
        Route::get('{sale}/payment-form', [SaleController::class, 'paymentForm'])->name('sales.payment-form');
        Route::get('{sale}/payment-timeline', [SaleController::class, 'paymentTimeline'])->name('sales.payment-timeline');
        Route::get('{sale}/invoice', [SaleController::class, 'invoice'])->name('sales.invoice');
        Route::patch('{sale}/deliver', [SaleController::class, 'markDelivered'])->name('sales.deliver');
    });
    
    // Payment routes
    Route::group(['prefix' => 'payments'], function() {
        Route::get('sales/{sale}/create', [PaymentController::class, 'createSalePayment'])->name('payments.sale.create');
        Route::post('sales/{sale}/store', [PaymentController::class, 'storeSalePayment'])->name('payments.sale.store');
        Route::get('lots/{lot}/create', [PaymentController::class, 'createLotPayment'])->name('payments.lot.create');
        Route::post('lots/{lot}/store', [PaymentController::class, 'storeLotPayment'])->name('payments.lot.store');
        Route::get('cash-flow', [PaymentController::class, 'dailyCashFlow'])->name('payments.cash-flow');
        Route::post('sale-payment', [PaymentController::class, 'storeSalePayment'])->name('payments.store-sale-payment');
        Route::post('lot-payment', [PaymentController::class, 'storeLotPayment'])->name('payments.store-lot-payment');
    });
    
    // Configuration routes
    Route::get('configuration', [ConfigurationController::class, 'index'])->name('configuration.index');
    Route::get('configuration/qualities-table', [ConfigurationController::class, 'getQualitiesTable'])->name('configuration.qualities.table');
    Route::post('configuration/quality', [ConfigurationController::class, 'storeQuality'])->name('configuration.quality.store');
    Route::get('configuration/quality/{qualityGrade}', [ConfigurationController::class, 'showQuality'])->name('configuration.quality.show');
    Route::put('configuration/quality/{qualityGrade}', [ConfigurationController::class, 'updateQuality'])->name('configuration.quality.update');
    Route::delete('configuration/quality/{qualityGrade}', [ConfigurationController::class, 'destroyQuality'])->name('configuration.quality.destroy');
    
    // Company configuration routes
    Route::get('configuration/company/get', [ConfigurationController::class, 'getCompanyConfig'])->name('configuration.company.get');
    Route::post('configuration/company/store', [ConfigurationController::class, 'storeCompanyConfig'])->name('configuration.company.store');
    
    // Email configuration routes
    Route::get('configuration/email/get', [ConfigurationController::class, 'getEmailConfig'])->name('configuration.email.get');
    Route::post('configuration/email/store', [ConfigurationController::class, 'storeEmailConfig'])->name('configuration.email.store');
    Route::post('configuration/email/test', [ConfigurationController::class, 'testEmailConfig'])->name('configuration.email.test');
    
    // Profile routes
    Route::prefix('perfil')->name('profile.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ProfileController::class, 'index'])->name('index');
        Route::post('/update', [\App\Http\Controllers\ProfileController::class, 'update'])->name('update');
        Route::post('/settings', [\App\Http\Controllers\ProfileController::class, 'updateSettings'])->name('settings');
        Route::post('/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('password');
    });
    
    // Reports routes
    Route::prefix('reportes')->name('reports.')->group(function () {
        Route::get('rentabilidad', [ReportController::class, 'profitability'])->name('profitability');
        Route::get('rentabilidad-lotes', [ReportController::class, 'lotProfitability'])->name('lot-profitability');
        Route::get('analisis-clientes', [ReportController::class, 'customerAnalysis'])->name('customer-analysis');
        Route::get('analisis-proveedores', [ReportController::class, 'supplierAnalysis'])->name('supplier-analysis');
    });
});

require __DIR__.'/auth.php';
