<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sensor_logs', function (Blueprint $table) {
            $table->dropForeign(['device_id']);
            $table->unsignedBigInteger('device_id')->nullable()->change();
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('set null');
        });

        Schema::table('fermentation_batches', function (Blueprint $table) {
            $table->dropForeign(['device_id']);
            $table->unsignedBigInteger('device_id')->nullable()->change();
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fermentation_batches', function (Blueprint $table) {
            $table->dropForeign(['device_id']);
            $table->unsignedBigInteger('device_id')->nullable(false)->change();
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
        });

        Schema::table('sensor_logs', function (Blueprint $table) {
            $table->dropForeign(['device_id']);
            $table->unsignedBigInteger('device_id')->nullable(false)->change();
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
        });
    }
};
