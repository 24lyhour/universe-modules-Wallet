<?php

use Illuminate\Support\Facades\Route;
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
        Route::post('/top-up', [WalletController::class, 'topUp'])->name('top-up');
        Route::post('/pay', [WalletController::class, 'pay'])->name('pay');
        Route::post('/transfer', [WalletController::class, 'transfer'])->name('transfer');
    });
});
