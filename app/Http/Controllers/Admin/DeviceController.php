<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeviceController extends Controller
{
    /**
     * Show the device mapping page.
     */
    public function index()
    {
        $devices = Device::with('user')->orderBy('created_at', 'desc')->get();
        $users = User::orderBy('name')->get();

        return view('admin.devices', compact('devices', 'users'));
    }

    /**
     * Link a device to a user (AJAX).
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'device_name' => 'required|string|max:255',
            'device_token' => 'required|string|unique:devices,device_token|max:255',
        ]);

        $device = Device::create([
            'user_id' => $request->user_id,
            'device_name' => $request->device_name,
            'device_token' => $request->device_token,
            'operation_mode' => 'AUTO',
            'fan_status' => 'OFF',
        ]);

        $device->load('user');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Device berhasil di-link ke user.',
                'device' => $device,
            ]);
        }

        return redirect()->route('admin.devices')->with('success', 'Device berhasil di-link.');
    }

    /**
     * Unlink / delete a device (AJAX).
     */
    public function destroy(Request $request, Device $device)
    {
        $device->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Device berhasil di-unlink.',
            ]);
        }

        return redirect()->route('admin.devices')->with('success', 'Device berhasil di-unlink.');
    }
}
