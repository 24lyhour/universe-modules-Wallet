<?php

use Illuminate\Support\Facades\Route;
use Modules\Wallets\Http\Controllers\Dashboard\V1\WalletController;

Route::middleware(['auth', 'verified'])
    ->prefix('dashboard')
    ->group(function () {
        Route::resource('wallets', WalletController::class)->names('wallets');
        Route::get('wallets/{wallet}/delete', [WalletController::class, 'confirmDelete'])->name('wallets.delete');
    });
