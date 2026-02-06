<?php

use Illuminate\Support\Facades\Route;
use Modules\Wallets\Http\Controllers\WalletsController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('wallets', WalletsController::class)->names('wallets');
});
