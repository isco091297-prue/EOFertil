<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ProtocolController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\GuideController;
use App\Http\Controllers\Api\V1\CashbackController;

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

        /*
        |--------------------------------------------------------------------------
        | Autenticación
        |--------------------------------------------------------------------------
        */

        Route::post('/logout', [AuthController::class, 'logout']);

        /*
        |--------------------------------------------------------------------------
        | Perfil
        |--------------------------------------------------------------------------
        */

        Route::get('/profile', [ProfileController::class, 'show']);

        /*
        |--------------------------------------------------------------------------
        | Guía Técnica
        |--------------------------------------------------------------------------
        */

        Route::get('/guide/crops', [GuideController::class, 'crops']);

        Route::get('/guide/problems', [GuideController::class, 'problems']);

        Route::get('/guide/protocol', [GuideController::class, 'protocol']);

        /*
        |--------------------------------------------------------------------------
        | Cashback
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/cashback/current-campaign',
            [CashbackController::class, 'currentCampaign']
        );

        Route::get(
            '/cashback/balance',
            [CashbackController::class, 'balance']
        );

        Route::get(
            '/cashback/history',
            [CashbackController::class, 'history']
        );

        Route::get(
            '/cashback/invoices/{invoice}',
            [CashbackController::class, 'showInvoice']
        );

        Route::post(
            '/cashback/invoices',
            [CashbackController::class, 'storeInvoice']
        );

        /*
        |--------------------------------------------------------------------------
        | Futuras APIs
        |--------------------------------------------------------------------------
        */

        // Route::apiResource('protocols', ProtocolController::class);
        Route::apiResource('products', ProductController::class);

   } );
});
