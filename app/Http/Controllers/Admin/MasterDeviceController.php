<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterDevice;
use Illuminate\Http\Request;

class MasterDeviceController extends Controller
{
    /**
     * Show the master devices whitelist page.
     */
    public function index()
    {
        $masterDevices = MasterDevice::with('device.user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.devices', compact('masterDevices'));
    }

    /**
     * Add a new device ID to the whitelist (AJAX).
     */
    public function store(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string|unique:master_devices,device_id|max:255',
        ]);

        $deviceId = strtoupper(trim($request->device_id));

        $masterDevice = MasterDevice::create([
            'device_id'     => $deviceId,
            'is_registered' => false,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Device {$deviceId} berhasil ditambahkan ke whitelist.",
                'data'    => $masterDevice,
            ]);
        }

        return redirect()->route('admin.master-devices')->with('success', 'Device berhasil ditambahkan.');
    }

    /**
     * Remove a device from the whitelist (AJAX).
     */
    public function destroy(Request $request, MasterDevice $masterDevice)
    {
        if ($masterDevice->is_registered) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus device yang sedang digunakan oleh user.',
                ], 422);
            }
            return redirect()->route('admin.master-devices')->with('error', 'Device sedang digunakan.');
        }

        $masterDevice->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Device berhasil dihapus dari whitelist.',
            ]);
        }

        return redirect()->route('admin.master-devices')->with('success', 'Device berhasil dihapus.');
    }
}
