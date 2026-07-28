<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ProtocolController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\CatalogController;

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Público
    |--------------------------------------------------------------------------
    */

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/zones', [CatalogController::class, 'zones']);
    Route::get('/branches', [CatalogController::class, 'branches']);
    Route::get('/warehouses', [CatalogController::class, 'warehouses']);

    /*
    |--------------------------------------------------------------------------
    | Protegido
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/profile', [ProfileController::class, 'show']);

        Route::post('/welcome/complete', [AuthController::class, 'completeWelcome']);

        // Route::apiResource('protocols', ProtocolController::class);
        // Route::apiResource('products', ProductController::class);

    });
});
