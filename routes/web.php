<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceRegistrationController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\MasterDeviceController;
use App\Http\Controllers\Admin\SensorLogController;

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

// Admin login (separate guard, no conflict)
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login']);

// ============================================
// Authenticated User Routes
// ============================================
Route::middleware('auth')->group(function () {
    // Dashboard (multi-card view)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Management
    Route::get('/dashboard/profile', [DashboardController::class, 'editProfile'])->name('profile.edit');
    Route::put('/dashboard/profile', [DashboardController::class, 'updateProfile'])->name('profile.update');
    Route::put('/dashboard/password', [DashboardController::class, 'changePassword'])->name('profile.password');

    // Device Detail & Export
    Route::get('/dashboard/device/{id}', [DashboardController::class, 'deviceDetail'])->name('device.detail');
    Route::get('/dashboard/device/{id}/export-pdf', [DashboardController::class, 'exportPdf'])->name('device.export-pdf');
    Route::put('/dashboard/device/{id}/thresholds', [DashboardController::class, 'updateThresholds'])->name('device.thresholds');

    // Device self-registration (user flow)
    Route::post('/dashboard/register-device', [DeviceRegistrationController::class, 'register'])->name('device.register');
    Route::post('/dashboard/unregister-device', [DeviceRegistrationController::class, 'unregister'])->name('device.unregister');

    // AJAX API endpoints for dashboard cards
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

    // Master Device Whitelist Management
    Route::get('/master-devices', [MasterDeviceController::class, 'index'])->name('admin.master-devices');
    Route::post('/master-devices', [MasterDeviceController::class, 'store'])->name('admin.master-devices.store');
    Route::post('/master-devices/{masterDevice}/assign', [MasterDeviceController::class, 'assignDevice'])->name('admin.master-devices.assign');
    Route::delete('/master-devices/{masterDevice}/unassign', [MasterDeviceController::class, 'unassignDevice'])->name('admin.master-devices.unassign');
    Route::delete('/master-devices/{masterDevice}', [MasterDeviceController::class, 'destroy'])->name('admin.master-devices.destroy');

    // Sensor Logs Management
    Route::get('/sensor-logs', [SensorLogController::class, 'index'])->name('admin.sensor-logs');
    Route::delete('/sensor-logs/purge', [SensorLogController::class, 'purgeOldLogs'])->name('admin.sensor-logs.purge');

    // Admin Logout
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});
