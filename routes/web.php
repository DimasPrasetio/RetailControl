<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Route;

// ─── Guest routes ─────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

// ─── Authenticated routes ──────────────────────────────────────────────────────
Route::middleware(['auth', 'active'])->group(function () {

    Route::post('/logout', [LogoutController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', function () {
        $stats = [
            'total_users'  => \App\Models\User::count(),
            'active_users' => \App\Models\User::where('is_active', true)->count(),
            'audit_today'  => \App\Models\AuditLog::whereDate('created_at', today())->count(),
        ];
        return view('dashboard', compact('stats'));
    })->name('dashboard');

    // ─── User Management (Super Admin only via UserPolicy) ─────────────────
    Route::prefix('admin')->name('admin.')->group(function () {

        Route::resource('users', UserController::class)
            ->except(['show']);

        Route::get('audit-logs', [AuditLogController::class, 'index'])
            ->name('audit-logs.index')
            ->middleware('permission:audit_logs.view');
    });
});
