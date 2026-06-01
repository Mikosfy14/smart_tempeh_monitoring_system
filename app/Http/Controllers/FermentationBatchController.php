<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\FermentationBatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class FermentationBatchController extends Controller
{
    /**
     * Mulai sesi produksi (batch) baru untuk sebuah device.
     *
     * Guard:
     * - Device harus milik user yang sedang login.
     * - Tidak boleh ada batch aktif/semangit yang masih berjalan.
     */
    public function startBatch(Device $device): RedirectResponse
    {
        // Pastikan device adalah milik user yang login
        if ($device->user_id !== Auth::id()) {
            abort(403);
        }

        // Cegah double-start: cek apakah sudah ada batch yang berjalan
        if ($device->activeBatch) {
            return redirect()
                ->route('device.detail', $device->id)
                ->with('error', 'Tidak dapat memulai produksi baru — masih ada sesi produksi yang sedang berjalan.');
        }

        // Buat batch baru
        FermentationBatch::create([
            'device_id'  => $device->id,
            'start_time' => now(),
            'status'     => 'active',
        ]);

        return redirect()
            ->route('device.detail', $device->id)
            ->with('success', 'Sesi produksi baru berhasil dimulai. Selamat memfermentasi! 🍄');
    }

    /**
     * Akhiri sesi produksi (batch) yang sedang berjalan.
     *
     * Guard:
     * - Device harus milik user yang sedang login.
     * - Batch harus milik device yang sama.
     * - Batch harus masih berstatus aktif / semangit (belum selesai).
     */
    public function endBatch(Device $device, FermentationBatch $batch): RedirectResponse
    {
        // Pastikan device adalah milik user yang login
        if ($device->user_id !== Auth::id()) {
            abort(403);
        }

        // Pastikan batch memang milik device ini
        if ($batch->device_id !== $device->id) {
            abort(403);
        }

        // Hanya batch yang masih aktif / semangit yang bisa diakhiri
        if (!in_array($batch->status, ['active', 'semangit'])) {
            return redirect()
                ->route('device.detail', $device->id)
                ->with('error', 'Sesi produksi ini sudah tidak aktif.');
        }

        // Selesaikan batch
        $batch->update([
            'status'   => 'completed',
            'end_time' => now(),
        ]);

        return redirect()
            ->route('device.detail', $device->id)
            ->with('success', 'Sesi produksi berhasil diakhiri dan dicatat sebagai selesai.');
    }
}
