<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CropController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProblemController;
use App\Http\Controllers\ProtocolController;
use App\Http\Controllers\CashbackCampaignController;
use App\Http\Controllers\RankingRewardController;
use App\Http\Controllers\ActiveIngredientController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Invitados
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get(
        '/',
        [LoginController::class, 'index']
    )->name('login');

    Route::post(
        '/login',
        [LoginController::class, 'login']
    )->name('login.store');
});

/*
|--------------------------------------------------------------------------
| Administración
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'admin',
    'nocache',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    )->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Incentivos
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'cashback-campaigns',
        CashbackCampaignController::class
    );

    Route::resource(
        'ranking-rewards',
        RankingRewardController::class
    );

    /*
    |--------------------------------------------------------------------------
    | Usuarios
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'users',
        UserController::class
    );

    Route::patch(
        'users/{user}/approve',
        [UserController::class, 'approve']
    )->name('users.approve');

    /*
    |--------------------------------------------------------------------------
    | Organización
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'warehouses',
        WarehouseController::class
    );

    Route::resource(
        'zones',
        ZoneController::class
    );

    Route::resource(
        'branches',
        BranchController::class
    );

    /*
    |--------------------------------------------------------------------------
    | Catálogo técnico
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'crops',
        CropController::class
    );

    Route::resource(
        'categories',
        CategoryController::class
    );

    Route::resource(
        'brands',
        BrandController::class
    );

    Route::resource(
        'products',
        ProductController::class
    );

    Route::resource(
        'active-ingredients',
        ActiveIngredientController::class
    );

    Route::resource(
        'problems',
        ProblemController::class
    );

    /*
    |--------------------------------------------------------------------------
    | Búsquedas para Protocolos
    |--------------------------------------------------------------------------
    */

    Route::get(
        'protocols/crops/search',
        [ProtocolController::class, 'searchCrops']
    )->name('protocols.crops.search');

    Route::get(
        'protocols/problems/search',
        [ProtocolController::class, 'searchProblems']
    )->name('protocols.problems.search');

    /*
    |--------------------------------------------------------------------------
    | Productos EOFertil
    |--------------------------------------------------------------------------
    */

    Route::get(
        'protocols/products/search',
        [ProtocolController::class, 'searchProducts']
    )->name('protocols.products.search');

    /*
    |--------------------------------------------------------------------------
    | Ingredientes activos
    |--------------------------------------------------------------------------
    */

    Route::get(
        'protocols/active-ingredients/search',
        [ProtocolController::class, 'searchActiveIngredients']
    )->name('protocols.active-ingredients.search');

    /*
    |--------------------------------------------------------------------------
    | Productos vinculados a un ingrediente activo
    |--------------------------------------------------------------------------
    */

    Route::get(
        'protocols/active-ingredients/{activeIngredient}/products',
        [ProtocolController::class, 'activeIngredientProducts']
    )->name('protocols.active-ingredients.products');

    /*
    |--------------------------------------------------------------------------
    | Protocolos
    |--------------------------------------------------------------------------
    |
    | IMPORTANTE:
    | El resource debe estar DESPUÉS de las rutas especiales anteriores.
    |
    */

    Route::resource(
        'protocols',
        ProtocolController::class
    );

    /*
    |--------------------------------------------------------------------------
    | Cerrar sesión
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/logout',
        [LoginController::class, 'logout']
    )->name('logout');
});
