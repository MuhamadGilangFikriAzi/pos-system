<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pos\DashboardController;
use App\Http\Controllers\Pos\PosController;
use App\Http\Controllers\Pos\ProductController;
use App\Http\Controllers\Pos\CategoryController;
use App\Http\Controllers\Pos\ReportController;
use App\Http\Controllers\Pos\StockController;
use App\Http\Controllers\Pos\ShiftController;
use App\Http\Controllers\Pos\KasirDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Inventory\InventoryDashboardController;
use App\Http\Controllers\Inventory\WarehouseController;
use App\Http\Controllers\Inventory\SupplierController;
use App\Http\Controllers\Inventory\PurchaseOrderController;
use App\Http\Controllers\Inventory\StockMutationController;
use App\Http\Controllers\Inventory\StockOpnameController;
use App\Http\Controllers\Inventory\ProductVariantController;

Route::get('/', function () {
    return redirect()->route('pos.dashboard');
});

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);
});

Route::post('/logout', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::middleware(['auth', 'track.activity'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('pos.dashboard');

    // ===== SHIFT =====
    Route::get('/shift/open', [ShiftController::class, 'openForm'])->name('pos.shift.open');
    Route::post('/shift/open', [ShiftController::class, 'openStore'])->name('pos.shift.open.store');
    Route::get('/shift/close', [ShiftController::class, 'closeForm'])->name('pos.shift.close');
    Route::post('/shift/close', [ShiftController::class, 'closeStore'])->name('pos.shift.close.store');
    Route::get('/shift/history', [ShiftController::class, 'history'])->name('pos.shift.history');
    Route::get('/shift/{id}', [ShiftController::class, 'show'])->name('pos.shift.show');

    // ===== KASIR DASHBOARD =====
    Route::get('/kasir/dashboard', [KasirDashboardController::class, 'index'])->name('pos.kasir.dashboard');
    Route::get('/kasir/transactions', [KasirDashboardController::class, 'transactions'])->name('pos.kasir.transactions');
    Route::get('/kasir/activity', [KasirDashboardController::class, 'activity'])->name('pos.kasir.activity');
    Route::get('/kasir/export', [KasirDashboardController::class, 'export'])->name('pos.kasir.export');

    // ===== POS KASIR (wajib shift untuk kasir) =====
    Route::middleware(['check.shift'])->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('/pos/checkout', [PosController::class, 'store'])->name('pos.store');
        Route::get('/pos/products', [PosController::class, 'products'])->name('pos.products');
        Route::get('/pos/receipt/{transaction}', [PosController::class, 'receipt'])->name('pos.receipt');
        Route::post('/pos/calculate', [PosController::class, 'calculate'])->name('pos.calculate');
    });

    // ===== PRODUCTS & CATEGORIES =====
    Route::resource('products', ProductController::class);
    Route::resource('categories', CategoryController::class);

    // ===== STOCK =====
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/stock/create', [StockController::class, 'create'])->name('stock.create');
    Route::post('/stock', [StockController::class, 'store'])->name('stock.store');
    Route::get('/stock/{product}', [StockController::class, 'show'])->name('stock.show');
    Route::get('/stock/product/{product}', [StockController::class, 'getProduct'])->name('stock.product');

    // ===== REPORTS =====
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // ===== ADMIN USERS (admin + supervisor) =====
    Route::middleware('role:admin,supervisor')->group(function () {
        Route::get('/kasir/all-stats', [DashboardController::class, 'allKasirStats'])->name('pos.kasir.stats');
        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/users/create', [UserController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    });

    // ========================================================================
    // INVENTORY MANAGEMENT
    // ========================================================================
    Route::prefix('inventory')->name('inventory.')->group(function () {

        // Dashboard
        Route::get('/', [InventoryDashboardController::class, 'index'])->name('dashboard');

        // Warehouses
        Route::resource('warehouses', WarehouseController::class);

        // Suppliers
        Route::resource('suppliers', SupplierController::class);

        // Purchase Orders
        Route::get('purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
        Route::get('purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
        Route::post('purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
        Route::get('purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
        Route::get('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
        Route::post('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receiveStore'])->name('purchase-orders.receive-store');
        Route::post('purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
        Route::delete('purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'destroy'])->name('purchase-orders.destroy');

        // AJAX: get product info
        Route::get('purchase-orders/product/{product}', [PurchaseOrderController::class, 'getProduct'])->name('purchase-orders.product');

        // Stock Mutations
        Route::get('stock', [StockMutationController::class, 'index'])->name('stock.index');
        Route::get('stock/in', [StockMutationController::class, 'createIn'])->name('stock.in');
        Route::post('stock/in', [StockMutationController::class, 'storeIn'])->name('stock.in.store');
        Route::get('stock/out', [StockMutationController::class, 'createOut'])->name('stock.out');
        Route::post('stock/out', [StockMutationController::class, 'storeOut'])->name('stock.out.store');
        Route::get('stock/adjust', [StockMutationController::class, 'createAdjustment'])->name('stock.adjust');
        Route::post('stock/adjust', [StockMutationController::class, 'storeAdjustment'])->name('stock.adjust.store');
        Route::get('stock/transfer', [StockMutationController::class, 'createTransfer'])->name('stock.transfer');
        Route::post('stock/transfer', [StockMutationController::class, 'storeTransfer'])->name('stock.transfer.store');
        Route::get('stock/export', [StockMutationController::class, 'export'])->name('stock.export');
        Route::get('stock/{stockMutation}', [StockMutationController::class, 'show'])->name('stock.show');
        Route::get('stock/product/{product}', [StockMutationController::class, 'getProductStock'])->name('stock.product');
        Route::get('stock/chart', [StockMutationController::class, 'chart'])->name('stock.chart');

        // Stock Opname
        Route::get('opname', [StockOpnameController::class, 'index'])->name('opname.index');
        Route::get('opname/create', [StockOpnameController::class, 'create'])->name('opname.create');
        Route::post('opname', [StockOpnameController::class, 'store'])->name('opname.store');
        Route::get('opname/{stockOpname}', [StockOpnameController::class, 'show'])->name('opname.show');
        Route::get('opname/{stockOpname}/edit', [StockOpnameController::class, 'edit'])->name('opname.edit');
        Route::put('opname/{stockOpname}', [StockOpnameController::class, 'update'])->name('opname.update');
        Route::post('opname/{stockOpname}/complete', [StockOpnameController::class, 'complete'])->name('opname.complete');
        Route::post('opname/{stockOpname}/cancel', [StockOpnameController::class, 'cancel'])->name('opname.cancel');

        // Product Variants (nested under product)
        Route::get('variants/product/{product}', [ProductVariantController::class, 'index'])->name('variants.index');
        Route::post('variants', [ProductVariantController::class, 'store'])->name('variants.store');
        Route::put('variants/{productVariant}', [ProductVariantController::class, 'update'])->name('variants.update');
        Route::delete('variants/{productVariant}', [ProductVariantController::class, 'destroy'])->name('variants.destroy');
    });

});
