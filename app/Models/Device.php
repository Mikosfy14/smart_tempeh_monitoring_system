<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    protected $fillable = [
        'user_id',
        'device_name',
        'device_id',
        'label_rak',
        'operation_mode',
        'fan_status',
        'temp_threshold',
        'amonia_threshold',
        'humidity_threshold',
        'offline_notified_at',
    ];

    protected function casts(): array
    {
        return [
            'temp_threshold'     => 'float',
            'amonia_threshold'   => 'float',
            'humidity_threshold' => 'float',
            'offline_notified_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns this device.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all sensor logs for this device.
     */
    public function sensorLogs(): HasMany
    {
        return $this->hasMany(SensorLog::class);
    }

    /**
     * Get the latest sensor log for this device.
     */
    public function latestLog()
    {
        return $this->hasOne(SensorLog::class)->latestOfMany();
    }

    /**
     * Get the master device record.
     */
    public function masterDevice()
    {
        return $this->belongsTo(MasterDevice::class, 'device_id', 'device_id');
    }

    /**
     * Check if the device is online (has sent data within the last 5 minutes).
     */
    public function getIsOnlineAttribute(): bool
    {
        if (!$this->latestLog) {
            return false;
        }

        return $this->latestLog->created_at->greaterThanOrEqualTo(now()->subMinutes(5));
    }

    /**
     * Get the status of each sensor on this device.
     */
    public function getSensorStatusAttribute(): array
    {
        $log = $this->latestLog;

        if (!$log) {
            return [
                'ds18b20' => 'error',
                'dht22'   => 'error',
                'mq135'   => 'error',
                'relay'   => $this->fan_status,
            ];
        }

        // DS18B20: error if null, <= -126, or == 85
        $ds18b20 = 'ok';
        if ($log->internal_temp === null || $log->internal_temp <= -126 || $log->internal_temp == 85) {
            $ds18b20 = 'error';
        }

        // DHT22: error if either room_temp or humidity is null or <= -126
        $dht22 = 'ok';
        if ($log->room_temp === null || $log->room_temp <= -126 || $log->humidity === null || $log->humidity <= -126) {
            $dht22 = 'error';
        }

        // MQ135: error if null or < 0
        $mq135 = 'ok';
        if ($log->amonia_level === null || $log->amonia_level < 0) {
            $mq135 = 'error';
        }

        return [
            'ds18b20' => $ds18b20,
            'dht22'   => $dht22,
            'mq135'   => $mq135,
            'relay'   => $this->fan_status,
        ];
    }
}
