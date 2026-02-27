<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Admin\UomController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Route;

// ─── Guest routes ─────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store')->middleware('throttle:5,1');
});

// ─── Authenticated routes ──────────────────────────────────────────────────────
Route::middleware(['auth', 'auth.session', 'active'])->group(function () {

    Route::post('/logout', [LogoutController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', function () {
        $stats = [
            'total_users' => \App\Models\User::count(),
            'active_users' => \App\Models\User::where('is_active', true)->count(),
            'audit_today' => \App\Models\AuditLog::whereDate('created_at', today())->count(),
        ];
        return view('dashboard', compact('stats'));
    })->name('dashboard');

    // ─── User Management (Super Admin only via UserPolicy) ─────────────────
    Route::prefix('admin')->name('admin.')->group(function () {

        Route::resource('users', UserController::class)
            ->except(['show']);

        Route::patch('users/{user}/deactivate', [UserController::class, 'deactivate'])
            ->name('users.deactivate');

        Route::get('audit-logs', [AuditLogController::class, 'index'])
            ->name('audit-logs.index')
            ->middleware('permission:audit_logs.view');

        // ─── Master Data: Brands ───────────────────────────────────────────
        Route::resource('brands', BrandController::class)->except(['show', 'destroy']);
        Route::patch('brands/{brand}/deactivate', [BrandController::class, 'deactivate'])
            ->name('brands.deactivate');

        // ─── Master Data: Categories ──────────────────────────────────────
        Route::resource('categories', CategoryController::class)->except(['show', 'destroy']);
        Route::patch('categories/{category}/deactivate', [CategoryController::class, 'deactivate'])
            ->name('categories.deactivate');

        // ─── Master Data: UoM ─────────────────────────────────────────────
        Route::resource('uoms', UomController::class)->except(['show', 'destroy']);
        Route::patch('uoms/{uom}/deactivate', [UomController::class, 'deactivate'])
            ->name('uoms.deactivate');

        // ─── Master Data: Items (SKU) ──────────────────────────────────────
        Route::get('items/import', [ItemController::class, 'importForm'])->name('items.import-form');
        Route::post('items/import', [ItemController::class, 'import'])->name('items.import');
        Route::resource('items', ItemController::class)->except(['destroy']);
        Route::patch('items/{item}/deactivate', [ItemController::class, 'deactivate'])
            ->name('items.deactivate');
    });
});
