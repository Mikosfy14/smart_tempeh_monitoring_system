<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\FermentationBatch;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        // Hanya batch yang masih aktif / semangit / gagal yang bisa diakhiri
        if (!in_array($batch->status, ['active', 'semangit', 'failed'])) {
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

    /**
     * Tampilkan riwayat semua batch untuk device tertentu.
     */
    public function history(Device $device)
    {
        // Pastikan device adalah milik user yang login
        if ($device->user_id !== Auth::id()) {
            abort(403);
        }

        // Ambil data batch dengan pagination
        $batches = FermentationBatch::where('device_id', $device->id)
            ->orderBy('start_time', 'desc')
            ->paginate(15);

        return view('dashboard.batch-history', compact('device', 'batches'));
    }

    /**
     * Export log batch ke PDF dalam rentang tanggal tertentu.
     */
    public function exportPdf(Request $request, Device $device)
    {
        // Pastikan device adalah milik user yang login
        if ($device->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',
        ]);

        $batches = FermentationBatch::where('device_id', $device->id)
            ->whereBetween('start_time', [
                $request->date_from . ' 00:00:00',
                $request->date_to . ' 23:59:59',
            ])
            ->orderBy('start_time', 'asc')
            ->get();

        $dateFrom = $request->date_from;
        $dateTo   = $request->date_to;

        $pdf = Pdf::loadView('pdf.batch-report', compact('device', 'batches', 'dateFrom', 'dateTo'))
            ->setPaper('a4', 'landscape');

        $filename = "Laporan_Batch_Produksi_{$device->device_id}_{$dateFrom}_to_{$dateTo}.pdf";

        return $pdf->download($filename);
    }
    /**
     * Update status dan end_time batch.
     */
    public function update(Request $request, Device $device, FermentationBatch $batch): RedirectResponse
    {
        // Pastikan device adalah milik user yang login
        if ($device->user_id !== Auth::id()) {
            abort(403);
        }

        // Pastikan batch memang milik device ini
        if ($batch->device_id !== $device->id) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:active,completed,failed,semangit',
            'end_time' => 'nullable|date|after_or_equal:' . $batch->start_time,
            'prediction_notes' => 'nullable|string',
        ]);

        $status = $request->status;
        $endTime = $request->end_time;

        if ($status === 'active') {
            // Prevent Overlap
            $hasOtherActiveBatch = \App\Models\FermentationBatch::where('device_id', $device->id)
                ->where('id', '!=', $batch->id)
                ->where('status', 'active')
                ->exists();

            if ($hasOtherActiveBatch) {
                return redirect()->back()->with('error', 'Tidak dapat mengaktifkan batch ini. Perangkat sedang menjalankan proses fermentasi lain.');
            }

            // Reset End Time
            $endTime = null;
        } else {
            // Auto-Fill End Time
            if (empty($endTime)) {
                $endTime = now();
            }
        }

        $updateData = [
            'status' => $status,
            'end_time' => $endTime,
        ];

        if ($request->has('prediction_notes')) {
            $updateData['prediction_notes'] = $request->prediction_notes;
        }

        $batch->update($updateData);

        return redirect()->back()->with('success', 'Data batch berhasil diperbarui.');
    }

    /**
     * Hapus record batch.
     */
    public function destroy(Device $device, FermentationBatch $batch): RedirectResponse
    {
        // Pastikan device adalah milik user yang login
        if ($device->user_id !== Auth::id()) {
            abort(403);
        }

        // Pastikan batch memang milik device ini
        if ($batch->device_id !== $device->id) {
            abort(403);
        }

        $batch->delete();

        return redirect()->back()->with('success', 'Data batch berhasil dihapus.');
    }
}
