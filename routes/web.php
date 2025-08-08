<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LotController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;

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
    
    // Special routes for payments
    Route::get('sales/{sale}/payment', [PaymentController::class, 'createSalePayment'])->name('payments.sale.create');
    Route::post('sales/{sale}/payment', [PaymentController::class, 'storeSalePayment'])->name('payments.sale.store');
    Route::get('lots/{lot}/payment', [PaymentController::class, 'createLotPayment'])->name('payments.lot.create');
    Route::post('lots/{lot}/payment', [PaymentController::class, 'storeLotPayment'])->name('payments.lot.store');
    Route::get('payments/cash-flow', [PaymentController::class, 'dailyCashFlow'])->name('payments.cash-flow');
    
    // Special route for marking sales as delivered
    Route::patch('sales/{sale}/deliver', [SaleController::class, 'markDelivered'])->name('sales.deliver');
});

require __DIR__.'/auth.php';
