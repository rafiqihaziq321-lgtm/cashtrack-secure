<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth', 'role:Owner'])->group(function () {
    Route::get('/owner/dashboard', [DashboardController::class, 'owner'])
        ->name('owner.dashboard');

    Route::get('/owner/products', [ProductController::class, 'index'])
        ->name('owner.products.index');

    Route::get('/owner/products/create', [ProductController::class, 'create'])
        ->name('owner.products.create');

    Route::post('/owner/products', [ProductController::class, 'store'])
        ->name('owner.products.store');

    Route::get('/owner/products/{product}/edit', [ProductController::class, 'edit'])
        ->name('owner.products.edit');

    Route::put('/owner/products/{product}', [ProductController::class, 'update'])
        ->name('owner.products.update');

    Route::patch('/owner/products/{product}/deactivate', [ProductController::class, 'deactivate'])
        ->name('owner.products.deactivate');
});

Route::middleware(['auth', 'role:Cashier'])->group(function () {
    Route::get('/cashier/dashboard', [DashboardController::class, 'cashier'])
        ->name('cashier.dashboard');

    Route::get('/cashier/sales/create', [SaleController::class, 'create'])
        ->name('cashier.sales.create');

    Route::post('/cashier/sales', [SaleController::class, 'store'])
        ->name('cashier.sales.store');
});

require __DIR__.'/settings.php';
