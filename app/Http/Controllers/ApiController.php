<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\FermentationBatch;
use App\Models\SensorLog;
use App\Services\FermentationPredictionService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    protected WhatsAppService $whatsApp;
    protected FermentationPredictionService $predictor;

    public function __construct(WhatsAppService $whatsApp, FermentationPredictionService $predictor)
    {
        $this->whatsApp  = $whatsApp;
        $this->predictor = $predictor;
    }

    /**
     * POST /api/telemetry
     *
     * Endpoint utama untuk menerima data sensor dari ESP32.
     *
     * Alur kerja dinamis:
     * 1. Validasi API Key dari header X-API-Key
     * 2. Baca device_id dari payload ESP32
     * 3. Cari Device di database, tarik data User (pemilik) via relasi Eloquent
     * 4. Simpan data sensor (MQ-135 amonia, DHT22 suhu+kelembaban, DS18B20 suhu internal)
     * 5. Jalankan expert system prediksi (jika ada batch aktif)
     * 6. Cek threshold dinamis per-device:
     *    - MQ-135 Amonia vs amonia_threshold milik user
     *    - DHT22 Suhu vs temp_threshold milik user
     *    - DHT22 Kelembaban vs humidity_threshold milik user
     * 7. Kirim WhatsApp alert ke nomor WA user jika threshold terlampaui
     */
    public function telemetry(Request $request)
    {
        // ================================================
        // SECURITY GUARD: STATIC API KEY CHECK
        // ================================================
        $apiKey = $request->header('X-API-Key');
        $validKey = env('ESP32_API_KEY');

        if (!$validKey || $apiKey !== $validKey) {
            Log::warning('Unauthorized API access attempt to telemetry endpoint.', ['ip' => $request->ip()]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Akses ditolak: API Key tidak valid atau tidak ditemukan.'
            ], 401);
        }
        // ================================================

        $request->validate([
            'device_id'     => 'required|string',
            'internal_temp' => 'required|numeric',
            'amonia_level'  => 'required|numeric',
            'room_temp'     => 'required|numeric',
            'humidity'      => 'required|numeric',
        ]);

        // ================================================
        // STEP 1: Baca device_id, cari Device & User pemilik
        // ================================================
        $device = Device::where('device_id', $request->device_id)
            ->with('user')  // Eager-load relasi: Device → User (pemilik)
            ->first();

        if (!$device) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Device ID not found or not registered.',
            ], 404);
        }

        // Tarik data User (pemilik device) secara dinamis dari database
        $user = $device->user;

        Log::info('ApiController: Telemetry diterima', [
            'device_id' => $device->device_id,
            'user_id'   => $user?->id ?? 'unassigned',
            'user_name' => $user?->name ?? 'N/A',
            'wa_number' => $user?->whatsapp_number ?? 'N/A',
        ]);

        // ================================================
        // STEP 2: Cari batch aktif untuk device ini
        // ================================================
        $activeBatch = FermentationBatch::where('device_id', $device->id)
            ->whereIn('status', ['active', 'semangit'])
            ->orderByDesc('start_time')
            ->first();

        // Simpan sensor log, sertakan batch_id jika ada batch yang sedang berjalan
        $newLog = SensorLog::create([
            'device_id'     => $device->id,
            'batch_id'      => $activeBatch?->id,
            'internal_temp' => $request->internal_temp,
            'amonia_level'  => $request->amonia_level,
            'room_temp'     => $request->room_temp,
            'humidity'      => $request->humidity,
        ]);

        Log::info('ApiController: Sensor log disimpan', [
            'device_id'   => $device->id,
            'batch_id'    => $activeBatch?->id ?? 'null (tidak ada batch aktif)',
            'amonia'      => $request->amonia_level,
            'temp'        => $request->internal_temp,
        ]);

        // ================================================
        // STEP 3: Jalankan expert system prediksi
        // ================================================
        if ($activeBatch) {
            try {
                $this->predictor->analyze($activeBatch, $newLog);
                $activeBatch->refresh();
            } catch (\Exception $e) {
                Log::error('BatchPredictor: Exception saat analisis', [
                    'batch_id' => $activeBatch->id,
                    'error'    => $e->getMessage(),
                    'trace'    => $e->getTraceAsString(),
                ]);
            }
        }

        // ================================================
        // STEP 4: Threshold Checks — Dinamis per-device
        // ================================================
        // Threshold dibaca dari device (bukan hardcode), sehingga
        // setiap user bisa mengatur batas sesuai kebutuhannya.
        $alertsSent = [];

        $tempThreshold     = $device->temp_threshold ?? 35.0;
        $amoniaThreshold   = $device->amonia_threshold ?? 2.0;
        $humidityThreshold = $device->humidity_threshold ?? 90.0;

        // --- DHT22: Suhu (internal_temp dari DS18B20) ---
        if ($request->internal_temp > $tempThreshold) {
            Log::info('ApiController: Threshold SUHU terlampaui', [
                'value'     => $request->internal_temp,
                'threshold' => $tempThreshold,
                'has_user'  => !!$user,
                'has_wa'    => !!($user?->whatsapp_number),
            ]);
            // AUTO mode: kipas otomatis menyala saat suhu melebihi threshold
            if ($device->operation_mode === 'AUTO' && $device->fan_status === 'OFF') {
                $device->update(['fan_status' => 'ON']);
            }
            // Kirim WhatsApp alert ke nomor WA user (dinamis dari DB)
            if ($user && $user->whatsapp_number) {
                $sent = $this->whatsApp->sendAlert($user, $device, 'temp', $request->internal_temp);
                if ($sent) $alertsSent[] = 'temp';
            } else {
                Log::warning('ApiController: Alert suhu dilewati — user atau WA number tidak ada');
            }
        } else {
            // Suhu kembali normal → matikan kipas (jika AUTO)
            if ($device->operation_mode === 'AUTO' && $device->fan_status === 'ON') {
                $device->update(['fan_status' => 'OFF']);
            }
            // Kirim notifikasi recovery ke user
            if ($user && $user->whatsapp_number) {
                $recovered = $this->whatsApp->sendRecoveryNotification($user, $device, 'temp', $request->internal_temp);
                if ($recovered) $alertsSent[] = 'temp_recovery';
            }
        }

        // --- MQ-135: Gas Amonia ---
        if ($request->amonia_level > $amoniaThreshold) {
            Log::info('ApiController: Threshold AMONIA terlampaui', [
                'value'     => $request->amonia_level,
                'threshold' => $amoniaThreshold,
                'has_user'  => !!$user,
                'has_wa'    => !!($user?->whatsapp_number),
            ]);
            // Amonia melebihi threshold → kirim alert ke user
            if ($user && $user->whatsapp_number) {
                $sent = $this->whatsApp->sendAlert($user, $device, 'amonia', $request->amonia_level);
                if ($sent) $alertsSent[] = 'amonia';
            } else {
                Log::warning('ApiController: Alert amonia dilewati — user atau WA number tidak ada');
            }
        } else {
            // Amonia kembali normal
            if ($user && $user->whatsapp_number) {
                $recovered = $this->whatsApp->sendRecoveryNotification($user, $device, 'amonia', $request->amonia_level);
                if ($recovered) $alertsSent[] = 'amonia_recovery';
            }
        }

        // --- DHT22: Kelembaban ---
        if ($request->humidity > $humidityThreshold) {
            if ($user && $user->whatsapp_number) {
                $sent = $this->whatsApp->sendAlert($user, $device, 'humidity', $request->humidity);
                if ($sent) $alertsSent[] = 'humidity';
            }
        } else {
            if ($user && $user->whatsapp_number) {
                $recovered = $this->whatsApp->sendRecoveryNotification($user, $device, 'humidity', $request->humidity);
                if ($recovered) $alertsSent[] = 'humidity_recovery';
            }
        }

        // Refresh device untuk mendapatkan fan_status terbaru
        $device->refresh();

        // ================================================
        // Response untuk ESP32
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
            'batch'            => $activeBatch ? [
                'id'     => $activeBatch->id,
                'status' => $activeBatch->status,
            ] : null,
            'server_time'      => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * GET /api/device/status?device_id=TEMPE-001
     * ESP32 polls this every 1 second to get fan commands.
     */
    public function status(Request $request)
    {
        // ================================================
        // SECURITY GUARD: STATIC API KEY CHECK
        // ================================================
        $apiKey = $request->header('X-API-Key');
        $validKey = env('ESP32_API_KEY');

        if (!$validKey || $apiKey !== $validKey) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akses ditolak: API Key tidak valid atau tidak ditemukan.'
            ], 401);
        }
        // ================================================

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
