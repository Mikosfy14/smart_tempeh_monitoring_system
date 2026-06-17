<?php

namespace App\Services;

use App\Models\Device;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Cooldown duration in seconds (30 minutes).
     */
    protected int $cooldownSeconds = 1800;

    /**
     * Send a threshold alert with cooldown protection.
     * Returns true if the message was actually sent.
     */
    public function sendAlert(User $user, Device $device, string $alertType, float $value): bool
    {
        if (!$user->whatsapp_number) {
            Log::warning('WhatsApp: User tidak memiliki whatsapp_number', [
                'user_id'   => $user->id,
                'user_name' => $user->name,
                'device_id' => $device->device_id,
                'alertType' => $alertType,
            ]);
            return false;
        }

        // Cooldown check — skip if alert already sent recently for this device + type
        $cooldownKey = "wa_alert_{$device->id}_{$alertType}";
        if (Cache::has($cooldownKey)) {
            Log::info("WhatsApp alert cooldown active", [
                'device_id' => $device->device_id,
                'type'      => $alertType,
                'remaining' => Cache::get($cooldownKey . '_expires', 'unknown'),
            ]);
            return false;
        }

        $label = $device->label_rak ?? $device->device_name ?? $device->device_id;
        $message = $this->buildAlertMessage($alertType, $label, $device->device_id, $value);

        Log::info('WhatsApp: Mengirim alert', [
            'phone'     => $user->whatsapp_number,
            'device_id' => $device->device_id,
            'type'      => $alertType,
            'value'     => $value,
        ]);

        $sent = $this->sendMessage($user->whatsapp_number, $message);

        if ($sent) {
            // Set cooldown cache
            Cache::put($cooldownKey, true, $this->cooldownSeconds);
            // Mark alert as active for recovery notification
            Cache::put("alert_active_{$device->id}_{$alertType}", true, 86400); // 24h max
            Log::info('WhatsApp: Alert berhasil terkirim', [
                'phone'     => $user->whatsapp_number,
                'type'      => $alertType,
            ]);
        } else {
            Log::error('WhatsApp: Alert GAGAL terkirim', [
                'phone'     => $user->whatsapp_number,
                'type'      => $alertType,
                'device_id' => $device->device_id,
            ]);
        }

        return $sent;
    }

    /**
     * Send a "Back to Normal" recovery notification.
     * Only fires if there was a previous active alert.
     */
    public function sendRecoveryNotification(User $user, Device $device, string $alertType, float $value): bool
    {
        if (!$user->whatsapp_number) {
            return false;
        }

        $activeKey = "alert_active_{$device->id}_{$alertType}";

        // Only send recovery if there was a previous breach
        if (!Cache::has($activeKey)) {
            return false;
        }

        $label = $device->label_rak ?? $device->device_name ?? $device->device_id;
        $message = $this->buildRecoveryMessage($alertType, $label, $device->device_id, $value);

        Log::info('WhatsApp: Mengirim recovery notification', [
            'phone'     => $user->whatsapp_number,
            'type'      => $alertType,
            'device_id' => $device->device_id,
        ]);

        $sent = $this->sendMessage($user->whatsapp_number, $message);

        if ($sent) {
            Cache::forget($activeKey);
            Cache::forget("wa_alert_{$device->id}_{$alertType}");
        }

        return $sent;
    }

    /**
     * Send an offline alert when a device stops sending data.
     */
    public function sendOfflineAlert(User $user, Device $device): bool
    {
        if (!$user->whatsapp_number) {
            Log::warning('WhatsApp: Offline alert dilewati — user tidak punya WA number', [
                'user_id' => $user->id,
            ]);
            return false;
        }

        $label = $device->label_rak ?? $device->device_name ?? $device->device_id;
        $timestamp = now()->format('d M Y H:i:s');

        $message = "⚠️ *ALAT OFFLINE / KEHILANGAN SINYAL*\n\n"
            . "Rak: *{$label}*\n"
            . "Device ID: {$device->device_id}\n\n"
            . ":information_source: Alat tidak mengirim data selama lebih dari 5 menit.\n"
            . "Periksa koneksi internet dan sumber daya alat Anda.\n\n"
            . "Waktu terdeteksi: {$timestamp}";

        Log::info('WhatsApp: Mengirim offline alert', [
            'phone'     => $user->whatsapp_number,
            'device_id' => $device->device_id,
        ]);

        return $this->sendMessage($user->whatsapp_number, $message);
    }

    /**
     * Send an online recovery notification when a device comes back online.
     */
    public function sendOnlineRecovery(User $user, Device $device): bool
    {
        if (!$user->whatsapp_number) {
            return false;
        }

        $label = $device->label_rak ?? $device->device_name ?? $device->device_id;
        $timestamp = now()->format('d M Y H:i:s');

        $message = "✅ *ALAT KEMBALI ONLINE*\n\n"
            . "Rak: *{$label}*\n"
            . "Device ID: {$device->device_id}\n\n"
            . ":information_source: Alat telah berhasil terhubung kembali dan mulai mengirim data sensor.\n\n"
            . "Waktu: {$timestamp}";

        return $this->sendMessage($user->whatsapp_number, $message);
    }

    /**
     * Build alert message based on type.
     */
    protected function buildAlertMessage(string $type, string $label, string $deviceId, float $value): string
    {
        $timestamp = now()->format('d M Y H:i:s');

        return match ($type) {
            'temp' => "⚠️ *PERINGATAN SUHU TINGGI*\n\n"
                . "Rak: *{$label}*\n"
                . "Device ID: {$deviceId}\n"
                . "Suhu Internal: *{$value}°C*\n\n"
                . "Kipas pendingin telah *DINYALAKAN OTOMATIS*.\n"
                . "Waktu: {$timestamp}",

            'amonia' => "⚠️ *PERINGATAN GAS AMONIA TINGGI*\n\n"
                . "Rak: *{$label}*\n"
                . "Device ID: {$deviceId}\n"
                . "Level Amonia: *{$value} ppm*\n\n"
                . "Segera periksa kondisi fermentasi tempe Anda!\n" 
                . "Waktu: {$timestamp}",

            'humidity' => "⚠️ *PERINGATAN KELEMBAPAN TINGGI*\n\n"
                . "Rak: *{$label}*\n"
                . "Device ID: {$deviceId}\n"
                . "Kelembapan: *{$value}%*\n\n"
                . ":information_source: Kelembapan melebihi ambang batas. Periksa ventilasi.\n"
                . "Waktu: {$timestamp}",

            default => "⚠️ *PERINGATAN SENSOR*\n\n"
                . "Rak: *{$label}*\n"
                . "Device ID: {$deviceId}\n"
                . "Nilai: *{$value}*\n"
                . "Waktu: {$timestamp}",
        };
    }

    /**
     * Build recovery (back to normal) message.
     */
    protected function buildRecoveryMessage(string $type, string $label, string $deviceId, float $value): string
    {
        $timestamp = now()->format('d M Y H:i:s');
        $typeLabel = match ($type) {
            'temp'     => 'Suhu',
            'amonia'   => 'Gas Amonia',
            'humidity' => 'Kelembapan',
            default    => 'Sensor',
        };

        $valueDisplay = match ($type) {
            'temp'     => "{$value}°C",
            'amonia'   => "{$value} ppm",
            'humidity' => "{$value}%",
            default    => "{$value}",
        };

        return "✅ *{$typeLabel} KEMBALI NORMAL*\n\n"
            . "Rak: *{$label}*\n"
            . "Device ID: {$deviceId}\n"
            . "{$typeLabel} saat ini: *{$valueDisplay}*\n\n"
            . ":information_source: Kondisi sudah kembali dalam batas aman.\n"
            . "Waktu: {$timestamp}";
    }

    /**
     * Low-level: send a WhatsApp message via local Node.js WA Gateway
     * (whatsapp-web.js running on the same VPS).
     *
     * Gateway URL dikonfigurasi via WA_GATEWAY_URL env variable.
     * Default: http://localhost:3000/api/send
     *
     * Pastikan Node.js gateway SUDAH running sebelum Laravel mengirim.
     * Cek dengan: curl http://localhost:3000/api/send
     */
    public function sendMessage(string $phone, string $message): bool
    {
        $gatewayUrl = config('services.wa_gateway.url', 'http://127.0.0.1:3000/send-message');

        Log::info('WhatsApp: Mencoba kirim via Node.js gateway', [
            'phone'   => $phone,
            'gateway' => $gatewayUrl,
        ]);

        try {
            $response = Http::timeout(10)->post($gatewayUrl, [
                'number'  => $phone,  // Field 'number' sesuai format gateway Node.js
                'message' => $message,
            ]);

            Log::info('WhatsApp: Response dari gateway', [
                'phone'       => $phone,
                'gateway'     => $gatewayUrl,
                'http_status' => $response->status(),
                'body'        => $response->body(),
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp: ✅ Terkirim via Node.js gateway', [
                    'phone' => $phone,
                ]);
                return true;
            }

            // Gateway merespons tapi bukan 2xx
            Log::error('WhatsApp: ❌ Gateway merespons error', [
                'phone'       => $phone,
                'http_status' => $response->status(),
                'body'        => $response->body(),
            ]);
            return false;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('WhatsApp: ❌ Gateway tidak dapat dihubungi (connection refused)', [
                'phone'   => $phone,
                'gateway' => $gatewayUrl,
                'error'   => $e->getMessage(),
                'hint'    => 'Pastikan Node.js WA gateway sudah running di VPS!',
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('WhatsApp: ❌ Exception saat mengirim', [
                'phone'   => $phone,
                'gateway' => $gatewayUrl,
                'error'   => $e->getMessage(),
                'class'   => get_class($e),
            ]);
            return false;
        }
    }
}
