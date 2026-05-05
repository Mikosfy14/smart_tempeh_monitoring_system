<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorLog extends Model
{
    protected $fillable = [
        'device_id',
        'internal_temp',
        'amonia_level',
        'room_temp',
        'humidity',
    ];

    protected function casts(): array
    {
        return [
            'internal_temp' => 'float',
            'amonia_level' => 'float',
            'room_temp' => 'float',
            'humidity' => 'float',
        ];
    }

    /**
     * Get the device that generated this log.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
