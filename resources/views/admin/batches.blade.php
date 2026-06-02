@extends('layouts.admin')

@section('title', 'Manajemen Riwayat Batch Fermentasi')


@section('content')
<div class="stagger-children">
    {{-- 1. Struktur Utama Halaman & Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold">Fermentation Batches Management</h1>
        <p class="mt-1" style="color: var(--color-text-secondary);">Monitor and manage all device fermentation batches</p>
    </div>

    @if(session('success'))
        <div class="p-4 mb-6 rounded-lg text-sm" style="background: rgba(52, 211, 153, 0.1); border: 1px solid rgba(52, 211, 153, 0.3); color: #34d399;">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="p-4 mb-6 rounded-lg text-sm" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444;">
            {{ session('error') }}
        </div>
    @endif

    {{-- 2. Outer Container (Daftar Alat / Accordion Header) --}}
    <div class="grid gap-4">
        @foreach($devices as $device)
            <div class="card-static mb-4" style="padding: 0; overflow: hidden; border: 1px solid var(--color-border-card, #334155);">
                {{-- Sisi Kiri & Kanan Card Perangkat --}}
                <div class="flex items-center justify-between p-4 cursor-pointer hover:bg-opacity-80 transition-all" onclick="toggleBatchList({{ $device->id }})" style="background: var(--color-bg-card, #1e293b);">
                    {{-- Sisi Kiri --}}
                    <div class="flex items-center gap-3">
                        <code class="text-xs px-2 py-1 rounded" style="background: var(--color-bg-primary); color: var(--color-accent-teal);">{{ $device->device_id }}</code>
                        <span class="text-sm font-semibold">— {{ $device->label_rak ?? 'Tanpa Label Rak' }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full" style="background: rgba(255,255,255,0.05); color: var(--color-text-secondary);">User: {{ $device->user->name ?? 'Belum di-assign' }}</span>
                    </div>
                    
                    {{-- Sisi Kanan --}}
                    <div class="flex items-center gap-3">
                        <span class="text-xs px-2 py-1 rounded font-semibold" style="background: var(--color-bg-primary); color: var(--color-text-secondary);">Total Batches: {{ $device->batches->count() }}</span>
                        <svg id="chevron-{{ $device->id }}" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 transition-transform duration-300" style="color: var(--color-text-muted);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

                {{-- 3. Inner Container (Tabel Riwayat Batch / Accordion Body) --}}
                <div id="batch-list-{{ $device->id }}" class="hidden px-4 pb-4 overflow-x-auto" style="border-top: 1px solid var(--color-border-card, #334155); background: var(--color-bg-primary, #0f172a); padding-top: 1rem;">
                    <table class="data-table w-full text-left border-collapse">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--color-border-card, #334155);">
                                <th class="p-3 text-xs font-semibold uppercase tracking-wider" style="color: var(--color-text-muted);">Waktu Mulai</th>
                                <th class="p-3 text-xs font-semibold uppercase tracking-wider" style="color: var(--color-text-muted);">Waktu Selesai</th>
                                <th class="p-3 text-xs font-semibold uppercase tracking-wider" style="color: var(--color-text-muted);">Status</th>
                                <th class="p-3 text-xs font-semibold uppercase tracking-wider" style="color: var(--color-text-muted);">Catatan Prediksi</th>
                                <th class="p-3 text-xs font-semibold uppercase tracking-wider" style="color: var(--color-text-muted);">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($device->batches as $batch)
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); transition: background 0.2s;" class="hover:bg-gray-800/30">
                                <td class="p-3 text-sm" style="color: var(--color-text-primary);">
                                    {{ $batch->start_time->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="p-3 text-sm" style="color: var(--color-text-primary);">
                                    @if($batch->end_time)
                                        {{ $batch->end_time->format('d/m/Y H:i:s') }}
                                    @else
                                        <em style="color: var(--color-text-muted); opacity: 0.8;">Sedang berjalan...</em>
                                    @endif
                                </td>
                                <td class="p-3">
                                    @if($batch->status === 'active')
                                        <span class="px-2 py-1 text-xs font-semibold rounded" style="background: rgba(20, 184, 166, 0.15); color: #2dd4bf;">Active</span>
                                    @elseif($batch->status === 'completed')
                                        <span class="px-2 py-1 text-xs font-semibold rounded" style="background: rgba(34, 197, 94, 0.15); color: #4ade80;">Completed</span>
                                    @elseif($batch->status === 'semangit')
                                        <span class="px-2 py-1 text-xs font-semibold rounded" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24;">Semangit</span>
                                    @elseif($batch->status === 'failed')
                                        <span class="px-2 py-1 text-xs font-semibold rounded" style="background: rgba(239, 68, 68, 0.15); color: #f87171;">Failed</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded bg-gray-700 text-gray-300">{{ ucfirst($batch->status) }}</span>
                                    @endif
                                </td>
                                <td class="p-3 text-sm" style="max-width: 250px;">
                                    <div style="color: var(--color-text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $batch->prediction_notes ?? 'Tidak ada catatan' }}">
                                        {{ $batch->prediction_notes ?? '—' }}
                                    </div>
                                </td>
                                <td class="p-3">
                                    <div class="flex items-center gap-2">
                                        @if($batch->status === 'active')
                                            <form id="form-end-{{ $batch->id }}" action="{{ route('admin.batches.update', ['batch' => $batch->id]) }}" method="POST" class="m-0" onsubmit="return false;">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="completed">
                                                <button type="button" class="px-3 py-1.5 text-xs rounded-md font-bold text-white bg-red-600 hover:bg-red-700 transition-colors border-none cursor-pointer flex items-center gap-1" data-form-id="end-{{ $batch->id }}" onclick="confirmAction(this.getAttribute('data-form-id'), 'Akhiri Batch Sekarang?', 'Proses fermentasi akan dihentikan dan waktu selesai akan dicatat.')">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1-1h-4a1 1 0 01-1-1v-4z" /></svg>
                                                    Akhiri Batch
                                                </button>
                                            </form>
                                            <form id="form-delete-{{ $batch->id }}" action="{{ route('admin.batches.destroy', ['batch' => $batch->id]) }}" method="POST" class="m-0" onsubmit="return false;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="px-3 py-1.5 text-xs rounded-md font-bold text-white bg-red-600 hover:bg-red-700 transition-colors border-none cursor-pointer" data-form-id="delete-{{ $batch->id }}" onclick="confirmAction(this.getAttribute('data-form-id'), 'Hapus Riwayat Batch?', 'Data riwayat produksi ini akan dihapus secara permanen beserta catatannya.')">
                                                    Hapus (Batal)
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="px-3 py-1.5 text-xs rounded-md font-bold text-white bg-gray-700 hover:bg-gray-600 transition-colors border-none cursor-pointer" onclick="openEditBatchModal({{ $batch->id }}, '{{ $batch->status }}', '{{ $batch->end_time ? $batch->end_time->format('Y-m-d\TH:i') : '' }}', `{{ addslashes($batch->prediction_notes ?? '') }}`, '{{ route('admin.batches.update', ['batch' => $batch->id]) }}')">
                                                Edit Batch
                                            </button>
                                            <form id="form-delete-{{ $batch->id }}" action="{{ route('admin.batches.destroy', ['batch' => $batch->id]) }}" method="POST" class="m-0" onsubmit="return false;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="px-3 py-1.5 text-xs rounded-md font-bold text-white bg-red-600 hover:bg-red-700 transition-colors border-none cursor-pointer" data-form-id="delete-{{ $batch->id }}" onclick="confirmAction(this.getAttribute('data-form-id'), 'Hapus Riwayat Batch?', 'Data riwayat produksi ini akan dihapus secara permanen beserta catatannya.')">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-6" style="color: var(--color-text-muted);">Belum ada riwayat batch untuk perangkat ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagination List Devices --}}
    @if($devices->hasPages())
    <div class="mt-6 pt-4">
        {{ $devices->links() }}
    </div>
    @endif
</div>

{{-- MODALS --}}
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
                <input type="datetime-local" name="end_time" id="edit-batch-endtime" class="form-input" step="1">
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

{{-- ACTION CONFIRMATION MODAL --}}
<div class="modal-overlay" id="action-modal">
    <div class="modal-content text-center" style="max-width: 400px;">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full mb-4" style="background: rgba(248, 113, 113, 0.1);">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" style="color: var(--color-accent-red);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
        </div>
        <h3 class="text-lg font-bold mb-2" id="modal-title">Konfirmasi Aksi</h3>
        <p class="text-sm mb-6" style="color: var(--color-text-secondary);" id="modal-desc">Apakah Anda yakin ingin melakukan tindakan ini?</p>
        <div class="flex gap-3">
            <button class="btn btn-secondary flex-1" onclick="closeModal('action-modal')">Batal</button>
            <button class="btn btn-danger flex-1" id="btn-confirm-action">Ya, Lanjutkan</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // 4. Logika JavaScript & Paginasi
    function toggleBatchList(deviceId) {
        const listDiv = document.getElementById('batch-list-' + deviceId);
        const chevron = document.getElementById('chevron-' + deviceId);
        
        listDiv.classList.toggle('hidden');
        
        if (listDiv.classList.contains('hidden')) {
            chevron.style.transform = 'rotate(0deg)';
        } else {
            chevron.style.transform = 'rotate(180deg)';
        }
    }

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

    function confirmAction(formId, title, desc) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: title,
                text: desc,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#374151',
                confirmButtonText: 'Ya, Lakukan!',
                cancelButtonText: 'Batal',
                background: 'var(--color-bg-card, #1e293b)',
                color: 'var(--color-text-primary, #f1f5f9)',
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-' + formId).submit();
                }
            });
        } else {
            document.getElementById('modal-title').innerText = title;
            document.getElementById('modal-desc').innerText = desc;
            
            let confirmBtn = document.getElementById('btn-confirm-action');
            confirmBtn.onclick = function() {
                document.getElementById('form-' + formId).submit();
            };
            
            document.getElementById('action-modal').classList.add('active');
        }
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
    }
</script>
@endpush
