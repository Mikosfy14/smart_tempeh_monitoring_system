<?php

namespace App\Services;

use App\Models\FermentationBatch;
use App\Models\SensorLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * FermentationPredictionService
 *
 * Rule-Based Expert System untuk mendeteksi kondisi fermentasi tempe secara
 * real-time. Service ini dipanggil setiap kali data sensor baru masuk dari ESP32.
 *
 * Strategi Analisis:
 * - Menggunakan rata-rata data 15 menit terakhir (bukan 1 titik data saja)
 *   untuk menghindari false positive akibat lonjakan sesaat pada sensor.
 * - Notifikasi WhatsApp hanya dikirim SATU KALI per transisi status
 *   (active → semangit, atau semangit → failed) menggunakan Cache key.
 *
 * ┌──────────────────────────────────────────────────────────────┐
 * │  STATUS MACHINE                                              │
 * │                                                              │
 * │  active  ──[Rule 1]──►  semangit  ──[Rule 2]──►  failed     │
 * │                              │                               │
 * │                         [Rule 2]──────────────────────────► │
 * └──────────────────────────────────────────────────────────────┘
 */
class FermentationPredictionService
{
    // =========================================================================
    // AMBANG BATAS (THRESHOLDS) — Mudah dikustomisasi di sini
    // =========================================================================

    /**
     * Jendela waktu (menit) untuk mengambil data historis batch.
     * Data di luar jendela ini tidak diikutsertakan dalam perhitungan tren.
     */
    protected int $trendWindowMinutes = 15;

    /**
     * Jumlah minimum data dalam jendela waktu sebelum analisis dijalankan.
     * Mencegah false-positive di awal batch saat data masih sedikit.
     */
    protected int $minDataPointsRequired = 3;

    // --- Rule 1: Kondisi "Semangit" (Peringatan Kuning) ---

    /**
     * Rata-rata suhu internal (°C) dalam jendela waktu yang memicu status semangit.
     * Suhu normal fermentasi eksotermik adalah 30-36°C. 
     * Di atas 36.5°C -> jamur kepanasan / potensi semangit.
     */
    protected float $semangitTempAvg = 36.5;

    /**
     * Level gas amonia (ppm) sesaat yang memicu status semangit.
     * Amonia normal saat fermentasi adalah 43-50 ppm.
     * Amonia tinggi mengindikasikan proteolisis berlebih (tempe mulai busuk).
     */
    protected float $semangitAmoniaInstant = 100.0;

    // --- Rule 2: Kondisi "Gagal/Busuk" (Kritis Merah) ---

    /**
     * Level gas amonia (ppm) yang mengindikasikan kegagalan total.
     * Di atas 250 ppm → degradasi protein massif, tempe tidak layak.
     */
    protected float $failedAmoniaThreshold = 250.0;

    /**
     * Selisih suhu (°C): jika suhu internal DROP di bawah suhu ruangan sebesar
     * nilai ini SETELAH batch berjalan > 1 jam, dianggap proses fermentasi mati.
     * Contoh: internal_temp = 26°C, room_temp = 30°C → drop = -4°C → gagal.
     */
    protected float $failedTempDropBelow = -3.0;

    /**
     * Waktu minimum (jam) batch berjalan sebelum rule "suhu drop" bisa aktif.
     * Mencegah false-positive di awal batch (tempe memang belum panas).
     */
    protected int $failedTempDropMinBatchHours = 1;

    // =========================================================================
    // ENTRY POINT
    // =========================================================================

    /**
     * Analisis kondisi batch berdasarkan log sensor terbaru.
     *
     * @param  FermentationBatch  $batch     Batch aktif yang sedang berjalan
     * @param  SensorLog          $latestLog Log sensor yang baru saja disimpan
     * @return void
     */
    public function analyze(FermentationBatch $batch, SensorLog $latestLog): void
    {
        // Jangan analisis batch yang sudah selesai/gagal
        if (!in_array($batch->status, ['active', 'semangit'])) {
            return;
        }

        // Ambil data historis dalam jendela waktu untuk analisis tren
        $recentLogs = SensorLog::where('batch_id', $batch->id)
            ->where('created_at', '>=', now()->subMinutes($this->trendWindowMinutes))
            ->orderBy('created_at', 'asc')
            ->get(['internal_temp', 'amonia_level', 'room_temp', 'created_at']);

        // Tunda analisis sampai ada cukup data untuk mengukur tren
        if ($recentLogs->count() < $this->minDataPointsRequired) {
            Log::info("BatchPredictor: Data belum cukup untuk analisis [{$batch->id}]", [
                'data_count' => $recentLogs->count(),
                'required'   => $this->minDataPointsRequired,
            ]);
            return;
        }

        // Hitung metrik statistik dari data tren
        $avgInternalTemp = $recentLogs->avg('internal_temp');
        $avgAmoniaLevel  = $recentLogs->avg('amonia_level');

        // Nilai sesaat dari log terbaru (untuk rule yang butuh deteksi cepat)
        $currentAmonia   = (float) $latestLog->amonia_level;
        $currentIntTemp  = (float) $latestLog->internal_temp;
        $currentRoomTemp = (float) $latestLog->room_temp;

        Log::info("BatchPredictor: Analisis batch [{$batch->id}]", [
            'status'          => $batch->status,
            'avg_temp'        => round($avgInternalTemp, 2),
            'avg_amonia'      => round($avgAmoniaLevel, 2),
            'current_amonia'  => $currentAmonia,
        ]);

        // Cek Rule 2 terlebih dahulu — kondisi kritis lebih prioritas
        if ($this->checkRuleFailed($batch, $currentAmonia, $currentIntTemp, $currentRoomTemp)) {
            $this->transitionToFailed($batch, $currentAmonia, $currentIntTemp, $currentRoomTemp);
            return;
        }

        // Cek Rule 1 — hanya jika batch masih 'active' (bukan sudah 'semangit')
        if ($batch->status === 'active') {
            if ($this->checkRuleSemangit($avgInternalTemp, $currentAmonia)) {
                $this->transitionToSemangit($batch, $avgInternalTemp, $currentAmonia);
            }
        }
    }

    // =========================================================================
    // RULES
    // =========================================================================

    /**
     * Rule 1 — Deteksi kondisi "Semangit" (Peringatan Dini).
     *
     * Kondisi terpenuhi jika SALAH SATU dari:
     * (a) Rata-rata suhu internal dalam 15 menit terakhir > ambang batas semangit
     * (b) Level amonia sesaat sudah melewati batas semangit
     */
    protected function checkRuleSemangit(float $avgTemp, float $instantAmonia): bool
    {
        $tempBreached   = $avgTemp > $this->semangitTempAvg;
        $amoniaBreached = $instantAmonia > $this->semangitAmoniaInstant;

        if ($tempBreached || $amoniaBreached) {
            Log::info('BatchPredictor: Rule 1 (Semangit) TERPENUHI', [
                'avg_temp_breach'    => $tempBreached,
                'amonia_breach'      => $amoniaBreached,
                'avg_temp'           => $avgTemp,
                'instant_amonia'     => $instantAmonia,
                'threshold_temp'     => $this->semangitTempAvg,
                'threshold_amonia'   => $this->semangitAmoniaInstant,
            ]);
            return true;
        }

        return false;
    }

    /**
     * Rule 2 — Deteksi kondisi "Gagal/Busuk" (Kritis).
     *
     * Kondisi terpenuhi jika SALAH SATU dari:
     * (a) Level amonia sesaat > ambang batas kritis (degradasi masif)
     * (b) Suhu internal DROP drastis di bawah suhu ruangan setelah batch
     *     berjalan cukup lama (fermentasi mati / tidak ada aktivitas mikroba)
     */
    protected function checkRuleFailed(
        FermentationBatch $batch,
        float $currentAmonia,
        float $currentIntTemp,
        float $currentRoomTemp
    ): bool {
        // (a) Amonia sangat tinggi → busuk total
        if ($currentAmonia > $this->failedAmoniaThreshold) {
            Log::info('BatchPredictor: Rule 2 (Gagal) — amonia kritis', [
                'amonia'    => $currentAmonia,
                'threshold' => $this->failedAmoniaThreshold,
            ]);
            return true;
        }

        // (b) Suhu internal drop di bawah suhu ruangan setelah batch cukup lama berjalan
        // Rule ini hanya aktif setelah batch berjalan > N jam (cegah false-positive awal)
        $batchAgeHours = $batch->start_time->diffInHours(now());
        if ($batchAgeHours >= $this->failedTempDropMinBatchHours) {
            $tempDiff = $currentIntTemp - $currentRoomTemp; // negatif = internal lebih dingin dari ruangan
            if ($tempDiff < $this->failedTempDropBelow) {
                Log::info('BatchPredictor: Rule 2 (Gagal) — suhu drop drastis', [
                    'internal_temp' => $currentIntTemp,
                    'room_temp'     => $currentRoomTemp,
                    'diff'          => $tempDiff,
                    'threshold'     => $this->failedTempDropBelow,
                    'batch_age_hrs' => $batchAgeHours,
                ]);
                return true;
            }
        }

        return false;
    }

    // =========================================================================
    // STATE TRANSITIONS
    // =========================================================================

    /**
     * Transisi status: active → semangit.
     * Update batch, tulis catatan prediksi, kirim WA (1x saja).
     */
    protected function transitionToSemangit(
        FermentationBatch $batch,
        float $avgTemp,
        float $currentAmonia
    ): void {
        $label     = $batch->device->label_rak ?? $batch->device->device_name ?? $batch->device->device_id;
        $timestamp = now()->format('d M Y H:i:s');

        $notes = "PERINGATAN SEMANGIT terdeteksi pada {$timestamp}.\n"
            . "Rata-rata suhu internal (15 mnt): " . round($avgTemp, 1) . "°C "
            . "(ambang batas: {$this->semangitTempAvg}°C).\n"
            . "Amonia sesaat: " . round($currentAmonia, 1) . " ppm "
            . "(ambang batas: {$this->semangitAmoniaInstant} ppm).\n"
            . "Segera periksa kondisi tempe dan pastikan sirkulasi udara memadai.";

        $batch->update([
            'status'           => 'semangit',
            'prediction_notes' => $notes,
        ]);

        Log::warning("BatchPredictor: Batch [{$batch->id}] → SEMANGIT");

        // Kirim notifikasi WA — hanya SATU KALI saat transisi ini terjadi
        $this->sendBatchTransitionNotification($batch, 'semangit', $label, $avgTemp, $currentAmonia);
    }

    /**
     * Transisi status: active/semangit → failed.
     * Update batch, tulis catatan, kirim WA (1x saja).
     */
    protected function transitionToFailed(
        FermentationBatch $batch,
        float $currentAmonia,
        float $currentIntTemp,
        float $currentRoomTemp
    ): void {
        $label     = $batch->device->label_rak ?? $batch->device->device_name ?? $batch->device->device_id;
        $timestamp = now()->format('d M Y H:i:s');
        $tempDiff  = round($currentIntTemp - $currentRoomTemp, 1);

        $notes = "KEGAGALAN FERMENTASI terdeteksi pada {$timestamp}.\n"
            . "Amonia sesaat: " . round($currentAmonia, 1) . " ppm "
            . "(ambang kritis: {$this->failedAmoniaThreshold} ppm).\n"
            . "Suhu internal: " . round($currentIntTemp, 1) . "°C | "
            . "Suhu ruangan: " . round($currentRoomTemp, 1) . "°C "
            . "(selisih: {$tempDiff}°C).\n"
            . "Tempe kemungkinan sudah busuk atau proses fermentasi berhenti total. "
            . "Segera keluarkan dari rak untuk mencegah kontaminasi silang.";

        $batch->update([
            'status'           => 'failed',
            'end_time'         => now(), // Otomatis catat waktu gagal
            'prediction_notes' => $notes,
        ]);

        Log::error("BatchPredictor: Batch [{$batch->id}] → FAILED");

        // Kirim notifikasi WA — hanya SATU KALI saat transisi ini terjadi
        $this->sendBatchTransitionNotification($batch, 'failed', $label, $currentAmonia, $currentIntTemp);
    }

    // =========================================================================
    // NOTIFIKASI WHATSAPP (Anti-Spam via Cache)
    // =========================================================================

    /**
     * Kirim notifikasi WhatsApp saat terjadi perpindahan status batch.
     *
     * Menggunakan Cache key unik per batch + status untuk memastikan
     * notifikasi hanya dikirim SATU KALI per transisi, tidak berulang
     * meskipun data sensor terus masuk setiap detik.
     *
     * Cache key: "batch_wa_notif_{batch_id}_{target_status}"
     * TTL: 24 jam (lebih dari cukup — batch umumnya selesai dalam 36-48 jam)
     */
    protected function sendBatchTransitionNotification(
        FermentationBatch $batch,
        string $targetStatus,
        string $deviceLabel,
        float $valueA,
        float $valueB
    ): void {
        // Key unik: mencegah pengiriman WA berulang untuk transisi yang sama
        $cacheKey = "batch_wa_notif_{$batch->id}_{$targetStatus}";

        if (Cache::has($cacheKey)) {
            Log::info("BatchPredictor: WA sudah dikirim sebelumnya untuk transisi ini", [
                'batch_id'      => $batch->id,
                'target_status' => $targetStatus,
            ]);
            return;
        }

        $user = $batch->device->user ?? null;
        if (!$user || !$user->whatsapp_number) {
            Log::warning("BatchPredictor: User tidak memiliki nomor WA, notifikasi dilewati", [
                'batch_id' => $batch->id,
            ]);
            return;
        }

        $message = $this->buildBatchNotificationMessage(
            $targetStatus,
            $deviceLabel,
            $batch->device->device_id,
            $batch->id,
            $valueA,
            $valueB
        );

        // Kirim WA menggunakan low-level sendMessage dari WhatsAppService
        $whatsApp = app(WhatsAppService::class);
        $sent     = $whatsApp->sendMessage($user->whatsapp_number, $message);

        if ($sent) {
            // Tandai bahwa notifikasi untuk transisi ini sudah dikirim (TTL 24 jam)
            Cache::put($cacheKey, true, 86400);

            Log::info("BatchPredictor: Notifikasi WA terkirim", [
                'batch_id'      => $batch->id,
                'target_status' => $targetStatus,
                'phone'         => $user->whatsapp_number,
            ]);
        }
    }

    /**
     * Bangun pesan WhatsApp berdasarkan status batch yang baru ditransisikan.
     */
    protected function buildBatchNotificationMessage(
        string $status,
        string $deviceLabel,
        string $deviceId,
        int    $batchId,
        float  $valueA,
        float  $valueB
    ): string {
        $timestamp  = now()->format('d M Y H:i:s');
        $batchIdStr = '#' . str_pad($batchId, 4, '0', STR_PAD_LEFT);

        return match ($status) {
            'semangit' =>
                "⚠️ *PERINGATAN: TEMPE MULAI SEMANGIT*\n\n"
                . "Rak: *{$deviceLabel}*\n"
                . "Device ID: {$deviceId}\n"
                . "Batch: {$batchIdStr}\n\n"
                . "📊 *Data Tren (15 menit terakhir):*\n"
                . "• Rata-rata Suhu Internal: *" . round($valueA, 1) . "°C*\n"
                . "  (Ambang batas: {$this->semangitTempAvg}°C)\n"
                . "• Amonia Sesaat: *" . round($valueB, 1) . " ppm*\n"
                . "  (Ambang batas: {$this->semangitAmoniaInstant} ppm)\n\n"
                . "🔧 *Tindakan yang disarankan:*\n"
                . "• Periksa sirkulasi udara pada rak\n"
                . "• Pastikan suhu ruangan tidak terlalu panas\n"
                . "• Pantau perkembangan setiap 30 menit\n\n"
                . "⏱ Waktu deteksi: {$timestamp}\n"
                . "_Pesan ini dikirim otomatis oleh sistem Mikosfy._",

            'failed' =>
                "🔴 *KRITIS: FERMENTASI GAGAL / TEMPE BUSUK*\n\n"
                . "Rak: *{$deviceLabel}*\n"
                . "Device ID: {$deviceId}\n"
                . "Batch: {$batchIdStr}\n\n"
                . "📊 *Data Sensor Kritis:*\n"
                . "• Level Amonia: *" . round($valueA, 1) . " ppm*\n"
                . "  (Batas kritis: {$this->failedAmoniaThreshold} ppm)\n"
                . "• Suhu Internal: *" . round($valueB, 1) . "°C*\n\n"
                . "❗ *TINDAKAN SEGERA DIPERLUKAN:*\n"
                . "• Keluarkan tempe dari rak sekarang\n"
                . "• Hindari kontaminasi ke batch lain\n"
                . "• Bersihkan rak sebelum produksi berikutnya\n\n"
                . "⏱ Waktu deteksi: {$timestamp}\n"
                . "_Pesan ini dikirim otomatis oleh sistem Mikosfy._",

            default =>
                "ℹ️ *UPDATE STATUS BATCH {$batchIdStr}*\n\n"
                . "Rak: *{$deviceLabel}* | Status: {$status}\n"
                . "Waktu: {$timestamp}",
        };
    }
}
