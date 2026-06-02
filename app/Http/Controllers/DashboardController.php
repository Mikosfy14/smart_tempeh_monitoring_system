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
            ->with(['latestLog', 'activeBatch'])
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



    /**
     * Inisialisasi proses tautan akun Google.
     */
    public function linkGoogle()
    {
        // Beri tanda ke session bahwa ini adalah proses tautan akun, bukan login biasa
        session(['linking_google' => true]);
        session()->save(); // Simpan session secara paksa sebelum redirect ke luar

        return \Laravel\Socialite\Facades\Socialite::driver('google')->redirect();
    }

    public function unlinkGoogle(Request $request)
    {
        $user = Auth::user();

        // Lockout Prevention: Pastikan pengguna sudah memiliki password manual
        if (empty($user->password)) {
            return redirect()->route('profile.edit')->withErrors([
                'google' => 'Anda belum mengatur password manual! Silakan atur password terlebih dahulu sebelum memutuskan koneksi Google untuk menghindari kehilangan akses ke akun Anda.'
            ]);
        }

        $request->validate([
            'password' => 'required|string',
        ], [
            'password.required' => 'Password wajib diisi untuk memutus koneksi Google.',
        ]);

        if (!Hash::check($request->password, $user->password)) {
            return redirect()->route('profile.edit')->withErrors([
                'google' => 'Password yang Anda masukkan salah. Gagal memutus koneksi.'
            ]);
        }

        // Hapus google_id
        $user->update(['google_id' => null]);

        return redirect()->route('profile.edit')->with('success', 'Koneksi akun Google berhasil diputus.');
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
            ->with(['latestLog', 'activeBatch', 'latestBatch'])
            ->firstOrFail();

        // Double-guarantee: load eksplisit setelah eager-load
        // untuk memastikan relasi ofMany() ter-resolve dengan benar.
        $device->load(['activeBatch', 'latestBatch']);

        // activeBatch: batch dengan status 'active' atau 'semangit' (sedang berjalan)
        $activeBatch = $device->activeBatch;

        // latestBatch: batch terakhir TANPA filter status (termasuk 'failed', 'completed')
        // Digunakan untuk menampilkan state "Gagal" di UI jika batch terakhir adalah failed.
        $latestBatch = $device->latestBatch;

        // Initial chart data (last 6 hours)
        $chartData = SensorLog::where('device_id', $device->id)
            ->where('created_at', '>=', now()->subHours(6))
            ->orderBy('created_at', 'asc')
            ->get(['internal_temp', 'amonia_level', 'room_temp', 'humidity', 'created_at']);

        // Initial table data (last 20 logs, newest first)
        $tableData = SensorLog::where('device_id', $device->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get(['internal_temp', 'amonia_level', 'room_temp', 'humidity', 'created_at']);

        return view('dashboard.device-detail', compact(
            'device', 'chartData', 'tableData', 'activeBatch', 'latestBatch'
        ));
    }


    /**
     * Show device edit page.
     */
    public function editDevice($id)
    {
        $user = Auth::user();
        $device = Device::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return view('dashboard.device-edit', compact('device'));
    }

    /**
     * Update device details and thresholds.
     */
    public function updateDevice(Request $request, $id)
    {
        $user = Auth::user();
        $device = Device::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $request->validate([
            'device_name'        => 'nullable|string|max:255',
            'label_rak'          => 'nullable|string|max:255',
            'temp_threshold'     => 'required|numeric|min:0|max:100',
            'amonia_threshold'   => 'required|numeric|min:0|max:500',
            'humidity_threshold' => 'required|numeric|min:0|max:100',
        ]);

        $device->update([
            'device_name'        => $request->device_name,
            'label_rak'          => $request->label_rak,
            'temp_threshold'     => $request->temp_threshold,
            'amonia_threshold'   => $request->amonia_threshold,
            'humidity_threshold' => $request->humidity_threshold,
        ]);

        return redirect()->route('device.detail', $id)->with('success', 'Detail alat dan ambang batas berhasil diperbarui.');
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
                'full_timestamp' => $latestLog->created_at->format('d M Y H:i:s'),
                'log_id'        => $latestLog->id,
            ] : null,
            'fan' => [
                'status' => $device->fan_status,
                'mode'   => $device->operation_mode,
            ],
            'is_online'     => $device->is_online,
            'sensor_status' => $device->sensor_status,
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
