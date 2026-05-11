<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\SensorLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

    // ============================================
    // PROFILE MANAGEMENT
    // ============================================

    /**
     * Show the profile edit page.
     */
    public function editProfile()
    {
        $user = Auth::user();
        return view('dashboard.profile', compact('user'));
    }

    /**
     * Update profile information (name, email, whatsapp).
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email,' . $user->id,
            'whatsapp_number'  => 'nullable|string|max:20',
        ]);

        $user->update([
            'name'            => $request->name,
            'email'           => $request->email,
            'whatsapp_number' => $request->whatsapp_number,
        ]);

        return redirect()->route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Change password — requires old password verification.
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'old_password'     => 'required|string',
            'new_password'     => 'required|string|min:6|confirmed',
        ]);

        // Verify old password
        if (!Hash::check($request->old_password, $user->password)) {
            return back()->withErrors(['old_password' => 'Password lama tidak sesuai.']);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->route('profile.edit')->with('success', 'Password berhasil diubah.');
    }

    // ============================================
    // DEVICE DETAIL & CHARTS
    // ============================================

    /**
     * Show device detail page with real-time charts.
     */
    public function deviceDetail($id)
    {
        $user = Auth::user();
        $device = Device::where('id', $id)
            ->where('user_id', $user->id)
            ->with('latestLog')
            ->firstOrFail();

        // Initial chart data (last 6 hours)
        $chartData = SensorLog::where('device_id', $device->id)
            ->where('created_at', '>=', now()->subHours(6))
            ->orderBy('created_at', 'asc')
            ->get(['internal_temp', 'amonia_level', 'room_temp', 'humidity', 'created_at']);

        return view('dashboard.device-detail', compact('device', 'chartData'));
    }

    /**
     * Update per-device threshold settings.
     */
    public function updateThresholds(Request $request, $id)
    {
        $user = Auth::user();
        $device = Device::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $request->validate([
            'temp_threshold'     => 'required|numeric|min:0|max:100',
            'amonia_threshold'   => 'required|numeric|min:0|max:500',
            'humidity_threshold' => 'required|numeric|min:0|max:100',
        ]);

        $device->update([
            'temp_threshold'     => $request->temp_threshold,
            'amonia_threshold'   => $request->amonia_threshold,
            'humidity_threshold' => $request->humidity_threshold,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Ambang batas berhasil diperbarui.',
            ]);
        }

        return redirect()->route('device.detail', $id)->with('success', 'Ambang batas berhasil diperbarui.');
    }

    /**
     * Export sensor log to PDF for a specific device and date range.
     */
    public function exportPdf(Request $request, $id)
    {
        $user = Auth::user();
        $device = Device::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $request->validate([
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',
        ]);

        $logs = SensorLog::where('device_id', $device->id)
            ->whereBetween('created_at', [
                $request->date_from . ' 00:00:00',
                $request->date_to . ' 23:59:59',
            ])
            ->orderBy('created_at', 'asc')
            ->get();

        $dateFrom = $request->date_from;
        $dateTo   = $request->date_to;

        $pdf = Pdf::loadView('pdf.sensor-report', compact('device', 'logs', 'dateFrom', 'dateTo'))
            ->setPaper('a4', 'landscape');

        $filename = "Laporan_Sensor_{$device->device_id}_{$dateFrom}_to_{$dateTo}.pdf";

        return $pdf->download($filename);
    }

    // ============================================
    // AJAX ENDPOINTS
    // ============================================

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

        // In MANUAL mode, respect user's fan toggle directly.
        // In AUTO mode, fan is controlled by server threshold logic.
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
