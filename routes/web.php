<?php

use Illuminate\Support\Facades\Route;
use Modules\Wallets\Http\Controllers\WalletsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('wallets', WalletsController::class)->names('wallets');
});
