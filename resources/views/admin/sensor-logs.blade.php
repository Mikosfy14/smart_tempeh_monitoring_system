@extends('layouts.admin')

@section('title', 'Sensor Logs — Smart Tempeh Monitoring')
@section('page-title', 'Sensor Logs')

@section('content')
<div class="stagger-children">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm" style="color: var(--color-text-secondary);">Browse and filter all sensor telemetry data</p>
        <button class="btn btn-danger btn-sm" onclick="openModal('purge-modal')" id="btn-purge-logs">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
            Purge > 30 Hari
        </button>
    </div>

    {{-- Filter Bar --}}
    <div class="card-static mb-6">
        <form method="GET" action="{{ route('admin.sensor-logs') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end" id="filter-form">
            <div>
                <label class="form-label">Tanggal Mulai</label>
                <input type="text" name="date_from" class="form-input flatpickr-input" value="{{ request('date_from') }}" placeholder="YYYY-MM-DD" id="filter-date-from">
            </div>
            <div>
                <label class="form-label">Tanggal Akhir</label>
                <input type="text" name="date_to" class="form-input flatpickr-input" value="{{ request('date_to') }}" placeholder="YYYY-MM-DD" id="filter-date-to">
            </div>
            <div>
                <label class="form-label">Device</label>
                <select name="device_id" class="form-input" id="filter-device">
                    <option value="">Semua Device</option>
                    @foreach($devices as $d)
                        <option value="{{ $d->id }}" {{ request('device_id') == $d->id ? 'selected' : '' }}>
                            {{ $d->device_id }} {{ $d->label_rak ? '— '.$d->label_rak : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">User</label>
                <select name="user_id" class="form-input" id="filter-user">
                    <option value="">Semua User</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-1" id="btn-filter">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                    Filter
                </button>
                <a href="{{ route('admin.sensor-logs') }}" class="btn btn-secondary btn-sm" id="btn-reset-filter">Reset</a>
            </div>
        </form>
    </div>

    {{-- Data Table --}}
    <div class="card-static overflow-x-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold">
                {{ $logs->total() }} log ditemukan
                <span class="text-xs font-normal" style="color: var(--color-text-muted);">(halaman {{ $logs->currentPage() }} dari {{ $logs->lastPage() }})</span>
            </h3>
        </div>

        <table class="data-table" id="sensor-logs-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Waktu</th>
                    <th>Device ID</th>
                    <th>Label Rak</th>
                    <th>User</th>
                    <th>Suhu Internal</th>
                    <th>Amonia</th>
                    <th>Suhu Ruang</th>
                    <th>Kelembapan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $i => $log)
                <tr>
                    <td style="color: var(--color-text-muted);">{{ $logs->firstItem() + $i }}</td>
                    <td style="color: var(--color-text-secondary); white-space: nowrap;">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    <td><code class="text-xs px-2 py-1 rounded" style="background: var(--color-bg-primary); color: var(--color-accent-teal);">{{ $log->device->device_id ?? '—' }}</code></td>
                    <td>{{ $log->device->label_rak ?? '—' }}</td>
                    <td>{{ $log->device->user->name ?? '—' }}</td>
                    <td style="color: var(--color-accent-red); font-weight: 600;">{{ number_format($log->internal_temp, 1) }}°C</td>
                    <td style="color: var(--color-accent-amber); font-weight: 600;">{{ number_format($log->amonia_level, 1) }} ppm</td>
                    <td style="color: var(--color-accent-blue);">{{ number_format($log->room_temp, 1) }}°C</td>
                    <td style="color: var(--color-accent-cyan);">{{ number_format($log->humidity, 1) }}%</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-8" style="color: var(--color-text-muted);">Tidak ada log sensor ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        @if($logs->hasPages())
        <div class="mt-6 flex justify-center">
            <div style="display: flex; gap: 4px; align-items: center;">
                {{-- Previous --}}
                @if($logs->onFirstPage())
                    <span class="btn btn-secondary btn-sm" style="opacity: 0.5; pointer-events: none;">← Prev</span>
                @else
                    <a href="{{ $logs->previousPageUrl() }}" class="btn btn-secondary btn-sm">← Prev</a>
                @endif

                {{-- Page Numbers --}}
                @foreach ($logs->getUrlRange(max(1, $logs->currentPage() - 2), min($logs->lastPage(), $logs->currentPage() + 2)) as $page => $url)
                    @if ($page == $logs->currentPage())
                        <span class="btn btn-primary btn-sm">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="btn btn-secondary btn-sm">{{ $page }}</a>
                    @endif
                @endforeach

                {{-- Next --}}
                @if($logs->hasMorePages())
                    <a href="{{ $logs->nextPageUrl() }}" class="btn btn-secondary btn-sm">Next →</a>
                @else
                    <span class="btn btn-secondary btn-sm" style="opacity: 0.5; pointer-events: none;">Next →</span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

{{-- PURGE CONFIRM MODAL --}}
<div class="modal-overlay" id="purge-modal">
    <div class="modal-content text-center">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full mb-4" style="background: rgba(248, 113, 113, 0.1);">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" style="color: var(--color-accent-red);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
        </div>
        <h3 class="text-lg font-bold mb-2">Purge Old Logs</h3>
        <p class="text-sm mb-6" style="color: var(--color-text-secondary);">Hapus semua log sensor yang lebih lama dari <strong class="text-white">30 hari</strong>? Aksi ini tidak bisa di-undo.</p>
        <div class="flex gap-3">
            <button class="btn btn-secondary flex-1" onclick="closeModal('purge-modal')">Batal</button>
            <button class="btn btn-danger flex-1" onclick="confirmPurge()" id="btn-confirm-purge">Hapus Sekarang</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openModal(id) { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

async function confirmPurge() {
    const btn = document.getElementById('btn-confirm-purge');
    btn.disabled = true;
    btn.textContent = 'Menghapus...';

    try {
        const res = await fetch('{{ route("admin.sensor-logs.purge") }}', {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' }
        });
        const data = await res.json();
        closeModal('purge-modal');
        alert(data.message || 'Berhasil.');
        location.reload();
    } catch (err) {
        alert('Gagal menghapus log.');
        btn.disabled = false;
        btn.textContent = 'Hapus Sekarang';
    }
}

// ============================================
// FLATPICKR INITIALIZATION
// ============================================
flatpickr('#filter-date-from', { allowInput: true, dateFormat: 'Y-m-d' });
flatpickr('#filter-date-to', { allowInput: true, dateFormat: 'Y-m-d' });
</script>
@endpush
