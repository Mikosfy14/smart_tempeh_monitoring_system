<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan kolom batch_id (nullable) ke tabel sensor_logs
     * agar setiap log sensor dapat dikaitkan dengan satu batch fermentasi.
     * Kolom bersifat nullable karena log lama belum memiliki batch.
     */
    public function up(): void
    {
        Schema::table('sensor_logs', function (Blueprint $table) {
            // Tambahkan setelah kolom device_id agar urutan kolom rapi
            $table->foreignId('batch_id')
                  ->nullable()
                  ->after('device_id')
                  ->constrained('fermentation_batches')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sensor_logs', function (Blueprint $table) {
            // Hapus foreign key constraint terlebih dahulu, baru drop kolom
            $table->dropForeign(['batch_id']);
            $table->dropColumn('batch_id');
        });
    }
};
