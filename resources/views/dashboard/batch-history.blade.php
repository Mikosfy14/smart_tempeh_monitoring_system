@extends('layouts.app')

@section('title', 'Riwayat Batch Fermentasi — ' . ($device->label_rak ?? $device->device_name))

@section('content')
<div class="stagger-children">

    {{-- Header & Back Button --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 relative gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('device.detail', $device->id) }}" class="btn btn-secondary btn-sm" id="btn-back-detail">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                Kembali
            </a>
            <div>
                <h1 class="text-2xl font-bold">Riwayat Batch Produksi</h1>
                <p class="text-sm mt-1" style="color: var(--color-text-muted);">Alat: {{ $device->label_rak ?? $device->device_name }} ({{ $device->device_id }})</p>
            </div>
        </div>
    </div>

    {{-- Info Card --}}
    <div class="card-static mb-6" style="background: rgba(14, 165, 233, 0.05); border-color: rgba(14, 165, 233, 0.2);">
        <div class="flex gap-4">
            <div style="color: var(--color-accent-blue); padding-top: 2px;">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold" style="color: var(--color-text-primary);">Manajemen Riwayat Produksi</h3>
                <p class="text-xs mt-1" style="color: var(--color-text-muted); line-height: 1.6;">
                    Halaman ini menampilkan seluruh riwayat sesi fermentasi (batch) yang pernah dijalankan pada rak ini. 
                    Setiap data sensor yang tercatat akan dikaitkan dengan batch aktifnya. Jika ada peringatan semangit atau kegagalan yang terdeteksi AI, 
                    catatan prediksinya akan tersimpan secara permanen di sini.
                </p>
            </div>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="card-static">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-base font-bold">Daftar Batch</h2>
            <span class="badge badge-muted">{{ $batches->total() }} Total Batch</span>
        </div>

        <div class="history-table-wrapper">
            <table class="history-table w-full text-left" id="batch-table">
                <thead>
                    <tr>
                        <th style="padding: 12px; font-size: 0.75rem; color: var(--color-text-muted); border-bottom: 1px solid var(--color-border-card);">ID BATCH</th>
                        <th style="padding: 12px; font-size: 0.75rem; color: var(--color-text-muted); border-bottom: 1px solid var(--color-border-card);">WAKTU MULAI</th>
                        <th style="padding: 12px; font-size: 0.75rem; color: var(--color-text-muted); border-bottom: 1px solid var(--color-border-card);">WAKTU SELESAI</th>
                        <th style="padding: 12px; font-size: 0.75rem; color: var(--color-text-muted); border-bottom: 1px solid var(--color-border-card);">DURASI</th>
                        <th style="padding: 12px; font-size: 0.75rem; color: var(--color-text-muted); border-bottom: 1px solid var(--color-border-card);">STATUS</th>
                        <th style="padding: 12px; font-size: 0.75rem; color: var(--color-text-muted); border-bottom: 1px solid var(--color-border-card);">CATATAN SISTEM</th>
                        <th style="padding: 12px; font-size: 0.75rem; color: var(--color-text-muted); border-bottom: 1px solid var(--color-border-card);">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $batch)
                    <tr style="border-bottom: 1px solid var(--color-border-card); transition: background 0.2s;">
                        <td style="padding: 14px 12px;">
                            <span class="font-mono text-sm font-semibold" style="color: var(--color-text-primary);">#{{ str_pad($batch->id, 4, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td style="padding: 14px 12px;">
                            <span class="text-sm font-medium">{{ $batch->start_time->format('d M Y') }}</span><br>
                            <span class="text-xs" style="color: var(--color-text-muted);">{{ $batch->start_time->format('H:i:s') }}</span>
                        </td>
                        <td style="padding: 14px 12px;">
                            @if($batch->end_time)
                                <span class="text-sm font-medium">{{ $batch->end_time->format('d M Y') }}</span><br>
                                <span class="text-xs" style="color: var(--color-text-muted);">{{ $batch->end_time->format('H:i:s') }}</span>
                            @else
                                <span class="text-xs italic" style="color: var(--color-text-muted);">Masih berjalan...</span>
                            @endif
                        </td>
                        <td style="padding: 14px 12px;">
                            <span class="text-sm font-semibold" style="color: var(--color-text-primary);">{{ number_format($batch->duration_hours, 1) }} Jam</span>
                        </td>
                        <td style="padding: 14px 12px;">
                            @if($batch->status === 'active')
                                <span class="badge" style="background: rgba(52, 211, 153, 0.15); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.3);">🟢 Aktif</span>
                            @elseif($batch->status === 'semangit')
                                <span class="badge" style="background: rgba(251, 191, 36, 0.15); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.3);">⚠️ Semangit</span>
                            @elseif($batch->status === 'failed')
                                <span class="badge" style="background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.35);">🔴 Gagal</span>
                            @elseif($batch->status === 'completed')
                                <span class="badge" style="background: rgba(148, 163, 184, 0.15); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.3);">✓ Selesai</span>
                            @endif
                        </td>
                        <td style="padding: 14px 12px; max-width: 300px;">
                            @if($batch->prediction_notes)
                                <div class="text-xs line-clamp-3" style="color: var(--color-text-secondary); line-height: 1.5; white-space: pre-line;" title="{{ $batch->prediction_notes }}">
                                    {{ $batch->prediction_notes }}
                                </div>
                            @else
                                <span class="text-xs italic" style="color: var(--color-text-muted); opacity: 0.6;">- Tidak ada catatan -</span>
                            @endif
                        </td>
                        <td style="padding: 14px 12px;">
                            <div class="flex items-center gap-2">
                                @if($batch->status === 'active')
                                    <span class="text-xs" style="color: var(--color-text-muted); opacity: 0.7;">Kelola di Detail Alat</span>
                                @else
                                    <button type="button" class="px-3 py-1.5 text-xs rounded-md font-bold text-white bg-gray-700 hover:bg-gray-600 transition-colors border-none cursor-pointer" onclick="openEditBatchModal({{ $batch->id }}, '{{ $batch->status }}', '{{ $batch->end_time ? $batch->end_time->format('Y-m-d\TH:i') : '' }}', `{{ addslashes($batch->prediction_notes ?? '') }}`, '{{ route('batch.update', ['device' => $device->id, 'batch' => $batch->id]) }}')">
                                        Edit Batch
                                    </button>
                                    <form id="delete-batch-{{ $batch->id }}" action="{{ route('batch.destroy', ['device' => $device->id, 'batch' => $batch->id]) }}" method="POST" class="m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="px-3 py-1.5 text-xs rounded-md font-bold text-white bg-red-600 hover:bg-red-700 transition-colors border-none cursor-pointer" onclick="confirmDeleteBatch({{ $batch->id }})">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 3rem; color: var(--color-text-muted);">
                            <div class="flex flex-col items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mb-3" style="opacity: 0.2;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <span>Belum ada riwayat batch yang tercatat untuk alat ini.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Links --}}
        @if($batches->hasPages())
        <div class="mt-6 pt-4 border-t" style="border-color: var(--color-border-card);">
            {{ $batches->links() }}
        </div>
        @endif
    </div>

</div>

{{-- EDIT BATCH MODAL --}}
<div class="modal-overlay" id="edit-batch-modal">
    <div class="modal-content" style="max-width: 500px; text-align: left;">
        <h3 class="text-lg font-bold mb-4">Edit Data Batch</h3>
        <form id="edit-batch-form" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label class="form-label">Status</label>
                <select name="status" id="edit-batch-status" class="form-input" required>
                    <option value="active">Active</option>
                    <option value="completed">Completed</option>
                    <option value="failed">Failed</option>
                    <option value="semangit">Semangit</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="form-label">Waktu Selesai (End Time)</label>
                <input type="datetime-local" name="end_time" id="edit-batch-endtime" class="form-input">
                <p class="text-xs mt-1" style="color: var(--color-text-muted);">Kosongkan jika batch masih berjalan (active/semangit).</p>
            </div>

            <div class="mb-6">
                <label class="form-label">Catatan / Prediksi (prediction_notes)</label>
                <textarea name="prediction_notes" id="edit-batch-notes" class="form-input" rows="5" placeholder="Masukkan catatan..."></textarea>
            </div>
            
            <div class="flex gap-3">
                <button type="button" class="btn btn-secondary flex-1" onclick="closeEditBatchModal()">Batal</button>
                <button type="submit" class="btn btn-primary flex-1">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openEditBatchModal(batchId, status, endTime, notes, formAction) {
        document.getElementById('edit-batch-form').action = formAction;
        document.getElementById('edit-batch-status').value = status;
        document.getElementById('edit-batch-endtime').value = endTime;
        document.getElementById('edit-batch-notes').value = notes;
        
        document.getElementById('edit-batch-modal').classList.add('active');
    }

    function closeEditBatchModal() {
        document.getElementById('edit-batch-modal').classList.remove('active');
    }

    function confirmDeleteBatch(batchId) {
        if (typeof Swal === 'undefined') {
            if (confirm('Yakin ingin menghapus data batch ini?')) {
                document.getElementById('delete-batch-' + batchId).submit();
            }
            return;
        }

        Swal.fire({
            title: 'Hapus Riwayat Batch?',
            text: 'Data riwayat produksi ini akan dihapus secara permanen beserta catatannya.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#334155',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            background: 'var(--color-bg-card, #1e293b)',
            color: 'var(--color-text-primary, #f1f5f9)',
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-batch-' + batchId).submit();
            }
        });
    }
</script>
@endpush
