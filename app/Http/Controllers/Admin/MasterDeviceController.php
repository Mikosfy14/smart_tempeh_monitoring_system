<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\MasterDevice;
use App\Models\User;
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

        $users = User::orderBy('name')->get();

        return view('admin.devices', compact('masterDevices', 'users'));
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
     * Assign a master device to a specific user (Admin action).
     */
    public function assignDevice(Request $request, MasterDevice $masterDevice)
    {
        $request->validate([
            'user_id'   => 'required|exists:users,id',
            'label_rak' => 'nullable|string|max:255',
        ]);

        if ($masterDevice->is_registered) {
            $message = 'Device ini sudah terdaftar oleh user lain.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return redirect()->route('admin.master-devices')->with('error', $message);
        }

        // TUKANG SAPU OTOMATIS: Bersihkan rekam jejak perangkat "hantu" yang 
        // sebelumnya tersangkut karena bug unassign versi lama.
        \App\Models\Device::where('device_id', $masterDevice->device_id)->delete();

        // Buat record perangkat dengan ID Primary Key yang baru
        $device = \App\Models\Device::create([
            'user_id'        => $request->user_id,
            'device_name'    => $masterDevice->device_id,
            'device_id'      => $masterDevice->device_id,
            'label_rak'      => $request->label_rak ?: "Rak {$masterDevice->device_id}",
            'operation_mode' => 'AUTO',
            'fan_status'     => 'OFF',
        ]);

        $masterDevice->update(['is_registered' => true]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Device {$masterDevice->device_id} berhasil di-assign ke user.",
                'device'  => $device->load('user'),
            ]);
        }

        return redirect()->route('admin.master-devices')->with('success', 'Device berhasil di-assign.');
    }

    public function unassignDevice(Request $request, MasterDevice $masterDevice)
    {
        if (!$masterDevice->is_registered) {
            $message = 'Device ini belum terdaftar ke user manapun.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return redirect()->route('admin.master-devices')->with('error', $message);
        }

        // PENGHAPUSAN FISIK: Hapus baris dari tabel devices secara total.
        // Relasi sensor_logs & fermentation_batches otomatis diselamatkan (set null)
        // berkat file migrasi yang sudah dieksekusi sebelumnya.
        $device = \App\Models\Device::where('device_id', $masterDevice->device_id)->first();
        if ($device) {
            $device->delete();
        }

        $masterDevice->update(['is_registered' => false]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Device {$masterDevice->device_id} berhasil di-unassign.",
            ]);
        }

        return redirect()->route('admin.master-devices')->with('success', 'Device berhasil di-unassign.');
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
