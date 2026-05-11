<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

/*
|--------------------------------------------------------------------------
| API Routes — ESP32 Hardware Endpoints
|--------------------------------------------------------------------------
| These routes are stateless (no session/CSRF). They use device_id
| for identification. No auth middleware — hardware authenticates
| via its unique device_id registered in master_devices.
*/

// ESP32 sends sensor readings
Route::post('/telemetry', [ApiController::class, 'telemetry']);

// ESP32 polls for fan commands (every 1 second)
Route::get('/device/status', [ApiController::class, 'status']);
