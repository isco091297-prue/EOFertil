<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ProtocolController;
use App\Http\Controllers\Api\V1\ProductController;

Route::prefix('v1')->group(function () {

    // Autenticación
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    // Rutas protegidas
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/profile', [ProfileController::class, 'show']);

        //  Route::apiResource('protocols', ProtocolController::class);

        //Route::apiResource('products', ProductController::class);
        Route::post('/welcome/complete', [AuthController::class, 'completeWelcome']);
    });
});
