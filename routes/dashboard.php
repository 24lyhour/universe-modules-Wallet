<?php

use Illuminate\Support\Facades\Route;
use Modules\Wallets\Http\Controllers\Dashboard\V1\TopUpController;
use Modules\Wallets\Http\Controllers\Dashboard\V1\TransactionController;
use Modules\Wallets\Http\Controllers\Dashboard\V1\WalletController;
use Modules\Wallets\Http\Controllers\Dashboard\V1\WalletSettingsController;

Route::middleware(['auth', 'verified', 'auto.permission'])
    ->prefix('dashboard')
    ->group(function () {
        // Wallet Settings (under global settings)
        Route::prefix('settings')->group(function () {
            Route::get('wallet', [WalletSettingsController::class, 'index'])->name('settings.wallet');
            Route::patch('wallet', [WalletSettingsController::class, 'update'])->name('settings.wallet.update');
        });

        Route::resource('wallets', WalletController::class)->names('wallets');
        Route::get('wallets/{wallet}/delete', [WalletController::class, 'confirmDelete'])->name('wallets.delete');

        // Global transactions route (all transactions across all wallets)
        Route::get('transactions', [TransactionController::class, 'all'])->name('transactions.index');

        // Status change routes
        Route::patch('wallets/{wallet}/activate', [WalletController::class, 'activate'])->name('wallets.activate');
        Route::patch('wallets/{wallet}/deactivate', [WalletController::class, 'deactivate'])->name('wallets.deactivate');
        Route::patch('wallets/{wallet}/suspend', [WalletController::class, 'suspend'])->name('wallets.suspend');
        Route::patch('wallets/{wallet}/unsuspend', [WalletController::class, 'unsuspend'])->name('wallets.unsuspend');
        Route::patch('wallets/{wallet}/status', [WalletController::class, 'changeStatus'])->name('wallets.change-status');

        // Top-up routes (must be before resource to avoid /{topup} collision)
        Route::prefix('topups')->name('topups.')->group(function () {
            Route::get('/', [TopUpController::class, 'index'])->name('index');
            Route::get('/create', [TopUpController::class, 'create'])->name('create');
            Route::post('/', [TopUpController::class, 'store'])->name('store');
            Route::get('/{topup}', [TopUpController::class, 'show'])->name('show');
            Route::get('/{topup}/delete', [TopUpController::class, 'confirmDelete'])->name('delete');
            Route::delete('/{topup}', [TopUpController::class, 'destroy'])->name('destroy');
            Route::patch('/{topup}/complete', [TopUpController::class, 'complete'])->name('complete');
            Route::patch('/{topup}/cancel', [TopUpController::class, 'cancel'])->name('cancel');
            Route::patch('/{topup}/fail', [TopUpController::class, 'markFailed'])->name('fail');
        });

        // Transaction routes
        Route::prefix('wallets/{wallet}/transactions')->name('wallets.transactions.')->group(function () {
            Route::get('/', [TransactionController::class, 'index'])->name('index');
            Route::get('/{transaction}', [TransactionController::class, 'show'])->name('show');

            // Deposit
            Route::get('/deposit/create', [TransactionController::class, 'createDeposit'])->name('deposit.create');
            Route::post('/deposit', [TransactionController::class, 'deposit'])->name('deposit');

            // Withdraw
            Route::get('/withdraw/create', [TransactionController::class, 'createWithdraw'])->name('withdraw.create');
            Route::post('/withdraw', [TransactionController::class, 'withdraw'])->name('withdraw');

            // Transfer
            Route::get('/transfer/create', [TransactionController::class, 'createTransfer'])->name('transfer.create');
            Route::post('/transfer', [TransactionController::class, 'transfer'])->name('transfer');

            // Transaction actions
            Route::post('/{transaction}/reverse', [TransactionController::class, 'reverse'])->name('reverse');
            Route::post('/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('cancel');
        });
    });
