<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\MasterDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceRegistrationController extends Controller
{
    /**
     * Register a device from master_devices whitelist to the current user.
     */
    public function register(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string|max:255',
            'label_rak' => 'nullable|string|max:255',
        ]);

        $deviceId = strtoupper(trim($request->device_id));

        // Check if device_id exists in whitelist
        $master = MasterDevice::where('device_id', $deviceId)->first();

        if (!$master) {
            return $this->respond($request, false, 'Device ID tidak terdaftar dalam sistem. Hubungi admin.');
        }

        if ($master->is_registered) {
            return $this->respond($request, false, 'Device ID sudah diklaim oleh user lain.');
        }

        // Create device record and link to user
        $device = Device::create([
            'user_id'        => Auth::id(),
            'device_name'    => $deviceId,
            'device_id'      => $deviceId,
            'label_rak'      => $request->label_rak ?: "Rak {$deviceId}",
            'operation_mode' => 'AUTO',
            'fan_status'     => 'OFF',
        ]);

        // Mark as registered in whitelist
        $master->update(['is_registered' => true]);

        return $this->respond($request, true, "Alat {$deviceId} berhasil didaftarkan!", $device);
    }

    /**
     * Unregister a device from the current user.
     * Preserves sensor_logs — only nullifies user_id.
     */
    public function unregister(Request $request)
    {
        $request->validate([
            'device_id' => 'required|integer',
        ]);

        $device = Device::where('id', $request->device_id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$device) {
            return $this->respond($request, false, 'Device tidak ditemukan.');
        }

        // Reset master device registration status
        MasterDevice::where('device_id', $device->device_id)
            ->update(['is_registered' => false]);

        // Nullify user_id — DO NOT delete sensor_logs (data persistence)
        $device->update(['user_id' => null]);

        return $this->respond($request, true, "Alat {$device->device_id} berhasil dilepas.");
    }

    /**
     * Helper: respond JSON or redirect depending on request type.
     */
    private function respond(Request $request, bool $success, string $message, $data = null)
    {
        if ($request->wantsJson()) {
            $response = ['success' => $success, 'message' => $message];
            if ($data) $response['device'] = $data;
            return response()->json($response, $success ? 200 : 422);
        }

        return redirect()->route('dashboard')
            ->with($success ? 'success' : 'error', $message);
    }
}
