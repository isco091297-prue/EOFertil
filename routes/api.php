<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\GuideController;
use App\Http\Controllers\Api\V1\CashbackController;
use App\Http\Controllers\Api\V1\ProductController;

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Público
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/login',
        [AuthController::class, 'login']
    );

    Route::post(
        '/register',
        [AuthController::class, 'register']
    );

    /*
    |--------------------------------------------------------------------------
    | Catálogos públicos
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/zones',
        [CatalogController::class, 'zones']
    );

    Route::get(
        '/branches',
        [CatalogController::class, 'branches']
    );

    Route::get(
        '/warehouses',
        [CatalogController::class, 'warehouses']
    );

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

        Route::post(
            '/logout',
            [AuthController::class, 'logout']
        );

        /*
        |--------------------------------------------------------------------------
        | Perfil
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/profile',
            [ProfileController::class, 'show']
        );

        /*
        |--------------------------------------------------------------------------
        | Guía Técnica
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/guide/crops',
            [GuideController::class, 'crops']
        );

        Route::get(
            '/guide/problems',
            [GuideController::class, 'problems']
        );

        Route::get(
            '/guide/protocol',
            [GuideController::class, 'protocol']
        );

        /*
        |--------------------------------------------------------------------------
        | Productos
        |--------------------------------------------------------------------------
        |
        | La aplicación móvil solamente necesita consultar los productos.
        | La creación, edición y eliminación continúa en el panel web.
        |
        */

        Route::get(
            '/products',
            [ProductController::class, 'index']
        );

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
            '/cashback/ranking',
            [CashbackController::class, 'ranking']
        );
        Route::get(
            '/ranking-accumulated',
            [CashbackController::class, 'accumulatedRanking']
        );
        Route::get(
            '/cashback/invoices/{invoice}',
            [CashbackController::class, 'showInvoice']
        );

        Route::post(
            '/cashback/invoices',
            [CashbackController::class, 'storeInvoice']
        );
        Route::post(
            '/cashback/redeem',
            [CashbackController::class, 'redeem']
        );
        /*
        |--------------------------------------------------------------------------
        | Futuras APIs
        |--------------------------------------------------------------------------
        */

        // Route::apiResource('protocols', ProtocolController::class);
    });
});
