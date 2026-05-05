<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DeviceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// ============================================
// Guest Routes (not logged in)
// ============================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Admin login (separate, no guard conflict)
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login']);

// ============================================
// Authenticated User Routes
// ============================================
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // AJAX API endpoints for dashboard
    Route::get('/api/dashboard/live', [DashboardController::class, 'liveData'])->name('dashboard.live');
    Route::get('/api/dashboard/chart', [DashboardController::class, 'chartData'])->name('dashboard.chart');
    Route::post('/api/device/control', [DashboardController::class, 'controlFan'])->name('dashboard.control');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// ============================================
// Admin Routes (protected by admin middleware)
// ============================================
Route::middleware(\App\Http\Middleware\AdminMiddleware::class)->prefix('admin')->group(function () {
    // Admin Dashboard / Overview
    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // User Management
    Route::get('/users', [UserController::class, 'index'])->name('admin.users');
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');

    // Device Mapping
    Route::get('/devices', [DeviceController::class, 'index'])->name('admin.devices');
    Route::post('/devices', [DeviceController::class, 'store'])->name('admin.devices.store');
    Route::delete('/devices/{device}', [DeviceController::class, 'destroy'])->name('admin.devices.destroy');

    // Admin Logout
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});
