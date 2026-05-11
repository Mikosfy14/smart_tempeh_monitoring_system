<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MasterDevice extends Model
{
    protected $fillable = [
        'device_id',
        'is_registered',
    ];

    protected $casts = [
        'is_registered' => 'boolean',
    ];

    /**
     * Get the linked device record (if claimed by a user).
     */
    public function device(): HasOne
    {
        return $this->hasOne(Device::class, 'device_id', 'device_id');
    }
}
