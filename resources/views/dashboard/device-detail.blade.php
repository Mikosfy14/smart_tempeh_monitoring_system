@extends('layouts.app')

@section('title', ($device->label_rak ?? $device->device_id) . ' — Detail Alat')

@section('content')
<div class="stagger-children">

    {{-- Header & Back Button --}}
    <div class="flex flex-col md:flex-row md:items-center justify-center mb-8 relative gap-4">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm md:absolute md:left-0 self-start" id="btn-back-dashboard">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
            Kembali
        </a>
        <div class="text-left md:text-center w-full">
            <h1 class="text-2xl font-bold">{{ $device->label_rak ?? $device->device_name }}</h1>
            <p class="text-sm mt-1" style="color: var(--color-text-muted);">ID: {{ $device->device_id }}</p>
        </div>
        <div class="flex items-center gap-2 md:absolute md:right-0 self-start md:self-center">
            <span class="badge {{ $device->operation_mode === 'AUTO' ? 'badge-green' : 'badge-amber' }}" id="device-mode-badge">{{ $device->operation_mode }}</span>
            <span class="badge {{ $device->is_online ? 'badge-green' : 'badge-red' }}" id="device-online-badge">{{ $device->is_online ? 'Online' : 'Offline' }}</span>
        </div>
    </div>

    {{-- ============================================================= --}}
    {{-- CARD: STATUS PRODUKSI (BATCH)                                  --}}
    {{-- Posisi: tepat di bawah header, di atas grid sensor             --}}
    {{-- Tiga state: active/semangit | failed | idle                   --}}
    {{-- ============================================================= --}}
    <div class="card-static batch-status-card mb-6" id="card-batch-status"
        @if($activeBatch && $activeBatch->status === 'semangit') style="border-color: rgba(251,191,36,0.3);"
        @elseif($latestBatch && $latestBatch->status === 'failed') style="border-color: rgba(239,68,68,0.35);"
        @endif>

        {{-- ── Header card ── --}}
        <div class="flex items-center gap-3 mb-4">
            <div class="batch-icon-wrap"
                @if($activeBatch && $activeBatch->status === 'semangit') style="background: rgba(251,191,36,0.15); color: #fbbf24;"
                @elseif($latestBatch && $latestBatch->status === 'failed') style="background: rgba(239,68,68,0.15); color: #ef4444;"
                @endif>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold">Status Produksi (Batch)</h3>
                <p class="text-xs" style="color: var(--color-text-muted);">Manajemen sesi fermentasi aktif</p>
            </div>
        </div>

        @if($activeBatch)
            {{-- ─────────────────────────────────────────────────── --}}
            {{-- STATE A: Batch SEDANG BERJALAN (active / semangit) --}}
            {{-- ─────────────────────────────────────────────────── --}}
            <div class="batch-active-state">

                {{-- Baris 1: Badge status + durasi berjalan --}}
                <div class="flex flex-wrap items-center gap-3 mb-4">
                    @if($activeBatch->status === 'semangit')
                        <span class="badge badge-warning-glow" id="batch-status-badge">
                            <span class="batch-pulse-dot batch-pulse-dot--warning"></span>
                            Semangit
                        </span>
                    @else
                        <span class="badge badge-active-glow" id="batch-status-badge">
                            <span class="batch-pulse-dot batch-pulse-dot--active"></span>
                            Aktif
                        </span>
                    @endif

                    <span class="text-xs px-2 py-1 rounded-md" style="background: rgba(148,163,184,0.1); color: var(--color-text-muted);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 inline -mt-0.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Berjalan selama <strong>{{ number_format($activeBatch->duration_hours, 1) }} jam</strong>
                    </span>
                </div>

                {{-- Baris 2: Grid info — waktu mulai & ID batch --}}
                <div class="grid grid-cols-2 gap-3 mb-5">
                    <div class="batch-info-cell">
                        <span class="batch-info-label">Waktu Mulai</span>
                        <span class="batch-info-value">{{ $activeBatch->start_time->format('d M Y') }}</span>
                        <span class="batch-info-sub">{{ $activeBatch->start_time->format('H:i:s') }}</span>
                    </div>
                    <div class="batch-info-cell">
                        <span class="batch-info-label">ID Batch</span>
                        <span class="batch-info-value">#{{ str_pad($activeBatch->id, 4, '0', STR_PAD_LEFT) }}</span>
                        <span class="batch-info-sub">{{ $activeBatch->start_time->diffForHumans() }}</span>
                    </div>
                </div>

                {{-- Catatan prediksi (jika ada) --}}
                @if($activeBatch->prediction_notes)
                    <div class="mb-4 p-3 rounded-lg text-xs" style="background: rgba(251,191,36,0.08); border: 1px solid rgba(251,191,36,0.2); color: var(--color-text-secondary); white-space: pre-line;">{{ $activeBatch->prediction_notes }}</div>
                @endif

                {{-- Tombol aksi untuk batch aktif --}}
                <div class="flex flex-col sm:flex-row gap-3 mt-5">
                    {{-- Aksi 1: Akhiri Batch Resmi --}}
                    <form action="{{ route('batch.update', ['device' => $device->id, 'batch' => $activeBatch->id]) }}" method="POST" id="form-end-batch" class="flex-1">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="completed">
                        <button type="button" class="btn btn-batch-end w-full" id="btn-end-batch" onclick="confirmEndBatch('active')">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                            </svg>
                            Akhiri Batch
                        </button>
                    </form>

                    {{-- Aksi 2: Batalkan & Hapus --}}
                    <form action="{{ route('batch.destroy', ['device' => $device->id, 'batch' => $activeBatch->id]) }}" method="POST" id="form-delete-batch-{{ $activeBatch->id }}" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-secondary w-full" onclick="confirmCancelBatch({{ $activeBatch->id }})" style="border-color: rgba(239, 68, 68, 0.5); color: #f87171;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Hapus Batch (Batal)
                        </button>
                    </form>
                </div>

            </div>

        @elseif($latestBatch && $latestBatch->status === 'failed')
            {{-- ────────────────────────────────────────────────────── --}}
            {{-- STATE B: Batch GAGAL / BUSUK (terdeteksi oleh AI)     --}}
            {{-- ────────────────────────────────────────────────────── --}}
            <div class="batch-failed-state">

                {{-- Badge merah + info waktu gagal --}}
                <div class="flex flex-wrap items-center gap-3 mb-4">
                    <span class="badge badge-failed-glow" id="batch-status-badge">
                        <span class="batch-pulse-dot batch-pulse-dot--failed"></span>
                        Gagal / Busuk
                    </span>
                    <span class="text-xs px-2 py-1 rounded-md" style="background: rgba(148,163,184,0.1); color: var(--color-text-muted);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 inline -mt-0.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Terdeteksi {{ $latestBatch->end_time ? $latestBatch->end_time->diffForHumans() : 'baru saja' }}
                    </span>
                </div>

                {{-- Grid info batch gagal --}}
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="batch-info-cell" style="border-color: rgba(239,68,68,0.2);">
                        <span class="batch-info-label">Waktu Mulai</span>
                        <span class="batch-info-value">{{ $latestBatch->start_time->format('d M Y') }}</span>
                        <span class="batch-info-sub">{{ $latestBatch->start_time->format('H:i:s') }}</span>
                    </div>
                    <div class="batch-info-cell" style="border-color: rgba(239,68,68,0.2);">
                        <span class="batch-info-label">ID Batch</span>
                        <span class="batch-info-value">#{{ str_pad($latestBatch->id, 4, '0', STR_PAD_LEFT) }}</span>
                        <span class="batch-info-sub">{{ $latestBatch->start_time->diffForHumans() }}</span>
                    </div>
                </div>

                {{-- Catatan kegagalan dari sistem --}}
                @if($latestBatch->prediction_notes)
                    <div class="mb-5 p-3 rounded-lg text-xs" style="background: rgba(239,68,68,0.07); border: 1px solid rgba(239,68,68,0.25); color: var(--color-text-secondary); white-space: pre-line; line-height: 1.6;">
                        <div class="flex items-center gap-2 mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0" style="color: #ef4444;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <strong style="color: #ef4444;">Laporan Sistem</strong>
                        </div>
                        {{ $latestBatch->prediction_notes }}
                    </div>
                @endif

                {{-- Tombol Tutup Peringatan & Bersihkan (untuk batch yang gagal) --}}
                <form action="{{ route('batch.end', [$device, $latestBatch]) }}" method="POST" id="form-end-batch-failed">
                    @csrf
                    <button type="button" class="btn w-full font-semibold" id="btn-end-batch-failed"
                            onclick="confirmEndBatch('failed')"
                            style="background-color: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.35);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Konfirmasi Kegagalan
                    </button>
                </form>

            </div>

        @else
            {{-- ─────────────────────────────────────────────── --}}
            {{-- STATE C: Tidak ada batch berjalan (idle)        --}}
            {{-- Tampil jika: belum ada batch, atau sudah selesai --}}
            {{-- ─────────────────────────────────────────────── --}}
            <div class="batch-idle-state">
                <div class="batch-idle-illustration">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="color: var(--color-text-muted); opacity: 0.45;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </div>
                <p class="text-sm font-medium" style="color: var(--color-text-muted);">Tidak ada produksi yang sedang berjalan</p>
                <p class="text-xs mt-1 mb-5" style="color: var(--color-text-muted); opacity: 0.7;">
                    Mulai sesi baru untuk merekam dan menganalisis data fermentasi secara terstruktur.
                </p>
                <form action="{{ route('batch.start', $device) }}" method="POST" id="form-start-batch">
                    @csrf
                    <button type="submit" class="btn btn-batch-start w-full" id="btn-start-batch">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Mulai Produksi Baru
                    </button>
                </form>
            </div>

        @endif

        {{-- Common Footer Action: Lihat Semua Riwayat Batch --}}
        <div class="mt-4 pt-4" style="border-top: 1px solid var(--color-border-card);">
            <a href="{{ route('batch.history', $device) }}" class="btn btn-secondary w-full justify-center text-xs font-semibold gap-2" id="btn-batch-history" style="padding: 10px 16px;">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Lihat Semua Riwayat Batch
            </a>
        </div>

    </div>
    {{-- / END CARD BATCH --}}

    {{-- =============================== --}}
    {{-- SENSOR CARDS ROW --}}
    {{-- =============================== --}}
    @php $log = $device->latestLog; $sensorStatus = $device->sensor_status; @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="card-static sensor-mini-card" style="border-left: 3px solid var(--color-accent-red);" id="card-internal-temp">
            <div class="flex items-center justify-between">
                <span class="text-xs" style="color: var(--color-text-muted);">Suhu Internal</span>
                <span class="sensor-status-dot {{ $sensorStatus['ds18b20'] === 'ok' ? 'sensor-status--ok' : 'sensor-status--error' }}" id="status-ds18b20" title="DS18B20"></span>
            </div>
            <span class="text-2xl font-bold" style="color: var(--color-accent-red);" id="val-internal-temp">{{ $log ? number_format($log->internal_temp, 1) : '--' }}</span>
            <span class="text-xs" style="color: var(--color-text-muted);">°C</span>
        </div>
        <div class="card-static sensor-mini-card" style="border-left: 3px solid var(--color-accent-amber);" id="card-amonia">
            <div class="flex items-center justify-between">
                <span class="text-xs" style="color: var(--color-text-muted);">Amonia</span>
                <span class="sensor-status-dot {{ $sensorStatus['mq135'] === 'ok' ? 'sensor-status--ok' : 'sensor-status--error' }}" id="status-mq135" title="MQ-135"></span>
            </div>
            <span class="text-2xl font-bold" style="color: var(--color-accent-amber);" id="val-amonia">{{ $log ? number_format($log->amonia_level, 1) : '--' }}</span>
            <span class="text-xs" style="color: var(--color-text-muted);">ppm</span>
        </div>
        <div class="card-static sensor-mini-card" style="border-left: 3px solid var(--color-accent-blue);" id="card-room-temp">
            <div class="flex items-center justify-between">
                <span class="text-xs" style="color: var(--color-text-muted);">Suhu Ruang</span>
                <span class="sensor-status-dot {{ $sensorStatus['dht22'] === 'ok' ? 'sensor-status--ok' : 'sensor-status--error' }}" id="status-dht22-temp" title="DHT22"></span>
            </div>
            <span class="text-2xl font-bold" style="color: var(--color-accent-blue);" id="val-room-temp">{{ $log ? number_format($log->room_temp, 1) : '--' }}</span>
            <span class="text-xs" style="color: var(--color-text-muted);">°C</span>
        </div>
        <div class="card-static sensor-mini-card" style="border-left: 3px solid var(--color-accent-cyan);" id="card-humidity">
            <div class="flex items-center justify-between">
                <span class="text-xs" style="color: var(--color-text-muted);">Kelembapan</span>
                <span class="sensor-status-dot {{ $sensorStatus['dht22'] === 'ok' ? 'sensor-status--ok' : 'sensor-status--error' }}" id="status-dht22-hum" title="DHT22"></span>
            </div>
            <span class="text-2xl font-bold" style="color: var(--color-accent-cyan);" id="val-humidity">{{ $log ? number_format($log->humidity, 1) : '--' }}</span>
            <span class="text-xs" style="color: var(--color-text-muted);">%</span>
        </div>
    </div>
    {{-- / END SENSOR CARDS ROW --}}

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">


        {{-- =============================== --}}
        {{-- CHART SECTION (2/3 width) --}}
        {{-- =============================== --}}
        <div class="lg:col-span-2 card-static" style="position: relative;">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-base font-bold">Grafik Sensor</h2>
                <div class="flex items-center gap-1">
                    <button class="chart-range-btn" data-range="1h">1h</button>
                    <button class="chart-range-btn active" data-range="6h">6h</button>
                    <button class="chart-range-btn" data-range="24h">24h</button>
                    <button class="chart-range-btn" data-range="7d">7d</button>
                    <button class="chart-reset-btn" id="chart-reset-btn" title="Reset grafik (clear data abnormal)">Reset</button>
                </div>
            </div>

            {{-- Chart Filter Toggles --}}
            <div class="flex flex-wrap items-center gap-2 mb-4">
                <button class="chart-filter-btn active" data-index="0" style="--filter-color: #f87171;">
                    <span class="chart-filter-dot"></span> Suhu Internal
                </button>
                <button class="chart-filter-btn active" data-index="1" style="--filter-color: #fbbf24;">
                    <span class="chart-filter-dot"></span> Gas Amonia
                </button>
                <button class="chart-filter-btn active" data-index="2" style="--filter-color: #60a5fa;">
                    <span class="chart-filter-dot"></span> Suhu Rak
                </button>
                <button class="chart-filter-btn active" data-index="3" style="--filter-color: #22d3ee;">
                    <span class="chart-filter-dot"></span> Kelembapan
                </button>
            </div>

            <div style="height: 320px;">
                <canvas id="sensor-chart"></canvas>
            </div>

            <p class="text-xs mt-3" style="color: var(--color-text-muted);">
                Update terakhir: <span id="last-update">{{ $log ? $log->created_at->format('H:i:s') : '--:--:--' }}</span>
            </p>
        </div>

        {{-- =============================== --}}
        {{-- SIDE PANEL (1/3 width) --}}
        {{-- =============================== --}}
        <div class="flex flex-col gap-6">

            {{-- Status Komponen --}}
            <div class="card-static">
                <h3 class="text-sm font-bold mb-4">Status Komponen</h3>
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" style="color: var(--color-accent-red);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            <span class="text-xs font-medium">DS18B20</span>
                        </div>
                        <span class="badge {{ $sensorStatus['ds18b20'] === 'ok' ? 'badge-green' : 'badge-red' }}" id="comp-ds18b20">{{ $sensorStatus['ds18b20'] === 'ok' ? 'OK' : 'Error' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" style="color: var(--color-accent-blue);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" /></svg>
                            <span class="text-xs font-medium">DHT22</span>
                        </div>
                        <span class="badge {{ $sensorStatus['dht22'] === 'ok' ? 'badge-green' : 'badge-red' }}" id="comp-dht22">{{ $sensorStatus['dht22'] === 'ok' ? 'OK' : 'Error' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" style="color: var(--color-accent-amber);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                            <span class="text-xs font-medium">MQ-135</span>
                        </div>
                        <span class="badge {{ $sensorStatus['mq135'] === 'ok' ? 'badge-green' : 'badge-red' }}" id="comp-mq135">{{ $sensorStatus['mq135'] === 'ok' ? 'OK' : 'Error' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" style="color: var(--color-accent-cyan);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            <span class="text-xs font-medium">Relay (Kipas)</span>
                        </div>
                        <span class="badge {{ $device->fan_status === 'ON' ? 'badge-green' : 'badge-muted' }}" id="comp-relay">{{ $device->fan_status }}</span>
                    </div>
                </div>
            </div>

            {{-- Fan Control --}}
            <div class="card-static">
                <h3 class="text-sm font-bold mb-4">Kontrol Kipas</h3>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium" id="mode-label-auto" style="color: {{ $device->operation_mode === 'AUTO' ? 'var(--color-accent-green)' : 'var(--color-text-muted)' }};">AUTO</span>
                        <button class="toggle-switch toggle-switch--sm {{ $device->operation_mode === 'MANUAL' ? 'active' : '' }}" id="mode-toggle" onclick="toggleMode()"></button>
                        <span class="text-xs font-medium" id="mode-label-manual" style="color: {{ $device->operation_mode === 'MANUAL' ? 'var(--color-accent-amber)' : 'var(--color-text-muted)' }};">MANUAL</span>
                    </div>
                    <button class="power-btn power-btn--sm {{ $device->fan_status === 'ON' ? 'on' : '' }}" id="power-btn" {{ $device->operation_mode === 'AUTO' ? 'disabled' : '' }} onclick="toggleFan()">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    </button>
                </div>
                <div class="flex items-center gap-2">
                    <span class="badge {{ $device->fan_status === 'ON' ? 'badge-green' : 'badge-red' }}" id="fan-status-badge">{{ $device->fan_status }}</span>
                    <span class="text-xs" style="color: var(--color-text-muted);" id="fan-status-text">
                        {{ $device->operation_mode === 'AUTO' ? 'Mode AUTO — System controlled' : 'Mode MANUAL — Fan ' . $device->fan_status }}
                    </span>
                </div>
            </div>

            {{-- PDF Export --}}
            <div class="card-static">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold">Cetak Laporan PDF</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" style="color: var(--color-text-muted);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                </div>

                {{-- Tabs Toggle --}}
                <div class="flex mb-4 border-b border-gray-700/50">
                    <button type="button" class="flex-1 pb-2 text-xs font-semibold text-center border-b-2 transition-colors export-tab-btn active" data-target="export-sensor-form" style="border-color: var(--color-accent-teal); color: var(--color-accent-teal);">
                        Data Sensor
                    </button>
                    <button type="button" class="flex-1 pb-2 text-xs font-semibold text-center border-b-2 border-transparent transition-colors export-tab-btn" data-target="export-batch-form" style="color: var(--color-text-muted);">
                        Riwayat Batch
                    </button>
                </div>

                {{-- Tab 1: Export Sensor --}}
                <form action="{{ route('device.export-pdf', $device->id) }}" method="GET" class="export-form active" id="export-sensor-form">
                    <p class="text-xs mb-4" style="color: var(--color-text-muted);">Pilih rentang tanggal untuk mencetak data tren <strong>sensor</strong> harian.</p>
                    <div class="mb-3">
                        <label class="text-xs" style="color: var(--color-text-muted);">Tanggal Mulai</label>
                        <input type="text" name="date_from" class="form-input flatpickr-input" required id="pdf-date-from" value="{{ now()->subDays(7)->format('Y-m-d') }}" placeholder="YYYY-MM-DD" style="font-size: 0.85rem;">
                    </div>
                    <div class="mb-4">
                        <label class="text-xs" style="color: var(--color-text-muted);">Tanggal Akhir</label>
                        <input type="text" name="date_to" class="form-input flatpickr-input" required id="pdf-date-to" value="{{ now()->format('Y-m-d') }}" placeholder="YYYY-MM-DD" style="font-size: 0.85rem;">
                    </div>
                    <button type="submit" class="btn btn-secondary btn-sm w-full" id="btn-export-pdf">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        Download PDF Sensor
                    </button>
                </form>

                {{-- Tab 2: Export Batch --}}
                <form action="{{ route('batch.export-pdf', $device) }}" method="POST" class="export-form hidden" id="export-batch-form">
                    @csrf
                    <p class="text-xs mb-4" style="color: var(--color-text-muted);">Pilih rentang waktu untuk mencetak rekapitulasi data <strong>sesi fermentasi (batch)</strong>.</p>
                    <div class="mb-3">
                        <label class="text-xs" style="color: var(--color-text-muted);">Dari Tanggal</label>
                        <input type="text" name="date_from" class="form-input flatpickr-input" required id="pdf-batch-date-from" value="{{ now()->subDays(30)->format('Y-m-d') }}" placeholder="YYYY-MM-DD" style="font-size: 0.85rem;">
                    </div>
                    <div class="mb-4">
                        <label class="text-xs" style="color: var(--color-text-muted);">Sampai Tanggal</label>
                        <input type="text" name="date_to" class="form-input flatpickr-input" required id="pdf-batch-date-to" value="{{ now()->format('Y-m-d') }}" placeholder="YYYY-MM-DD" style="font-size: 0.85rem;">
                    </div>
                    <button type="submit" class="btn btn-secondary btn-sm w-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        Download PDF Batch
                    </button>
                </form>
            </div>

            {{-- Edit Device Cue --}}
            <div class="card-static">
                <h3 class="text-sm font-bold mb-3">Pengaturan Alat</h3>
                <p class="text-xs mb-4" style="color: var(--color-text-muted);">
                    Ubah nama alat atau sesuaikan ambang batas notifikasi peringatan sensor.
                </p>
                @if($activeBatch)
                    <p class="text-xs text-amber-500 font-semibold mb-2">⚠ Tidak dapat mengubah alat saat batch sedang aktif.</p>
                    <button type="button" class="btn btn-secondary btn-sm w-full" disabled style="opacity: 0.5; cursor: not-allowed;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        Edit Detail Alat
                    </button>
                @else
                    <a href="{{ route('device.edit', $device->id) }}" class="btn btn-secondary btn-sm w-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        Edit Detail Alat
                    </a>
                @endif
            </div>

        </div>
    </div>

    {{-- =============================== --}}
    {{-- HISTORICAL DATA TABLE --}}
    {{-- =============================== --}}
    <div class="card-static mt-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <h2 class="text-base font-bold">Data Historis Sensor</h2>
                <span class="badge badge-muted" id="table-row-count">{{ $tableData->count() }} data</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="sensor-status-dot sensor-status--ok" id="table-live-dot" style="animation: pulse-glow 2s ease-in-out infinite;"></span>
                <span class="text-xs" style="color: var(--color-text-muted);">Live</span>
            </div>
        </div>

        <div class="history-table-wrapper" id="history-table-wrapper">
            <table class="history-table" id="history-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Suhu Internal</th>
                        <th>Suhu Rak</th>
                        <th>Kelembapan</th>
                        <th>Gas Amonia</th>
                        <th>Kipas</th>
                    </tr>
                </thead>
                <tbody id="history-tbody">
                    @foreach($tableData as $row)
                    <tr>
                        <td>
                            <span class="text-xs font-medium">{{ $row->created_at->format('d M Y') }}</span><br>
                            <span class="text-xs" style="color: var(--color-text-muted);">{{ $row->created_at->format('H:i:s') }}</span>
                        </td>
                        <td>
                            <span style="color: var(--color-accent-red); font-weight: 600;">{{ number_format($row->internal_temp, 1) }}°C</span>
                        </td>
                        <td>
                            <span style="color: var(--color-accent-blue); font-weight: 600;">{{ number_format($row->room_temp, 1) }}°C</span>
                        </td>
                        <td>
                            <span style="color: var(--color-accent-cyan); font-weight: 600;">{{ number_format($row->humidity, 1) }}%</span>
                        </td>
                        <td>
                            <span style="color: var(--color-accent-amber); font-weight: 600;">{{ number_format($row->amonia_level, 1) }} ppm</span>
                        </td>
                        <td>
                            <span class="badge {{ $device->fan_status === 'ON' ? 'badge-green' : 'badge-muted' }}">{{ $device->fan_status }}</span>
                        </td>
                    </tr>
                    @endforeach

                    @if($tableData->count() === 0)
                    <tr id="empty-table-row">
                        <td colspan="6" class="text-center" style="padding: 2rem; color: var(--color-text-muted);">
                            Belum ada data sensor yang tercatat.
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <p class="text-xs mt-3" style="color: var(--color-text-muted);">
            Menampilkan maksimal 20 data terbaru. Data diperbarui secara otomatis setiap 5 detik.
        </p>
    </div>

</div>

<style>
@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Chart Reset Button */
.chart-reset-btn {
    padding: 4px 10px;
    border-radius: 6px;
    border: 1px solid rgba(239,68,68,0.3);
    background: rgba(239,68,68,0.08);
    color: #f87171;
    font-size: 0.7rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-left: 4px;
}
.chart-reset-btn:hover {
    background: rgba(239,68,68,0.2);
    border-color: rgba(239,68,68,0.5);
}

/* Chart Filter Toggles */
.chart-filter-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 6px;
    border: 1px solid var(--color-border-card);
    background: transparent;
    color: var(--color-text-muted);
    font-size: 0.7rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    opacity: 0.45;
}
.chart-filter-btn.active {
    opacity: 1;
    border-color: var(--filter-color);
    color: var(--color-text-primary);
}
.chart-filter-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--filter-color);
}
.chart-filter-btn:not(.active) .chart-filter-dot {
    opacity: 0.3;
}

/* Sensor Status Dots */
.sensor-status-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}
.sensor-status--ok {
    background: var(--color-accent-green);
    box-shadow: 0 0 4px rgba(52, 211, 153, 0.5);
}
.sensor-status--error {
    background: var(--color-accent-red);
    box-shadow: 0 0 4px rgba(248, 113, 113, 0.5);
}

/* Badge Muted (for relay OFF state) */
.badge-muted {
    background: rgba(148, 163, 184, 0.15);
    color: #94a3b8;
}

/* Historical Data Table */
.history-table-wrapper {
    overflow-x: auto;
    border-radius: 8px;
    border: 1px solid var(--color-border-card);
    max-height: 520px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: var(--color-border-card) transparent;
}
.history-table-wrapper::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.history-table-wrapper::-webkit-scrollbar-track {
    background: transparent;
}
.history-table-wrapper::-webkit-scrollbar-thumb {
    background: var(--color-border-card);
    border-radius: 3px;
}
.history-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.8rem;
}
.history-table thead {
    position: sticky;
    top: 0;
    z-index: 5;
}
.history-table th {
    padding: 10px 14px;
    text-align: left;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--color-text-muted);
    background: var(--color-bg-card);
    border-bottom: 1px solid var(--color-border-card);
    white-space: nowrap;
}
.history-table td {
    padding: 10px 14px;
    border-bottom: 1px solid rgba(148, 163, 184, 0.06);
    white-space: nowrap;
    vertical-align: middle;
}
.history-table tbody tr {
    transition: background 0.2s ease;
}
.history-table tbody tr:hover {
    background: rgba(148, 163, 184, 0.04);
}

/* New row animation */
@keyframes table-row-flash {
    0% {
        background: rgba(45, 212, 191, 0.15);
    }
    100% {
        background: transparent;
    }
}
.history-table tbody tr.new-row {
    animation: table-row-flash 1.5s ease-out;
}

/* Live pulse dot */
@keyframes pulse-glow {
    0%, 100% {
        opacity: 1;
        box-shadow: 0 0 4px rgba(52, 211, 153, 0.5);
    }
    50% {
        opacity: 0.4;
        box-shadow: 0 0 8px rgba(52, 211, 153, 0.8);
    }
}

/* ============================================
   BATCH STATUS CARD STYLES
   ============================================ */

/* Icon wrapper */
.batch-icon-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: linear-gradient(135deg, rgba(45, 212, 191, 0.15), rgba(56, 189, 248, 0.1));
    color: var(--color-accent-teal);
    flex-shrink: 0;
}

/* Idle state layout */
.batch-idle-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 12px 0 4px;
}
.batch-idle-illustration {
    margin-bottom: 12px;
}

/* START button — gradient teal/green */
.btn-batch-start {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 28px;
    border-radius: 10px;
    border: none;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    letter-spacing: 0.02em;
    background: linear-gradient(135deg, #059669, #0d9488);
    color: #fff;
    box-shadow: 0 4px 15px rgba(13, 148, 136, 0.35);
    transition: all 0.25s ease;
}
.btn-batch-start:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(13, 148, 136, 0.5);
    background: linear-gradient(135deg, #10b981, #14b8a6);
}
.btn-batch-start:active { transform: translateY(0); }

/* END button — gradient red/rose */
.btn-batch-end {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 10px;
    border: none;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    letter-spacing: 0.02em;
    background: linear-gradient(135deg, #dc2626, #e11d48);
    color: #fff;
    box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
    transition: all 0.25s ease;
}
.btn-batch-end:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(220, 38, 38, 0.45);
    background: linear-gradient(135deg, #ef4444, #f43f5e);
}
.btn-batch-end:active { transform: translateY(0); }

/* Active status badge glow */
.badge-active-glow {
    background: rgba(52, 211, 153, 0.15);
    color: #34d399;
    border: 1px solid rgba(52, 211, 153, 0.3);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
}
.badge-warning-glow {
    background: rgba(251, 191, 36, 0.15);
    color: #fbbf24;
    border: 1px solid rgba(251, 191, 36, 0.3);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
}
.badge-failed-glow {
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.35);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
}

/* Pulse dot inside badge */
.batch-pulse-dot {
    display: inline-block;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
}
.batch-pulse-dot--active {
    background: #34d399;
    animation: batch-dot-pulse 2s ease-in-out infinite;
}
.batch-pulse-dot--warning {
    background: #fbbf24;
    animation: batch-dot-pulse 1.2s ease-in-out infinite;
}
.batch-pulse-dot--failed {
    background: #ef4444;
    animation: batch-dot-pulse 0.9s ease-in-out infinite;
}
@keyframes batch-dot-pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: 0.5; transform: scale(1.4); }
}

/* Info grid cells */
.batch-info-cell {
    display: flex;
    flex-direction: column;
    gap: 2px;
    padding: 10px 14px;
    border-radius: 8px;
    background: rgba(148, 163, 184, 0.06);
    border: 1px solid rgba(148, 163, 184, 0.1);
}
.batch-info-label {
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--color-text-muted);
}
.batch-info-value {
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--color-text-primary);
    line-height: 1.2;
}
.batch-info-sub {
    font-size: 0.7rem;
    color: var(--color-text-muted);
}
</style>
@endsection

@push('scripts')
<script>
const DEVICE_ID = {{ $device->id }};
const MAX_TABLE_ROWS = 20;
let currentRange = '6h';
let sensorChart = null;
let currentMode = '{{ $device->operation_mode }}';
let currentFanStatus = '{{ $device->fan_status }}';
let lastLogId = {{ $device->latestLog ? $device->latestLog->id : 0 }};

// ============================================
// CHART INITIALIZATION
// ============================================
(function initChart() {
    const ctx = document.getElementById('sensor-chart').getContext('2d');
    const initialData = @json($chartData);

    const labels = initialData.map(d => {
        const dt = new Date(d.created_at);
        return dt.getHours().toString().padStart(2,'0') + ':' + dt.getMinutes().toString().padStart(2,'0');
    });

    sensorChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                { label: 'Internal Temp (°C)', data: initialData.map(d => d.internal_temp), borderColor: '#f87171', backgroundColor: 'rgba(248,113,113,0.1)', fill: true, tension: 0.4, pointRadius: 0, borderWidth: 2 },
                { label: 'Ammonia (ppm)', data: initialData.map(d => d.amonia_level), borderColor: '#fbbf24', backgroundColor: 'rgba(251,191,36,0.1)', fill: true, tension: 0.4, pointRadius: 0, borderWidth: 2 },
                { label: 'Room Temp (°C)', data: initialData.map(d => d.room_temp), borderColor: '#60a5fa', backgroundColor: 'rgba(96,165,250,0.1)', fill: true, tension: 0.4, pointRadius: 0, borderWidth: 2 },
                { label: 'Humidity (%)', data: initialData.map(d => d.humidity), borderColor: '#22d3ee', backgroundColor: 'rgba(34,211,238,0.1)', fill: true, tension: 0.4, pointRadius: 0, borderWidth: 2 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { color: '#94a3b8', usePointStyle: true, pointStyle: 'circle', padding: 16, font: { family: 'Inter', size: 11 } } },
                tooltip: { backgroundColor: '#1e293b', titleColor: '#f1f5f9', bodyColor: '#94a3b8', borderColor: '#334155', borderWidth: 1, cornerRadius: 8, padding: 12 }
            },
            scales: {
                x: { grid: { color: 'rgba(30,41,59,0.5)' }, ticks: { color: '#64748b', font: { family: 'Inter', size: 10 }, maxTicksLimit: 12 } },
                y: { grid: { color: 'rgba(30,41,59,0.5)' }, ticks: { color: '#64748b', font: { family: 'Inter', size: 10 } } }
            }
        }
    });
})();

// ============================================
// CHART RANGE BUTTONS
// ============================================
document.querySelectorAll('.chart-range-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.chart-range-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        currentRange = this.dataset.range;
        refreshChart();
    });
});

// ============================================
// CHART RESET BUTTON
// ============================================
let chartResetUntil = null; // timestamp sampai kapan chart di-reset

document.getElementById('chart-reset-btn').addEventListener('click', function() {
    // Kosongkan semua data di chart
    sensorChart.data.labels = [];
    sensorChart.data.datasets.forEach(ds => { ds.data = []; });
    sensorChart.update();

    // Set timer: jangan refresh chart selama 10 detik agar user lihat chart bersih
    chartResetUntil = Date.now() + 10000;

    // Ubah tombol jadi "Direset" sementara
    this.textContent = 'Direset';
    this.style.pointerEvents = 'none';
    setTimeout(() => {
        this.textContent = 'Reset';
        this.style.pointerEvents = 'auto';
        chartResetUntil = null;
    }, 10000);
});

// ============================================
// CHART FILTER TOGGLES
// ============================================
document.querySelectorAll('.chart-filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        this.classList.toggle('active');
        const index = parseInt(this.dataset.index);
        const dataset = sensorChart.data.datasets[index];
        dataset.hidden = !this.classList.contains('active');
        sensorChart.update();
    });
});

// ============================================
// LIVE POLLING (every 1s — real-time)
// ============================================
setInterval(pollSensorData, 1000);

async function pollSensorData() {
    try {
        const res = await fetch(`/api/dashboard/live?device_id=${DEVICE_ID}`, {
            headers: { 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' }
        });
        const data = await res.json();
        
        const thresholdTemp = {{ $device->temp_threshold ?? 35.0 }};
        const thresholdAmonia = {{ $device->amonia_threshold ?? 2.0 }};
        const thresholdHumidity = {{ $device->humidity_threshold ?? 90.0 }};

        if (data.sensors) {
            updateVal('val-internal-temp', data.sensors.internal_temp, thresholdTemp);
            updateVal('val-amonia', data.sensors.amonia_level, thresholdAmonia);
            updateVal('val-room-temp', data.sensors.room_temp, null);
            updateVal('val-humidity', data.sensors.humidity, thresholdHumidity);
            document.getElementById('last-update').textContent = data.sensors.timestamp;

            // Add new row to table if this is a genuinely new log
            if (data.sensors.log_id && data.sensors.log_id !== lastLogId) {
                lastLogId = data.sensors.log_id;
                addTableRow(data.sensors, data.fan ? data.fan.status : currentFanStatus);
            }
        }
        if (data.fan) updateFanUI(data.fan.mode, data.fan.status);

        // Update online/offline badge
        if (data.is_online !== undefined) {
            const onlineBadge = document.getElementById('device-online-badge');
            if (onlineBadge) {
                onlineBadge.textContent = data.is_online ? 'Online' : 'Offline';
                onlineBadge.className = 'badge ' + (data.is_online ? 'badge-green' : 'badge-red');
            }
        }

        // Update sensor status badges
        if (data.sensor_status) {
            updateSensorStatusDot('status-ds18b20', data.sensor_status.ds18b20);
            updateSensorStatusDot('status-mq135', data.sensor_status.mq135);
            updateSensorStatusDot('status-dht22-temp', data.sensor_status.dht22);
            updateSensorStatusDot('status-dht22-hum', data.sensor_status.dht22);
            updateComponentBadge('comp-ds18b20', data.sensor_status.ds18b20);
            updateComponentBadge('comp-dht22', data.sensor_status.dht22);
            updateComponentBadge('comp-mq135', data.sensor_status.mq135);
            // Relay badge
            const relayBadge = document.getElementById('comp-relay');
            if (relayBadge) {
                relayBadge.textContent = data.sensor_status.relay;
                relayBadge.className = 'badge ' + (data.sensor_status.relay === 'ON' ? 'badge-green' : 'badge-muted');
            }
        }

        // Update batch status (real-time tanpa refresh)
        if (data.batch) {
            updateBatchStatus(data.batch);
        }
    } catch (e) { console.error('Poll error:', e); }
}

function updateSensorStatusDot(id, status) {
    const dot = document.getElementById(id);
    if (!dot) return;
    dot.className = 'sensor-status-dot ' + (status === 'ok' ? 'sensor-status--ok' : 'sensor-status--error');
}

function updateComponentBadge(id, status) {
    const badge = document.getElementById(id);
    if (!badge) return;
    badge.textContent = status === 'ok' ? 'OK' : 'Error';
    badge.className = 'badge ' + (status === 'ok' ? 'badge-green' : 'badge-red');
}

function updateVal(id, value, threshold) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = value !== null ? parseFloat(value).toFixed(1) : '--';
    const card = el.closest('.card-static, .sensor-mini-card');
    if (threshold && parseFloat(value) > threshold) card.classList.add('animate-pulse-critical');
    else card.classList.remove('animate-pulse-critical');
}

// ============================================
// BATCH STATUS REAL-TIME UPDATE
// ============================================
let lastBatchStatus = '{{ $activeBatch ? $activeBatch->status : ($latestBatch && $latestBatch->status === "failed" ? "failed" : "idle") }}';

function updateBatchStatus(batchData) {
    const activeBatch = batchData.active;
    const latestBatch = batchData.latest;
    const statusBadge = document.getElementById('batch-status-badge');
    const notesEl = document.querySelector('.batch-active-state .mb-4:last-of-type, .batch-failed-state .mb-5:last-of-type');

    // Tentukan status saat ini
    let currentStatus = 'idle';
    if (activeBatch) {
        currentStatus = activeBatch.status;
    } else if (latestBatch && latestBatch.status === 'failed') {
        currentStatus = 'failed';
    }

    // Skip update jika status tidak berubah
    if (currentStatus === lastBatchStatus) return;
    lastBatchStatus = currentStatus;

    // Reload halaman untuk update card batch (karena struktur HTML berbeda per state)
    // Ini memastikan tombol, badge, dan layout berubah sesuai status
    location.reload();
}

// ============================================
// CHART REFRESH (setiap 5 detik)
// ============================================
setInterval(refreshChart, 5000);

async function refreshChart() {
    // Jangan refresh jika chart sedang dalam mode reset
    if (chartResetUntil && Date.now() < chartResetUntil) return;

    try {
        const res = await fetch(`/api/dashboard/chart?device_id=${DEVICE_ID}&range=${currentRange}`, {
            headers: { 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' }
        });
        const data = await res.json();
        sensorChart.data.labels = data.labels;
        sensorChart.data.datasets[0].data = data.internal_temp;
        sensorChart.data.datasets[1].data = data.amonia_level;
        sensorChart.data.datasets[2].data = data.room_temp;
        sensorChart.data.datasets[3].data = data.humidity;
        sensorChart.update('none');
    } catch (e) { console.error('Chart error:', e); }
}

// ============================================
// FAN CONTROL
// ============================================
function toggleMode() {
    const newMode = currentMode === 'AUTO' ? 'MANUAL' : 'AUTO';
    sendFanControl(newMode, newMode === 'AUTO' ? 'OFF' : currentFanStatus);
}

function toggleFan() {
    if (currentMode === 'AUTO') return;
    sendFanControl(currentMode, currentFanStatus === 'ON' ? 'OFF' : 'ON');
}

async function sendFanControl(mode, fanStatus) {
    updateFanUI(mode, fanStatus);
    try {
        const res = await fetch('/api/device/control', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ device_id: DEVICE_ID, mode, fan_status: fanStatus })
        });
        const data = await res.json();
        if (data.success) updateFanUI(data.fan.mode, data.fan.status);
    } catch (e) { console.error('Fan error:', e); updateFanUI(currentMode, currentFanStatus); }
}

function updateFanUI(mode, status) {
    currentMode = mode; currentFanStatus = status;
    const toggle = document.getElementById('mode-toggle');
    const powerBtn = document.getElementById('power-btn');
    const badge = document.getElementById('fan-status-badge');
    const text = document.getElementById('fan-status-text');
    const modeBadge = document.getElementById('device-mode-badge');

    if (toggle) toggle.classList.toggle('active', mode === 'MANUAL');
    document.getElementById('mode-label-auto').style.color = mode === 'AUTO' ? 'var(--color-accent-green)' : 'var(--color-text-muted)';
    document.getElementById('mode-label-manual').style.color = mode === 'MANUAL' ? 'var(--color-accent-amber)' : 'var(--color-text-muted)';
    if (powerBtn) { powerBtn.disabled = mode === 'AUTO'; powerBtn.classList.toggle('on', status === 'ON'); }
    if (badge) { badge.className = 'badge ' + (status === 'ON' ? 'badge-green' : 'badge-red'); badge.textContent = status; }
    if (modeBadge) { modeBadge.className = 'badge ' + (mode === 'AUTO' ? 'badge-green' : 'badge-amber'); modeBadge.textContent = mode; }
    if (text) text.textContent = mode === 'AUTO' ? 'Mode AUTO — System controlled' : `Mode MANUAL — Fan ${status}`;
}

// ============================================
// FLATPICKR INITIALIZATION
// ============================================
flatpickr('#pdf-date-from', { allowInput: true, dateFormat: 'Y-m-d' });
flatpickr('#pdf-date-to', { allowInput: true, dateFormat: 'Y-m-d' });

// ============================================
// BATCH: Confirm End Production (SweetAlert2)
// ============================================
function confirmEndBatch(status = 'active') {
    if (status === 'failed') {
        const msg = 'Konfirmasi bahwa Anda telah mengecek alat yang gagal ini? Peringatan akan dihapus dari layar.';
        if (typeof Swal === 'undefined') {
            if (confirm(msg)) {
                document.getElementById('form-end-batch-failed').submit();
            }
            return;
        }

        Swal.fire({
            title: 'Tutup Peringatan?',
            html: 'Konfirmasi bahwa Anda telah mengecek alat yang gagal ini?<br><br>Peringatan akan dihapus dari layar.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Bersihkan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#0D9488',
            cancelButtonColor: '#334155',
            background: 'var(--color-bg-card, #1e293b)',
            color: 'var(--color-text-primary, #f1f5f9)',
            reverseButtons: true,
            focusCancel: true,
            customClass: {
                popup:         'swal-batch-popup',
                confirmButton: 'swal-batch-confirm',
                cancelButton:  'swal-batch-cancel',
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const btn = document.getElementById('btn-end-batch-failed');
                if (btn) { btn.disabled = true; btn.innerHTML = '<span style="opacity:0.7">Membersihkan...</span>'; }
                document.getElementById('form-end-batch-failed').submit();
            }
        });
    } else {
        // Fallback to native confirm if SweetAlert2 not loaded
        if (typeof Swal === 'undefined') {
            if (confirm('Akhiri produksi ini?\n\nBatch akan ditandai selesai dan data fermentasi akan disimpan.')) {
                document.getElementById('form-end-batch').submit();
            }
            return;
        }

        Swal.fire({
            title: 'Akhiri Sesi Produksi?',
            html: 'Batch ini akan ditandai <strong>selesai</strong> dan waktu selesai akan dicatat.<br><br>Tindakan ini <u>tidak dapat dibatalkan</u>.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '✓ Ya, Akhiri Produksi',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#0D9488',
            cancelButtonColor: '#334155',
            background: 'var(--color-bg-card, #1e293b)',
            color: 'var(--color-text-primary, #f1f5f9)',
            reverseButtons: true,
            focusCancel: true,
            customClass: {
                popup:         'swal-batch-popup',
                confirmButton: 'swal-batch-confirm',
                cancelButton:  'swal-batch-cancel',
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const btn = document.getElementById('btn-end-batch');
                if (btn) { btn.disabled = true; btn.innerHTML = '<span style="opacity:0.7">Mengakhiri...</span>'; }
                document.getElementById('form-end-batch').submit();
            }
        });
    }
}

function confirmCancelBatch(batchId) {
    if (typeof Swal === 'undefined') {
        if (confirm('Batalkan dan hapus batch ini?\n\nData batch akan dihapus permanen.')) {
            document.getElementById('form-delete-batch-' + batchId).submit();
        }
        return;
    }

    Swal.fire({
        title: 'Batalkan & Hapus Batch?',
        html: 'Batch ini akan dibatalkan dan <strong>dihapus secara permanen</strong> dari riwayat.<br><br>Tindakan ini <u>tidak dapat dibatalkan</u>.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus Batch',
        cancelButtonText: 'Kembali',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#334155',
        background: 'var(--color-bg-card, #1e293b)',
        color: 'var(--color-text-primary, #f1f5f9)',
        reverseButtons: true,
        focusCancel: true
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('form-delete-batch-' + batchId).submit();
        }
    });
}

// ============================================
// HISTORICAL DATA TABLE
// ============================================
function addTableRow(sensors, fanStatus) {
    const tbody = document.getElementById('history-tbody');
    if (!tbody) return;

    // Remove empty state row if present
    const emptyRow = document.getElementById('empty-table-row');
    if (emptyRow) emptyRow.remove();

    const tr = document.createElement('tr');
    tr.classList.add('new-row');

    const ts = sensors.full_timestamp || sensors.timestamp || '--';
    const tsParts = ts.split(' ');
    let datePart = '', timePart = '';
    if (tsParts.length >= 4) {
        datePart = tsParts.slice(0, 3).join(' ');
        timePart = tsParts[3];
    } else {
        timePart = ts;
    }

    const fmt = (v, dec = 1) => v !== null && v !== undefined ? parseFloat(v).toFixed(dec) : '--';
    const fanClass = fanStatus === 'ON' ? 'badge-green' : 'badge-muted';

    tr.innerHTML = `
        <td>
            <span class="text-xs font-medium">${datePart}</span><br>
            <span class="text-xs" style="color: var(--color-text-muted);">${timePart}</span>
        </td>
        <td>
            <span style="color: var(--color-accent-red); font-weight: 600;">${fmt(sensors.internal_temp)}°C</span>
        </td>
        <td>
            <span style="color: var(--color-accent-blue); font-weight: 600;">${fmt(sensors.room_temp)}°C</span>
        </td>
        <td>
            <span style="color: var(--color-accent-cyan); font-weight: 600;">${fmt(sensors.humidity)}%</span>
        </td>
        <td>
            <span style="color: var(--color-accent-amber); font-weight: 600;">${fmt(sensors.amonia_level)} ppm</span>
        </td>
        <td>
            <span class="badge ${fanClass}">${fanStatus}</span>
        </td>
    `;

    // Prepend to top of tbody
    tbody.insertBefore(tr, tbody.firstChild);

    // Remove flash animation after it plays
    setTimeout(() => tr.classList.remove('new-row'), 1500);

    // Enforce max rows
    while (tbody.children.length > MAX_TABLE_ROWS) {
        tbody.removeChild(tbody.lastChild);
    }

    // Update row counter
    const counter = document.getElementById('table-row-count');
    if (counter) counter.textContent = Math.min(tbody.children.length, MAX_TABLE_ROWS) + ' data';
}

// ============================================
// EXPORT PDF TABS LOGIC
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    const tabBtns = document.querySelectorAll('.export-tab-btn');
    const forms = document.querySelectorAll('.export-form');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Remove active classes from all buttons
            tabBtns.forEach(b => {
                b.classList.remove('active');
                b.style.borderColor = 'transparent';
                b.style.color = 'var(--color-text-muted)';
            });

            // Hide all forms
            forms.forEach(f => {
                f.classList.remove('active');
                f.classList.add('hidden');
            });

            // Activate clicked button
            btn.classList.add('active');
            btn.style.borderColor = 'var(--color-accent-teal)';
            btn.style.color = 'var(--color-accent-teal)';

            // Show corresponding form
            const targetId = btn.getAttribute('data-target');
            const targetForm = document.getElementById(targetId);
            if (targetForm) {
                targetForm.classList.remove('hidden');
                targetForm.classList.add('active');
            }
        });
    });
});
</script>
@endpush
