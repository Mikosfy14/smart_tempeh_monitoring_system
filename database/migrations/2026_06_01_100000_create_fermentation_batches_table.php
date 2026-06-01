<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Membuat tabel fermentation_batches untuk menyimpan data setiap
     * sesi fermentasi tempe yang dipantau oleh sistem.
     */
    public function up(): void
    {
        Schema::create('fermentation_batches', function (Blueprint $table) {
            $table->id();

            // Foreign key ke tabel devices, hapus batch jika device dihapus
            $table->foreignId('device_id')
                  ->constrained()
                  ->onDelete('cascade');

            // Waktu mulai dan selesai proses fermentasi
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();

            // Status batch fermentasi:
            // - active   : sedang berjalan
            // - completed: selesai dengan sukses
            // - semangit : terdeteksi kondisi semangit (peringatan)
            // - failed   : gagal / dibatalkan
            $table->enum('status', ['active', 'completed', 'semangit', 'failed'])
                  ->default('active');

            // Catatan atau hasil analisis prediktif (opsional)
            $table->text('prediction_notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fermentation_batches');
    }
};
