<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LotController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ConfigurationController;

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
});

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Resources
    Route::resource('lots', LotController::class);
    Route::resource('sales', SaleController::class);
    Route::resource('payments', PaymentController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('customers', CustomerController::class);
    
    // Special routes for lots
    Route::get('lots/{lot}/report', [LotController::class, 'report'])->name('lots.report');
    Route::get('lots/{lot}/pdf', [LotController::class, 'downloadPDF'])->name('lots.pdf');
    
    // Special routes for payments
    Route::get('sales/{sale}/payment', [PaymentController::class, 'createSalePayment'])->name('payments.sale.create');
    Route::post('sales/{sale}/payment', [PaymentController::class, 'storeSalePayment'])->name('payments.sale.store');
    Route::get('lots/{lot}/payment', [PaymentController::class, 'createLotPayment'])->name('payments.lot.create');
    Route::post('lots/{lot}/payment', [PaymentController::class, 'storeLotPayment'])->name('payments.lot.store');
    Route::get('payments/cash-flow', [PaymentController::class, 'dailyCashFlow'])->name('payments.cash-flow');
    
    // Special route for marking sales as delivered
    Route::patch('sales/{sale}/deliver', [SaleController::class, 'markDelivered'])->name('sales.deliver');
    
    // Configuration routes
    Route::get('configuration', [ConfigurationController::class, 'index'])->name('configuration.index');
    Route::get('configuration/qualities-table', [ConfigurationController::class, 'getQualitiesTable'])->name('configuration.qualities.table');
    Route::post('configuration/quality', [ConfigurationController::class, 'storeQuality'])->name('configuration.quality.store');
    Route::get('configuration/quality/{qualityGrade}', [ConfigurationController::class, 'showQuality'])->name('configuration.quality.show');
    Route::put('configuration/quality/{qualityGrade}', [ConfigurationController::class, 'updateQuality'])->name('configuration.quality.update');
    Route::delete('configuration/quality/{qualityGrade}', [ConfigurationController::class, 'destroyQuality'])->name('configuration.quality.destroy');
});

require __DIR__.'/auth.php';
