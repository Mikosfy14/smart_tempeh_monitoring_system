<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add per-device threshold columns for dynamic alerts,
     * and add indexes to sensor_logs for fast chart/PDF queries.
     */
    public function up(): void
    {
        // Add threshold columns to devices
        Schema::table('devices', function (Blueprint $table) {
            $table->float('temp_threshold')->default(35.0)->after('fan_status');
            $table->float('amonia_threshold')->default(25.0)->after('temp_threshold');
            $table->float('humidity_threshold')->default(90.0)->after('amonia_threshold');
        });

        // Add indexes to sensor_logs for fast querying
        Schema::table('sensor_logs', function (Blueprint $table) {
            $table->index('device_id');
            $table->index('created_at');
            $table->index(['device_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn(['temp_threshold', 'amonia_threshold', 'humidity_threshold']);
        });

        Schema::table('sensor_logs', function (Blueprint $table) {
            $table->dropIndex(['device_id', 'created_at']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['device_id']);
        });
    }
};
