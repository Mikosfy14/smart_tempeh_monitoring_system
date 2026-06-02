<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;

class AdminBatchController extends Controller
{
    public function index()
    {
        // Menggunakan relasi 'batches' sesuai dengan definisi di model Device.php
        $devices = Device::with(['user', 'batches' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->paginate(10);

        return view('admin.batches', compact('devices'));
    }

    /**
     * Update status dan end_time batch oleh Admin.
     */
    public function update(Request $request, \App\Models\FermentationBatch $batch)
    {
        $request->validate([
            'status' => 'required|in:active,completed,failed,semangit',
            'end_time' => 'nullable|date|after_or_equal:' . $batch->start_time,
            'prediction_notes' => 'nullable|string',
        ]);

        $status = $request->status;
        $endTime = $request->end_time;

        if ($status === 'active') {
            // Prevent Overlap
            $hasOtherActiveBatch = \App\Models\FermentationBatch::where('device_id', $batch->device_id)
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

        return redirect()->back()->with('success', 'Data batch berhasil diperbarui oleh Admin.');
    }

    /**
     * Hapus record batch oleh Admin.
     */
    public function destroy(\App\Models\FermentationBatch $batch)
    {
        $batch->delete();

        return redirect()->back()->with('success', 'Data batch berhasil dihapus oleh Admin.');
    }
}
