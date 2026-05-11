<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the master_devices whitelist table.
     * Admin registers hardware IDs here; users claim them later.
     */
    public function up(): void
    {
        Schema::create('master_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->unique();
            $table->boolean('is_registered')->default(false);
            $table->timestamps();

            // Index for fast ESP32 polling lookups (every 1s)
            $table->index('device_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_devices');
    }
};
