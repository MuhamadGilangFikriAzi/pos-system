<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pos\DashboardController;
use App\Http\Controllers\Pos\PosController;
use App\Http\Controllers\Pos\ProductController;
use App\Http\Controllers\Pos\CategoryController;
use App\Http\Controllers\Pos\ReportController;

Route::get('/', function () {
    return redirect()->route('pos.dashboard');
});

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);
});

Route::post('/logout', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('pos.dashboard');

    // POS Kasir
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/checkout', [PosController::class, 'store'])->name('pos.store');
    Route::get('/pos/products', [PosController::class, 'products'])->name('pos.products');
    Route::get('/pos/receipt/{transaction}', [PosController::class, 'receipt'])->name('pos.receipt');

    // Products
    Route::resource('products', ProductController::class);

    // Categories
    Route::resource('categories', CategoryController::class);

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});
