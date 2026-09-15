<?php

use App\Http\Controllers\PlanBController as PlanB;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::middleware('auth')->get('/dashboard', function () {
    return match (request()->user()->role?->name) {
        'Owner' => redirect()->route('owner.dashboard'),
        'Cashier' => redirect()->route('cashier.dashboard'),
        default => abort(403, 'Peranan akaun belum diberikan.'),
    };
})->name('dashboard');

Route::middleware(['auth', 'role:Owner'])->group(function (): void {
    Route::get('/owner/dashboard', [PlanB::class, 'dashboard'])->name('owner.dashboard');
    Route::get('/owner/products', [PlanB::class, 'products'])->name('owner.products.index');
    Route::get('/owner/products/create', [PlanB::class, 'productForm'])->name('owner.products.create');
    Route::post('/owner/products', [PlanB::class, 'saveProduct'])->name('owner.products.store');
    Route::get('/owner/products/{product}/edit', [PlanB::class, 'productForm'])->name('owner.products.edit');
    Route::put('/owner/products/{product}', [PlanB::class, 'saveProduct'])->name('owner.products.update');
    Route::get('/owner/stock-in/create', [PlanB::class, 'receiptForm'])->name('owner.stock-in.create');
    Route::post('/owner/stock-in', [PlanB::class, 'receive'])->name('owner.stock-in.store');
    Route::get('/owner/receipts/{id}', [PlanB::class, 'receipt'])->name('owner.receipts.show');
    Route::get('/owner/stock-counts', [PlanB::class, 'counts'])->name('owner.stock-counts.index');
    Route::get('/owner/stock-counts/create', [PlanB::class, 'countForm'])->name('owner.stock-counts.create');
    Route::post('/owner/stock-counts', [PlanB::class, 'count'])->name('owner.stock-counts.store');
    Route::get('/owner/alerts', [PlanB::class, 'alerts'])->name('owner.alerts.index');
    Route::get('/owner/alerts/{alert}', [PlanB::class, 'alert'])->name('owner.alerts.show');
    Route::post('/owner/alerts/{alert}', [PlanB::class, 'review'])->name('owner.alerts.review');
    Route::get('/owner/audit-trail', [PlanB::class, 'audit'])->name('owner.audit.index');
    Route::get('/owner/reports', [PlanB::class, 'report'])->name('owner.reports.index');
    Route::get('/owner/users', [PlanB::class, 'users'])->name('owner.users.index');
    Route::get('/owner/users/create', [PlanB::class, 'userForm'])->name('owner.users.create');
    Route::post('/owner/users', [PlanB::class, 'saveUser'])->name('owner.users.store');
    Route::get('/owner/users/{user}/edit', [PlanB::class, 'userForm'])->name('owner.users.edit');
    Route::put('/owner/users/{user}', [PlanB::class, 'saveUser'])->name('owner.users.update');
});
Route::middleware(['auth', 'role:Cashier'])->get('/cashier/dashboard', [PlanB::class, 'dashboard'])->name('cashier.dashboard');
Route::middleware(['auth', 'role:Owner,Cashier'])->group(function (): void {
    Route::get('/cashier/sales/create', [PlanB::class, 'saleForm'])->name('cashier.sales.create');
    Route::post('/cashier/sales', [PlanB::class, 'sell'])->name('cashier.sales.store');
    Route::get('/sales/{sale}', [PlanB::class, 'sale'])->name('sales.show');
    Route::get('/withdrawals/create', [PlanB::class, 'withdrawalForm'])->name('withdrawals.create');
    Route::post('/withdrawals', [PlanB::class, 'withdraw'])->name('withdrawals.store');
});
require __DIR__.'/settings.php';
