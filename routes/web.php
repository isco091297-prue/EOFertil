<?php

use App\Http\Controllers\ActiveIngredientController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CashbackCampaignController;
use App\Http\Controllers\CashbackCampaignParticipantController;
use App\Http\Controllers\CashbackCampaignRankingController;
use App\Http\Controllers\CashbackCampaignWinnerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CropController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProblemController;
use App\Http\Controllers\ProtocolController;
use App\Http\Controllers\RankingRewardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\ZoneController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {

    Route::get('/', [
        LoginController::class,
        'index',
    ])->name('login');

    Route::post('/login', [
        LoginController::class,
        'login',
    ])->name('login.store');
});

Route::middleware([
    'auth',
    'admin',
    'nocache',
])->group(function () {

    Route::get('/dashboard', [
        DashboardController::class,
        'index',
    ])->name('dashboard');

    Route::resource(
        'cashback-campaigns',
        CashbackCampaignController::class
    );

    Route::prefix(
        'cashback-campaigns/{cashbackCampaign}'
    )->group(function () {


        Route::get(
            'participants',
            [
                CashbackCampaignParticipantController::class,
                'index',
            ]
        )->name(
            'cashback-campaigns.participants'
        );

        Route::post(
            'participants',
            [
                CashbackCampaignParticipantController::class,
                'store',
            ]
        )->name(
            'cashback-campaigns.participants.store'
        );

        Route::get(
            'ranking',
            [
                CashbackCampaignRankingController::class,
                'index',
            ]
        )->name(
            'cashback-campaigns.ranking'
        );

        Route::get(
            'winners',
            [
                CashbackCampaignWinnerController::class,
                'index',
            ]
        )->name(
            'cashback-campaigns.winners'
        );

        Route::resource(
            'ranking-rewards',
            RankingRewardController::class
        )->except([
            'show',
        ]);
    });

    Route::resource(
        'users',
        UserController::class
    );

    Route::patch(
        'users/{user}/approve',
        [
            UserController::class,
            'approve',
        ]
    )->name('users.approve');

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

    Route::get(
        'protocols/crops/search',
        [
            ProtocolController::class,
            'searchCrops',
        ]
    )->name(
        'protocols.crops.search'
    );

    Route::get(
        'protocols/problems/search',
        [
            ProtocolController::class,
            'searchProblems',
        ]
    )->name(
        'protocols.problems.search'
    );

    Route::get(
        'protocols/products/search',
        [
            ProtocolController::class,
            'searchProducts',
        ]
    )->name(
        'protocols.products.search'
    );

    Route::get(
        'protocols/active-ingredients/search',
        [
            ProtocolController::class,
            'searchActiveIngredients',
        ]
    )->name(
        'protocols.active-ingredients.search'
    );

    Route::get(
        'protocols/active-ingredients/{activeIngredient}/products',
        [
            ProtocolController::class,
            'activeIngredientProducts',
        ]
    )->name(
        'protocols.active-ingredients.products'
    );

    Route::resource(
        'protocols',
        ProtocolController::class
    );

    Route::post(
        '/logout',
        [
            LoginController::class,
            'logout',
        ]
    )->name('logout');
});
