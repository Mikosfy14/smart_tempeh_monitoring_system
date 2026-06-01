<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FermentationBatch extends Model
{
    protected $fillable = [
        'device_id',
        'start_time',
        'end_time',
        'status',
        'prediction_notes',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time'   => 'datetime',
        ];
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    /**
     * Dapatkan device yang menjalankan batch fermentasi ini.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Dapatkan semua sensor log yang tercatat selama batch ini berlangsung.
     */
    public function sensorLogs(): HasMany
    {
        return $this->hasMany(SensorLog::class, 'batch_id');
    }

    // =========================================================================
    // Helper / Scope
    // =========================================================================

    /**
     * Cek apakah batch ini masih aktif atau dalam kondisi semangit (belum selesai).
     */
    public function getIsActiveAttribute(): bool
    {
        return in_array($this->status, ['active', 'semangit']);
    }

    /**
     * Hitung durasi fermentasi dalam jam (null jika belum selesai).
     */
    public function getDurationHoursAttribute(): ?float
    {
        $end = $this->end_time ?? now();

        return round($this->start_time->diffInMinutes($end) / 60, 2);
    }
}
