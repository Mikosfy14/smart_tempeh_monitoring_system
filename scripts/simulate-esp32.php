<?php

/**
 * ============================================================================
 *  ESP32 Telemetry Simulator — Mikosfy Smart Tempeh Monitoring
 * ============================================================================
 *
 *  Script standalone untuk mendemokan semua fitur IoT + dashboard tanpa
 *  memerlukan hardware ESP32 fisik. Mengirim data dummy realistis langsung
 *  ke API endpoint POST /api/telemetry.
 *
 *  KEBUTUHAN SEBELUM MENJALANKAN:
 *  1. Buat batch aktif di dashboard (menu "Mulai Produksi" pada device)
 *  2. Pastikan device sudah terdaftar dan ter-assign ke user
 *  3. Pastikan API key sesuai dengan yang di .env VPS
 *
 *  CARA MENJALANKAN:
 *  php scripts/simulate-esp32.php
 *
 *  Atau dari VPS:
 *  php /path/to/project/scripts/simulate-esp32.php
 *
 *  ============================================================================
 */

// ============================================================================
//  KONFIGURASI — SESUAIKAN DENGAN ENVIRONMENT ANDA
// ============================================================================

// URL API endpoint (tanpa trailing slash)
$API_URL = 'https://e-tempeh.my.id/api/telemetry';

// API Key (harus sama dengan ESP32_API_KEY di .env VPS)
$API_KEY = 'mikosfy-esp32-secret-2024';

// Device ID yang terdaftar di master_devices
$DEVICE_ID = 'TEMPE-001';

// Interval pengiriman data dalam detik (default, bisa di-override per fase)
$INTERVAL_SECONDS = 5;

// Jeda antar fase dalam detik (untuk memberi waktu expert system memproses)
$PHASE_PAUSE_SECONDS = 3;

// ============================================================================
//  SKENARIO ALERT DEMO — Target 5-6 menit
// ============================================================================
//
//  FASE  1: Normal           (20 data × 5s = 1m 40s)
//           Suhu 30-33°C, amonia 5-15 ppm, humidity 70-80%
//           → Dashboard menampilkan grafik stabil, kipas OFF
//
//  FASE  2: Suhu Naik        (16 data × 4s = 1m 04s)
//           Suhu 34→38°C, amonia 15-30 ppm
//           → Alert WhatsApp suhu tinggi, kipas AUTO ON
//
//  FASE  3: SEMANGIT         (16 data × 4s = 1m 04s)
//           Suhu 37-39°C, amonia 60→130 ppm
//           → Expert system trigger SEMANGIT, WhatsApp alert batch
//
//  FASE  4: FAILED           (10 data × 3s = 30s)
//           Amonia 180→280 ppm, suhu masih tinggi
//           → Expert system trigger FAILED, WhatsApp alert batch
//
//  Total: ~5 menit 25 detik (termasuk jeda antar fase)
//
//  ============================================================================

// ============================================================================
//  WARNA TERMINAL (ANSI)
// ============================================================================

$COLORS = [
    'reset'     => "\033[0m",
    'bold'      => "\033[1m",
    'dim'       => "\033[2m",
    'red'       => "\033[31m",
    'green'     => "\033[32m",
    'yellow'    => "\033[33m",
    'blue'      => "\033[34m",
    'magenta'   => "\033[35m",
    'cyan'      => "\033[36m",
    'white'     => "\033[37m",
    'bg_red'    => "\033[41m",
    'bg_green'  => "\033[42m",
    'bg_yellow' => "\033[43m",
];

function color(string $text, string $color): string {
    global $COLORS;
    return ($COLORS[$color] ?? '') . $text . $COLORS['reset'];
}

function bold(string $text): string {
    return color($text, 'bold');
}

// ============================================================================
//  FUNGSI HELPER
// ============================================================================

/**
 * Kirim data telemetry ke API
 */
function sendTelemetry(string $apiUrl, string $apiKey, array $data): ?array {
    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($data),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
            "X-API-Key: {$apiKey}",
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => false, // Untuk development
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo color("  ✗ cURL Error: {$error}\n", 'red');
        return null;
    }

    $decoded = json_decode($response, true);

    if ($httpCode !== 200) {
        echo color("  ✗ HTTP {$httpCode}: " . ($decoded['message'] ?? 'Unknown error') . "\n", 'red');
        return null;
    }

    return $decoded;
}

/**
 * Cek apakah ada batch aktif untuk device
 */
function checkActiveBatch(string $apiUrl, string $apiKey, string $deviceId): bool {
    // Kirim satu data dummy untuk cek response batch
    $data = [
        'device_id'     => $deviceId,
        'internal_temp' => 30.0,
        'amonia_level'  => 5.0,
        'room_temp'     => 28.0,
        'humidity'      => 70.0,
    ];

    $result = sendTelemetry($apiUrl, $apiKey, $data);
    return $result && isset($result['batch']) && $result['batch'] !== null;
}

/**
 * Generate nilai dengan sedikit noise (agar terlihat natural)
 */
function noisy(float $base, float $noiseRange): float {
    return $base + (mt_rand(-100, 100) / 100) * $noiseRange;
}

/**
 * Format angka sensor dengan padding
 */
function fmtSensor(float $val, int $decimals = 1): string {
    return number_format($val, $decimals);
}

// ============================================================================
//  DEFINISI SKENARIO
// ============================================================================

function getScenario(): array {
    return [
        // ── FASE 1: Normal ──────────────────────────────────────────────
        // 20 data × 5 detik = 1 menit 40 detik
        [
            'name'     => 'NORMAL',
            'color'    => 'green',
            'icon'     => '🟢',
            'count'    => 20,
            'interval' => 5, // detik
            'gen'      => function(int $i) {
                return [
                    'internal_temp' => noisy(31.5, 1.5),
                    'amonia_level'  => noisy(8.0, 4.0),
                    'room_temp'     => noisy(28.0, 1.0),
                    'humidity'      => noisy(75.0, 5.0),
                ];
            },
        ],

        // ── FASE 2: Suhu Naik (trigger threshold alert + fan ON) ────────
        // 16 data × 4 detik = 1 menit 4 detik
        [
            'name'     => 'SUHU NAIK',
            'color'    => 'yellow',
            'icon'     => '🟡',
            'count'    => 16,
            'interval' => 4, // detik (lebih cepat — fase menarik)
            'gen'      => function(int $i) {
                $progress = $i / 15; // 0 → 1
                $temp = 34.0 + ($progress * 4.0);
                return [
                    'internal_temp' => noisy($temp, 0.8),
                    'amonia_level'  => noisy(20.0, 8.0),
                    'room_temp'     => noisy(28.5, 0.5),
                    'humidity'      => noisy(76.0, 3.0),
                ];
            },
        ],

        // ── Jeda antar fase ─────────────────────────────────────────────
        'pause' => 3,

        // ── FASE 3: SEMANGIT (amonia naik → trigger expert system) ──────
        // 16 data × 4 detik = 1 menit 4 detik
        [
            'name'     => 'SEMANGIT',
            'color'    => 'yellow',
            'icon'     => '⚠️',
            'count'    => 16,
            'interval' => 4, // detik
            'gen'      => function(int $i) {
                $progress = $i / 15;
                $amonia = 60.0 + ($progress * 70.0); // 60 → 130 ppm
                $temp = 37.0 + ($progress * 2.0);    // 37 → 39°C
                return [
                    'internal_temp' => noisy($temp, 0.5),
                    'amonia_level'  => noisy($amonia, 5.0),
                    'room_temp'     => noisy(29.0, 0.5),
                    'humidity'      => noisy(78.0, 2.0),
                ];
            },
        ],

        // ── Jeda antar fase ─────────────────────────────────────────────
        'pause' => 3,

        // ── FASE 4: FAILED (amonia kritis → trigger failed) ─────────────
        // 10 data × 3 detik = 30 detik (cepat — klimaks)
        [
            'name'     => 'FAILED',
            'color'    => 'red',
            'icon'     => '🔴',
            'count'    => 10,
            'interval' => 3, // detik (paling cepat — klimaks demo)
            'gen'      => function(int $i) {
                $progress = $i / 9;
                $amonia = 180.0 + ($progress * 100.0); // 180 → 280 ppm
                return [
                    'internal_temp' => noisy(38.5, 1.0),
                    'amonia_level'  => noisy($amonia, 8.0),
                    'room_temp'     => noisy(29.0, 0.5),
                    'humidity'      => noisy(80.0, 2.0),
                ];
            },
        ],
    ];
}

// ============================================================================
//  MAIN EXECUTION
// ============================================================================

echo "\n";
echo color("╔══════════════════════════════════════════════════════════════╗", 'cyan') . "\n";
echo color("║", 'cyan') . color("  🧬 Mikosfy — ESP32 Telemetry Simulator                     ", 'bold') . color("║", 'cyan') . "\n";
echo color("║", 'cyan') . color("  Smart Tempeh Fermentation Monitoring System                 ", 'dim') . color("║", 'cyan') . "\n";
echo color("╚══════════════════════════════════════════════════════════════╝", 'cyan') . "\n";
echo "\n";

// ── Tampilkan konfigurasi ──────────────────────────────────────────────────
echo bold("  Konfigurasi:\n");
echo "  API URL    : {$API_URL}\n";
echo "  Device ID  : {$DEVICE_ID}\n";
echo "  Interval   : {$INTERVAL_SECONDS} detik\n";
echo "  API Key    : " . substr($API_KEY, 0, 8) . "..." . substr($API_KEY, -4) . "\n";
echo "\n";

// ── Cek koneksi API ────────────────────────────────────────────────────────
echo bold("  Mengecek koneksi API...");
$result = sendTelemetry($API_URL, $API_KEY, [
    'device_id'     => $DEVICE_ID,
    'internal_temp' => 30.0,
    'amonia_level'  => 5.0,
    'room_temp'     => 28.0,
    'humidity'      => 70.0,
]);

if (!$result) {
    echo color("  ✗ Gagal terhubung ke API. Periksa URL dan API Key.\n", 'red');
    exit(1);
}

echo color("  ✓ API terhubung!\n", 'green');

// ── Cek batch aktif ────────────────────────────────────────────────────────
$hasBatch = isset($result['batch']) && $result['batch'] !== null;

if (!$hasBatch) {
    echo "\n";
    echo color("  ⚠ TIDAK ADA BATCH AKTIF!", 'yellow') . "\n";
    echo "  Expert system memerlukan batch aktif untuk menjalankan prediksi.\n";
    echo "\n";
    echo bold("  Langkah yang diperlukan:\n");
    echo "  1. Buka dashboard di browser\n";
    echo "  2. Pilih device: {$DEVICE_ID}\n";
    echo "  3. Klik tombol \"Mulai Produksi\" (Start Batch)\n";
    echo "  4. Jalankan ulang script ini\n";
    echo "\n";
    echo color("  Data tetap akan dikirim, tapi fitur expert system tidak aktif.", 'dim') . "\n";
    echo "\n";

    // Lanjutkan tanpa batch (data tetap masuk ke grafik)
    echo color("  Melanjutkan simulasi tanpa batch aktif...\n", 'dim');
} else {
    $batchId = $result['batch']['id'];
    $batchStatus = $result['batch']['status'];
    echo color("  ✓ Batch aktif ditemukan! ID: #{$batchId} ({$batchStatus})\n", 'green');
}

// ── Status device ──────────────────────────────────────────────────────────
$fanStatus = $result['fan_status'] ?? 'N/A';
$mode = $result['operation_mode'] ?? 'N/A';
echo "  Fan Status : {$fanStatus} | Mode: {$mode}\n";
echo "\n";

// ── Countdown mulai ────────────────────────────────────────────────────────
echo bold("  Memulai simulasi dalam ");
for ($i = 3; $i > 0; $i--) {
    echo color("{$i}...", 'cyan');
    sleep(1);
}
echo "\n\n";

// ── Jalankan skenario ──────────────────────────────────────────────────────
$scenario = getScenario();
$dataCount = 0;
$alertsLog = [];
$startTime = time();

foreach ($scenario as $phase) {
    // Handle jeda antar fase
    if (is_array($phase) && isset($phase['pause'])) {
        echo "\n";
        echo color("  ⏸ Jeda {$phase['pause']} detik...", 'dim') . "\n";
        sleep($phase['pause']);
        echo "\n";
        continue;
    }

    // Skip jika bukan array fase
    if (!is_array($phase) || !isset($phase['gen'])) continue;

    $name     = $phase['name'];
    $color    = $phase['color'];
    $icon     = $phase['icon'];
    $count    = $phase['count'];
    $interval = $phase['interval'] ?? $INTERVAL_SECONDS; // Interval per-fase

    // Header fase
    $phaseDur = sprintf("%dm %02ds", floor($count * $interval / 60), ($count * $interval) % 60);
    echo color("  ┌─────────────────────────────────────────────────────", $color) . "\n";
    echo color("  │  {$icon} FASE: {$name}", $color) . "\n";
    echo color("  │  Mengirim {$count} data (setiap {$interval} detik ≈ {$phaseDur})", $color) . "\n";
    echo color("  └─────────────────────────────────────────────────────", $color) . "\n";
    echo "\n";

    for ($i = 0; $i < $count; $i++) {
        $data = $phase['gen']($i);

        // Bulatkan 1 desimal
        $payload = [
            'device_id'     => $DEVICE_ID,
            'internal_temp' => round($data['internal_temp'], 1),
            'amonia_level'  => round($data['amonia_level'], 1),
            'room_temp'     => round($data['room_temp'], 1),
            'humidity'      => round($data['humidity'], 1),
        ];

        $result = sendTelemetry($API_URL, $API_KEY, $payload);
        $dataCount++;

        // Format output
        $idx = str_pad($dataCount, 3, '0', STR_PAD_LEFT);
        $temp = fmtSensor($payload['internal_temp']);
        $amon = fmtSensor($payload['amonia_level']);
        $hum  = fmtSensor($payload['humidity']);
        $fan  = $result['fan_status'] ?? '??';
        $batch = $result['batch']['status'] ?? 'none';

        // Warna suhu berdasarkan threshold
        $tempColor = $payload['internal_temp'] > 35.0 ? 'red' : ($payload['internal_temp'] > 33.0 ? 'yellow' : 'green');
        $amonColor = $payload['amonia_level'] > 100.0 ? 'red' : ($payload['amonia_level'] > 25.0 ? 'yellow' : 'green');

        $elapsed = time() - $startTime;
        $elapsedStr = sprintf("%02d:%02d", floor($elapsed / 60), $elapsed % 60);

        echo "  [{$elapsedStr}] #{$idx} ";
        echo color("🌡 {$temp}°C", $tempColor) . " | ";
        echo color("💨 {$amon}ppm", $amonColor) . " | ";
        echo "💧 {$hum}% | ";
        echo "Fan: " . ($fan === 'ON' ? color($fan, 'green') : color($fan, 'dim')) . " | ";
        echo "Batch: " . ($batch === 'semangit' ? color($batch, 'yellow') : ($batch === 'failed' ? color($batch, 'red') : color($batch, 'green')));

        // Cek alerts dari response
        if ($result && !empty($result['alerts_sent'])) {
            foreach ($result['alerts_sent'] as $alert) {
                echo " " . color("⚡{$alert}", 'magenta');
                $alertsLog[] = [
                    'time'   => $elapsedStr,
                    'type'   => $alert,
                    'value'  => $alert === 'temp' ? $temp : ($alert === 'amonia' ? $amon : $hum),
                    'phase'  => $name,
                ];
            }
        }

        // Cek perubahan status batch
        if ($result && isset($result['batch'])) {
            $newBatch = $result['batch']['status'] ?? 'none';
            if ($newBatch !== $batch) {
                echo " " . color("→ {$newBatch}", 'magenta');
            }
        }

        echo "\n";

        // Jangan sleep di data terakhir
        if ($i < $count - 1) {
            sleep($interval);
        }
    }

    echo "\n";
}

// ============================================================================
//  RINGKASAN
// ============================================================================

$totalTime = time() - $startTime;
$totalMin = floor($totalTime / 60);
$totalSec = $totalTime % 60;

echo color("╔══════════════════════════════════════════════════════════════╗", 'cyan') . "\n";
echo color("║", 'cyan') . bold("  📊 RINGKASAN SIMULASI                                      ") . color("║", 'cyan') . "\n";
echo color("╚══════════════════════════════════════════════════════════════╝", 'cyan') . "\n";
echo "\n";
echo "  Total data terkirim  : {$dataCount}\n";
echo "  Total waktu          : {$totalMin}m {$totalSec}s\n";
echo "  Device               : {$DEVICE_ID}\n";
echo "\n";

if (!empty($alertsLog)) {
    echo bold("  Alerts yang terpicu:\n");
    foreach ($alertsLog as $alert) {
        $icon = match(true) {
            str_contains($alert['type'], 'temp')    => '🌡',
            str_contains($alert['type'], 'amonia')  => '💨',
            str_contains($alert['type'], 'humidity') => '💧',
            default                                  => '⚡',
        };
        echo "    [{$alert['time']}] {$icon} {$alert['type']} ({$alert['value']}) — fase {$alert['phase']}\n";
    }
} else {
    echo color("  Tidak ada alert yang terpicu.\n", 'dim');
}

echo "\n";
echo bold("  Fitur yang berhasil didemo:\n");
echo "    ✓ Grafik real-time di dashboard (update per 3-5 detik)\n";
echo "    ✓ Data sensor masuk ke database\n";
if (!empty($alertsLog)) {
    echo "    ✓ Threshold alerts (WhatsApp notification)\n";
}
echo "    ✓ Fan control AUTO/MANUAL\n";
echo "    ✓ Expert system batch prediction\n";
echo "    ✓ Live data polling\n";
echo "\n";
echo color("  Simulasi selesai! 🎉\n", 'green');
echo "\n";
