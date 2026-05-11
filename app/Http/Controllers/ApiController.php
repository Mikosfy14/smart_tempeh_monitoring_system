<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\SensorLog;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    protected WhatsAppService $whatsApp;

    public function __construct(WhatsAppService $whatsApp)
    {
        $this->whatsApp = $whatsApp;
    }

    /**
     * POST /api/telemetry
     * ESP32 sends sensor data. Includes dynamic threshold check + WhatsApp alert with cooldown.
     */
    public function telemetry(Request $request)
    {
        $request->validate([
            'device_id'     => 'required|string',
            'internal_temp' => 'required|numeric',
            'amonia_level'  => 'required|numeric',
            'room_temp'     => 'required|numeric',
            'humidity'      => 'required|numeric',
        ]);

        // Find the device by its hardware ID
        $device = Device::where('device_id', $request->device_id)->first();

        if (!$device) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Device ID not found or not registered.',
            ], 404);
        }

        // Store sensor log
        SensorLog::create([
            'device_id'     => $device->id,
            'internal_temp' => $request->internal_temp,
            'amonia_level'  => $request->amonia_level,
            'room_temp'     => $request->room_temp,
            'humidity'      => $request->humidity,
        ]);

        // ================================================
        // THRESHOLD CHECKS — Dynamic per-device thresholds
        // ================================================
        $alertsSent = [];
        $user = $device->user;

        $tempThreshold     = $device->temp_threshold ?? 35.0;
        $amoniaThreshold   = $device->amonia_threshold ?? 25.0;
        $humidityThreshold = $device->humidity_threshold ?? 90.0;

        // --- Temperature threshold ---
        if ($request->internal_temp > $tempThreshold) {
            // AUTO mode: activate fan automatically
            if ($device->operation_mode === 'AUTO' && $device->fan_status === 'OFF') {
                $device->update(['fan_status' => 'ON']);
            }
            // Send alert (with cooldown)
            if ($user) {
                $sent = $this->whatsApp->sendAlert($user, $device, 'temp', $request->internal_temp);
                if ($sent) $alertsSent[] = 'temp';
            }
        } else {
            // Temperature back to normal
            if ($device->operation_mode === 'AUTO' && $device->fan_status === 'ON') {
                $device->update(['fan_status' => 'OFF']);
            }
            // Recovery notification
            if ($user) {
                $recovered = $this->whatsApp->sendRecoveryNotification($user, $device, 'temp', $request->internal_temp);
                if ($recovered) $alertsSent[] = 'temp_recovery';
            }
        }

        // --- Ammonia threshold ---
        if ($request->amonia_level > $amoniaThreshold) {
            if ($user) {
                $sent = $this->whatsApp->sendAlert($user, $device, 'amonia', $request->amonia_level);
                if ($sent) $alertsSent[] = 'amonia';
            }
        } else {
            if ($user) {
                $recovered = $this->whatsApp->sendRecoveryNotification($user, $device, 'amonia', $request->amonia_level);
                if ($recovered) $alertsSent[] = 'amonia_recovery';
            }
        }

        // --- Humidity threshold ---
        if ($request->humidity > $humidityThreshold) {
            if ($user) {
                $sent = $this->whatsApp->sendAlert($user, $device, 'humidity', $request->humidity);
                if ($sent) $alertsSent[] = 'humidity';
            }
        } else {
            if ($user) {
                $recovered = $this->whatsApp->sendRecoveryNotification($user, $device, 'humidity', $request->humidity);
                if ($recovered) $alertsSent[] = 'humidity_recovery';
            }
        }

        // Refresh device to get latest fan_status after possible updates
        $device->refresh();

        // ================================================
        // Consistent JSON response for ESP32
        // Always includes fan_status, operation_mode, and thresholds
        // so the ESP32 can sync its local logic.
        // ================================================
        return response()->json([
            'status'           => 'success',
            'message'          => 'Telemetry data stored.',
            'alerts_sent'      => $alertsSent,
            'fan_status'       => $device->fan_status,
            'operation_mode'   => $device->operation_mode,
            'thresholds'       => [
                'temp'     => $tempThreshold,
                'amonia'   => $amoniaThreshold,
                'humidity' => $humidityThreshold,
            ],
            'server_time'      => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * GET /api/device/status?device_id=TEMPE-001
     * ESP32 polls this every 1 second to get fan commands.
     */
    public function status(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
        ]);

        $device = Device::where('device_id', $request->device_id)->first();

        if (!$device) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Device ID not found.',
            ], 404);
        }

        return response()->json([
            'status'           => 'success',
            'device_id'        => $device->device_id,
            'operation_mode'   => $device->operation_mode,
            'fan_status'       => $device->fan_status,
            'thresholds'       => [
                'temp'     => $device->temp_threshold ?? 35.0,
                'amonia'   => $device->amonia_threshold ?? 25.0,
                'humidity' => $device->humidity_threshold ?? 90.0,
            ],
            'server_time'      => now()->format('Y-m-d H:i:s'),
        ]);
    }
}
