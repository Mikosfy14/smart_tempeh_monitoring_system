<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceRegistrationController;
use App\Http\Controllers\FermentationBatchController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\MasterDeviceController;
use App\Http\Controllers\Admin\SensorLogController;
use App\Http\Controllers\Admin\AdminManagementController;
use App\Http\Controllers\PasswordResetController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Show landing page on root
Route::get('/', function () {
    return view('welcome');
});

// ============================================
// Google OAuth Routes (Bebas Middleware)
// ============================================
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// ============================================
// Guest Routes (not logged in)
// ============================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Registration
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // Complete Profile (for new Google OAuth users)
    Route::get('/register/complete', [AuthController::class, 'showCompleteProfile'])->name('register.complete');
    Route::post('/register/complete', [AuthController::class, 'completeProfile']);

    // Password Reset
    Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

// Admin login (hidden, non-obvious URL — only admins should know this)
Route::get('/stm-internal/gateway', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/stm-internal/gateway', [AdminAuthController::class, 'login']);

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
    Route::get('/dashboard/profile/link-google', [DashboardController::class, 'linkGoogle'])->name('profile.link-google');

    // Device Detail, Edit, & Export
    Route::get('/dashboard/device/{id}', [DashboardController::class, 'deviceDetail'])->name('device.detail');
    Route::get('/dashboard/device/{id}/edit', [DashboardController::class, 'editDevice'])->name('device.edit');
    Route::put('/dashboard/device/{id}', [DashboardController::class, 'updateDevice'])->name('device.update');
    Route::get('/dashboard/device/{id}/export-pdf', [DashboardController::class, 'exportPdf'])->name('device.export-pdf');

    // Device self-registration (user flow)
    Route::post('/dashboard/register-device', [DeviceRegistrationController::class, 'register'])->name('device.register');
    Route::post('/dashboard/unregister-device', [DeviceRegistrationController::class, 'unregister'])->name('device.unregister');

    // Fermentation Batch Management
    Route::post('/dashboard/device/{device}/batch/start', [FermentationBatchController::class, 'startBatch'])->name('batch.start');
    Route::post('/dashboard/device/{device}/batch/{batch}/end', [FermentationBatchController::class, 'endBatch'])->name('batch.end');
    
    // Fermentation Batch History & Export
    Route::get('/dashboard/device/{device}/batches', [FermentationBatchController::class, 'history'])->name('batch.history');
    Route::post('/dashboard/device/{device}/batches/export-pdf', [FermentationBatchController::class, 'exportPdf'])->name('batch.export-pdf');
    Route::put('/dashboard/device/{device}/batch/{batch}', [FermentationBatchController::class, 'update'])->name('batch.update');
    Route::delete('/dashboard/device/{device}/batch/{batch}', [FermentationBatchController::class, 'destroy'])->name('batch.destroy');
    // AJAX API endpoints for dashboard cards
    Route::get('/api/dashboard/live', [DashboardController::class, 'liveData'])->name('dashboard.live');
    Route::get('/api/dashboard/chart', [DashboardController::class, 'chartData'])->name('dashboard.chart');
    Route::post('/api/device/control', [DashboardController::class, 'controlFan'])->name('dashboard.control');

    // Link Google Account
    Route::get('/dashboard/profile/link-google', [DashboardController::class, 'linkGoogle'])->name('profile.link-google');
    Route::post('/dashboard/profile/unlink-google', [DashboardController::class, 'unlinkGoogle'])->name('profile.unlink-google');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// ============================================
// Admin Routes (protected by admin middleware — hidden URL prefix)
// ============================================
Route::middleware('admin')->prefix('stm-internal')->group(function () {
    // Admin Dashboard / Overview
    Route::get('/panel', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

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

    // Fermentation Batches Management
    Route::get('/batches', [\App\Http\Controllers\Admin\AdminBatchController::class, 'index'])->name('admin.batches');
    Route::put('/batches/{batch}', [\App\Http\Controllers\Admin\AdminBatchController::class, 'update'])->name('admin.batches.update');
    Route::delete('/batches/{batch}', [\App\Http\Controllers\Admin\AdminBatchController::class, 'destroy'])->name('admin.batches.destroy');
    // Admin Management (Master Admin only — controller enforces is_master check)
    Route::get('/admins', [AdminManagementController::class, 'index'])->name('admin.admins');
    Route::post('/admins', [AdminManagementController::class, 'store'])->name('admin.admins.store');
    Route::put('/admins/{admin}', [AdminManagementController::class, 'update'])->name('admin.admins.update');
    Route::delete('/admins/{admin}', [AdminManagementController::class, 'destroy'])->name('admin.admins.destroy');

    // Admin Logout
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});
