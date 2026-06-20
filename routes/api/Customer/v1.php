<?php

use Illuminate\Support\Facades\Route;
use Modules\Wallets\Http\Controllers\Api\V1\TopUpController;
use Modules\Wallets\Http\Controllers\Api\V1\WalletController;

/*
|--------------------------------------------------------------------------
| Customer Wallet API Routes (v1)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->prefix('v1/customer')->group(function () {

    // ==================== WALLET ====================
    Route::group(['prefix' => 'wallet', 'as' => 'wallets.'], function () {
        Route::get('/', [WalletController::class, 'show'])->name('show');
        Route::get('/balance', [WalletController::class, 'balance'])->name('balance');
    });

    // ==================== TRANSACTIONS ====================
    Route::group(['prefix' => 'wallet/transactions', 'as' => 'wallets.transactions.'], function () {
        Route::get('/', [WalletController::class, 'transactions'])->name('index');
    });

    // ==================== OPERATIONS ====================
    Route::group(['prefix' => 'wallet', 'as' => 'wallets.'], function () {
        Route::post('/pay', [WalletController::class, 'pay'])->name('pay');
        Route::post('/transfer', [WalletController::class, 'transfer'])->name('transfer');
    });

    // ==================== TOP-UPS ====================
    Route::group(['prefix' => 'wallet/topups', 'as' => 'wallets.topups.'], function () {
        Route::get('/', [TopUpController::class, 'index'])->name('index');
        Route::post('/', [TopUpController::class, 'store'])->name('store');
        Route::get('/{reference}', [TopUpController::class, 'show'])->name('show');
    });
});
