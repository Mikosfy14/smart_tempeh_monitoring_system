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
    ];

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
}
