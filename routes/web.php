<?php

use App\Http\Controllers\Admin\AttributeDefinitionController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ConfirmPasswordController;
use App\Http\Controllers\Admin\ItemBarcodeController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Admin\StockLocationController;
use App\Http\Controllers\Admin\UomController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store')->middleware('throttle:login');
});

Route::middleware(['auth', 'auth.session', 'active'])->group(function () {
    Route::post('/logout', [LogoutController::class, 'destroy'])->name('logout');
    Route::post('/admin/confirm-password', ConfirmPasswordController::class)->name('admin.confirm-password');

    Route::get('/dashboard', function () {
        $user = auth()->user();
        $tenantId = $user->getAccessibleTenantId();

        $stats = [
            'total_users' => \App\Models\User::query()
                ->when($tenantId !== null, fn($query) => $query->where('tenant_id', $tenantId))
                ->count(),
            'active_users' => \App\Models\User::query()
                ->when($tenantId !== null, fn($query) => $query->where('tenant_id', $tenantId))
                ->where('is_active', true)
                ->count(),
            'audit_today' => \App\Models\AuditLog::query()
                ->when($tenantId !== null, fn($query) => $query->where('tenant_id', $tenantId))
                ->whereDate('created_at', today())
                ->count(),
        ];

        return view('dashboard', compact('stats'));
    })->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::patch('users/{user}/deactivate', [UserController::class, 'deactivate'])
            ->name('users.deactivate');

        Route::get('audit-logs', [AuditLogController::class, 'index'])
            ->name('audit-logs.index')
            ->middleware('permission:audit_logs.view');

        Route::resource('branches', BranchController::class);
        Route::patch('branches/{branch}/deactivate', [BranchController::class, 'deactivate'])
            ->name('branches.deactivate');
        Route::patch('branches/{branch}/reactivate', [BranchController::class, 'reactivate'])
            ->name('branches.reactivate');

        Route::resource('warehouses', WarehouseController::class);
        Route::patch('warehouses/{warehouse}/deactivate', [WarehouseController::class, 'deactivate'])
            ->name('warehouses.deactivate');
        Route::patch('warehouses/{warehouse}/reactivate', [WarehouseController::class, 'reactivate'])
            ->name('warehouses.reactivate');

        Route::resource('brands', BrandController::class)->except(['show']);
        Route::patch('brands/{brand}/deactivate', [BrandController::class, 'deactivate'])
            ->name('brands.deactivate');

        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::patch('categories/{category}/deactivate', [CategoryController::class, 'deactivate'])
            ->name('categories.deactivate');

        Route::resource('uoms', UomController::class)->except(['show']);
        Route::patch('uoms/{uom}/deactivate', [UomController::class, 'deactivate'])
            ->name('uoms.deactivate');

        Route::resource('attribute-definitions', AttributeDefinitionController::class)->except(['show']);

        Route::get('items/import', [ItemController::class, 'importForm'])->name('items.import-form');
        Route::post('items/import', [ItemController::class, 'import'])->name('items.import');
        Route::resource('items', ItemController::class);
        Route::patch('items/{item}/deactivate', [ItemController::class, 'deactivate'])
            ->name('items.deactivate');

        // Barcode management per item (PRD §11.3 — Produk dapat memiliki QR code / barcode)
        Route::get('items/{item}/barcodes', [ItemBarcodeController::class, 'index'])
            ->name('items.barcodes.index');
        Route::post('items/{item}/barcodes', [ItemBarcodeController::class, 'store'])
            ->name('items.barcodes.store');
        Route::patch('items/{item}/barcodes/{barcode}/set-primary', [ItemBarcodeController::class, 'setPrimary'])
            ->name('items.barcodes.set-primary');
        Route::delete('items/{item}/barcodes/{barcode}', [ItemBarcodeController::class, 'destroy'])
            ->name('items.barcodes.destroy');

        Route::resource('stock-locations', StockLocationController::class)->except(['show']);
        Route::patch('stock-locations/{stock_location}/deactivate', [StockLocationController::class, 'deactivate'])
            ->name('stock-locations.deactivate');
        Route::patch('stock-locations/{stock_location}/reactivate', [StockLocationController::class, 'reactivate'])
            ->name('stock-locations.reactivate');
    });
});
