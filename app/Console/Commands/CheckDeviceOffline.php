<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckDeviceOffline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'device:check-offline';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek status offline semua alat dan kirim notifikasi WhatsApp';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $devices = Device::whereNotNull('user_id')
            ->with(['latestLog', 'user'])
            ->get();

        $waService = new WhatsAppService();

        foreach ($devices as $device) {
            if ($device->is_online === false && $device->offline_notified_at === null) {
                $waService->sendOfflineAlert($device->user, $device);
                $device->offline_notified_at = now();
                $device->save();

                Log::info('Device offline alert sent', [
                    'device_id' => $device->device_id,
                    'user_id'   => $device->user_id,
                ]);
            } elseif ($device->is_online === true && $device->offline_notified_at !== null) {
                $waService->sendOnlineRecovery($device->user, $device);
                $device->offline_notified_at = null;
                $device->save();

                Log::info('Device online recovery sent', [
                    'device_id' => $device->device_id,
                    'user_id'   => $device->user_id,
                ]);
            }
        }

        return Command::SUCCESS;
    }
}
