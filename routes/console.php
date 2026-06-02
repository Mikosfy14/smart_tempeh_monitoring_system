<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('device:check-offline')->everyMinute();

Schedule::call(function () {
    // 1. Hapus Orphan Logs (data sampah tanpa device_id dan tanpa batch_id)
    $orphanDeleted = \App\Models\SensorLog::whereNull('device_id')
        ->whereNull('batch_id')
        ->delete();

    // 2. Data Retention (Hapus log yang lebih tua dari 30 hari)
    $expiredDeleted = \App\Models\SensorLog::where('created_at', '<', now()->subDays(30))
        ->delete();

    // 3. Catat di file log (storage/logs/laravel.log)
    \Illuminate\Support\Facades\Log::info("Auto-Cleanup SensorLog Berjalan: {$orphanDeleted} orphan logs dihapus, {$expiredDeleted} expired logs (> 30 hari) dihapus.");
})->dailyAt('00:00');
