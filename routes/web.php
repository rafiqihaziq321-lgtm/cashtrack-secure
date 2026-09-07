<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth', 'role:Owner'])->group(function () {
    Route::get('/owner/dashboard', [DashboardController::class, 'owner'])
        ->name('owner.dashboard');
});

Route::middleware(['auth', 'role:Cashier'])->group(function () {
    Route::get('/cashier/dashboard', [DashboardController::class, 'cashier'])
        ->name('cashier.dashboard');
});

require __DIR__.'/settings.php';
