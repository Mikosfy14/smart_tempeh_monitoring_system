<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\SensorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the multi-card dashboard — one card per device/rack.
     */
    public function index()
    {
        $user = Auth::user();

        // Load all devices with their latest sensor reading
        $devices = $user->devices()
            ->with('latestLog')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('dashboard.index', compact('devices'));
    }

    /**
     * AJAX: Get live sensor data for a specific device card.
     */
    public function liveData(Request $request)
    {
        $user = Auth::user();
        $deviceId = $request->input('device_id');

        $device = Device::where('id', $deviceId)
            ->where('user_id', $user->id)
            ->first();

        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }

        $latestLog = $device->latestLog;

        return response()->json([
            'sensors' => $latestLog ? [
                'internal_temp' => $latestLog->internal_temp,
                'amonia_level'  => $latestLog->amonia_level,
                'room_temp'     => $latestLog->room_temp,
                'humidity'      => $latestLog->humidity,
                'timestamp'     => $latestLog->created_at->format('H:i:s'),
            ] : null,
            'fan' => [
                'status' => $device->fan_status,
                'mode'   => $device->operation_mode,
            ],
        ]);
    }

    /**
     * AJAX: Get chart data for a specific device.
     */
    public function chartData(Request $request)
    {
        $user = Auth::user();
        $deviceId = $request->input('device_id');
        $range = $request->input('range', '24h');

        $device = Device::where('id', $deviceId)
            ->where('user_id', $user->id)
            ->first();

        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }

        $since = match ($range) {
            '1h'  => now()->subHour(),
            '6h'  => now()->subHours(6),
            '24h' => now()->subHours(24),
            '7d'  => now()->subDays(7),
            default => now()->subHours(24),
        };

        $logs = SensorLog::where('device_id', $device->id)
            ->where('created_at', '>=', $since)
            ->orderBy('created_at', 'asc')
            ->get(['internal_temp', 'amonia_level', 'room_temp', 'humidity', 'created_at']);

        return response()->json([
            'labels'        => $logs->pluck('created_at')->map(fn($d) => $d->format('H:i')),
            'internal_temp' => $logs->pluck('internal_temp'),
            'amonia_level'  => $logs->pluck('amonia_level'),
            'room_temp'     => $logs->pluck('room_temp'),
            'humidity'      => $logs->pluck('humidity'),
        ]);
    }

    /**
     * AJAX: Control fan (toggle mode / fan status) for a specific device.
     */
    public function controlFan(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'device_id'  => 'required|integer',
            'mode'       => 'required|in:AUTO,MANUAL',
            'fan_status' => 'required|in:ON,OFF',
        ]);

        $device = Device::where('id', $request->device_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }

        $device->update([
            'operation_mode' => $request->mode,
            'fan_status'     => $request->fan_status,
        ]);

        return response()->json([
            'success' => true,
            'fan' => [
                'status' => $device->fan_status,
                'mode'   => $device->operation_mode,
            ],
        ]);
    }
}
