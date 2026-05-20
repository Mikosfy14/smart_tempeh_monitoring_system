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

        $sent = $this->sendMessage($user->whatsapp_number, $message);

        if ($sent) {
            // Set cooldown cache
            Cache::put($cooldownKey, true, $this->cooldownSeconds);
            // Mark alert as active for recovery notification
            Cache::put("alert_active_{$device->id}_{$alertType}", true, 86400); // 24h max
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

        $sent = $this->sendMessage($user->whatsapp_number, $message);

        if ($sent) {
            // Clear active alert and cooldown
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
            return false;
        }

        $label = $device->label_rak ?? $device->device_name ?? $device->device_id;
        $timestamp = now()->format('d M Y H:i:s');

        $message = "⚠️ *ALAT OFFLINE / KEHILANGAN SINYAL*\n\n"
            . "Rak: *{$label}*\n"
            . "Device ID: {$device->device_id}\n\n"
            . "Alat tidak mengirim data selama lebih dari 5 menit.\n"
            . "Periksa koneksi internet dan sumber daya alat Anda.\n\n"
            . "Waktu terdeteksi: {$timestamp}";

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
            . "Alat telah berhasil terhubung kembali dan mulai mengirim data sensor.\n\n"
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
                . "🔄 Kipas pendingin telah *DINYALAKAN OTOMATIS*.\n"
                . "Waktu: {$timestamp}",

            'amonia' => "⚠️ *PERINGATAN GAS AMONIA TINGGI*\n\n"
                . "Rak: *{$label}*\n"
                . "Device ID: {$deviceId}\n"
                . "Level Amonia: *{$value} ppm*\n\n"
                . "⚡ Segera periksa kondisi fermentasi tempe Anda.\n"
                . "Waktu: {$timestamp}",

            'humidity' => "⚠️ *PERINGATAN KELEMBAPAN TINGGI*\n\n"
                . "Rak: *{$label}*\n"
                . "Device ID: {$deviceId}\n"
                . "Kelembapan: *{$value}%*\n\n"
                . "💧 Kelembapan melebihi ambang batas. Periksa ventilasi.\n"
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
            . "Kondisi sudah kembali dalam batas aman.\n"
            . "Waktu: {$timestamp}";
    }

    /**
     * Low-level: send a WhatsApp message via Fonnte API.
     */
    public function sendMessage(string $phone, string $message): bool
    {
        $token = config('services.fonnte.token', '');

        if (empty($token)) {
            Log::warning('WhatsApp: Fonnte API token is not configured.');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/send', [
                'target'  => $phone,
                'message' => $message,
            ]);

            Log::info('WhatsApp message sent', [
                'phone'    => $phone,
                'response' => $response->json(),
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('WhatsApp message failed', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
