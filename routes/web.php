<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CropController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProblemController;




Route::middleware('guest')->group(function () {

    Route::get('/', [LoginController::class, 'index'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'login'])
        ->name('login.store');
});

Route::middleware(['auth', 'admin', 'nocache'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::resource('users', UserController::class);

    Route::resource('warehouses', WarehouseController::class);
    Route::resource('zones', ZoneController::class);
    Route::resource('branches', BranchController::class);
    Route::resource('crops', CropController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('brands', BrandController::class);
    Route::resource('products', ProductController::class);
    Route::resource('problems', ProblemController::class);
    Route::post('/logout', [LoginController::class, 'logout'])
        ->name('logout');
});
