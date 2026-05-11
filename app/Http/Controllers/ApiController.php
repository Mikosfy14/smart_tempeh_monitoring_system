<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\MasterDevice;
use App\Models\SensorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    /**
     * POST /api/telemetry
     * ESP32 sends sensor data. Includes threshold check + WhatsApp alert.
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
                'status' => 'error',
                'message' => 'Device ID not found or not registered.',
            ], 404);
        }

        // Store sensor log
        $log = SensorLog::create([
            'device_id'     => $device->id,
            'internal_temp' => $request->internal_temp,
            'amonia_level'  => $request->amonia_level,
            'room_temp'     => $request->room_temp,
            'humidity'      => $request->humidity,
        ]);

        // ================================================
        // AUTO MODE: Threshold check + fan mitigation
        // If operation_mode == AUTO and internal_temp > 35°C:
        //   1. Turn fan ON
        //   2. Send WhatsApp alert via Fonnte
        // ================================================
        $alertSent = false;
        if ($device->operation_mode === 'AUTO' && $request->internal_temp > 35) {
            // Only trigger if fan was previously OFF (avoid spamming)
            if ($device->fan_status === 'OFF') {
                $device->update(['fan_status' => 'ON']);

                // Send WhatsApp alert if user has a number
                $user = $device->user;
                if ($user && $user->whatsapp_number) {
                    $this->sendWhatsAppAlert($user, $device, $request->internal_temp);
                    $alertSent = true;
                }
            }
        }

        // AUTO MODE: If temp drops back to normal, turn fan OFF
        if ($device->operation_mode === 'AUTO' && $request->internal_temp <= 35) {
            if ($device->fan_status === 'ON') {
                $device->update(['fan_status' => 'OFF']);
            }
        }

        return response()->json([
            'status'      => 'success',
            'message'     => 'Telemetry data stored.',
            'alert_sent'  => $alertSent,
            'fan_status'  => $device->fresh()->fan_status,
            'server_time' => now()->format('Y-m-d H:i:s'),
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
            'status'         => 'success',
            'device_id'      => $device->device_id,
            'operation_mode' => $device->operation_mode,
            'fan_status'     => $device->fan_status,
            'server_time'    => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Send WhatsApp alert via Fonnte API.
     */
    private function sendWhatsAppAlert($user, $device, $temperature)
    {
        $label = $device->label_rak ?? $device->device_name ?? $device->device_id;
        $message = "⚠️ *PERINGATAN SUHU TINGGI*\n\n"
            . "Rak: *{$label}*\n"
            . "Device ID: {$device->device_id}\n"
            . "Suhu Internal: *{$temperature}°C*\n"
            . "Ambang Batas: 35°C\n\n"
            . "🔄 Kipas pendingin telah *DINYALAKAN OTOMATIS*.\n"
            . "Waktu: " . now()->format('d M Y H:i:s');

        try {
            $response = Http::withHeaders([
                'Authorization' => config('services.fonnte.token', ''),
            ])->post('https://api.fonnte.com/send', [
                'target'  => $user->whatsapp_number,
                'message' => $message,
            ]);

            Log::info('WhatsApp alert sent', [
                'device_id' => $device->device_id,
                'user'      => $user->name,
                'response'  => $response->json(),
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp alert failed', [
                'device_id' => $device->device_id,
                'error'     => $e->getMessage(),
            ]);
        }
    }
}
