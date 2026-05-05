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
    Schema::create('sensor_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('device_id')->constrained()->onDelete('cascade');
        $table->float('internal_temp'); //Temperature Inside (DS18B20)
        $table->float('amonia_level'); //Ammonia Level (MQ-135)
        $table->float('room_temp'); //Temperature Outside (DHT22)
        $table->float('humidity'); //Humidity Outside (DHT22)    
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_logs');
    }
};
