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
            <span class="status-dot {{ $device->latestLog ? 'status-dot--online' : 'status-dot--offline' }}"></span>
        </div>
    </div>

    {{-- =============================== --}}
    {{-- SENSOR CARDS ROW --}}
    {{-- =============================== --}}
    @php $log = $device->latestLog; @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="card-static sensor-mini-card" style="border-left: 3px solid var(--color-accent-red);" id="card-internal-temp">
            <span class="text-xs" style="color: var(--color-text-muted);">Suhu Internal</span>
            <span class="text-2xl font-bold" style="color: var(--color-accent-red);" id="val-internal-temp">{{ $log ? number_format($log->internal_temp, 1) : '--' }}</span>
            <span class="text-xs" style="color: var(--color-text-muted);">°C</span>
        </div>
        <div class="card-static sensor-mini-card" style="border-left: 3px solid var(--color-accent-amber);" id="card-amonia">
            <span class="text-xs" style="color: var(--color-text-muted);">Amonia</span>
            <span class="text-2xl font-bold" style="color: var(--color-accent-amber);" id="val-amonia">{{ $log ? number_format($log->amonia_level, 1) : '--' }}</span>
            <span class="text-xs" style="color: var(--color-text-muted);">ppm</span>
        </div>
        <div class="card-static sensor-mini-card" style="border-left: 3px solid var(--color-accent-blue);" id="card-room-temp">
            <span class="text-xs" style="color: var(--color-text-muted);">Suhu Ruang</span>
            <span class="text-2xl font-bold" style="color: var(--color-accent-blue);" id="val-room-temp">{{ $log ? number_format($log->room_temp, 1) : '--' }}</span>
            <span class="text-xs" style="color: var(--color-text-muted);">°C</span>
        </div>
        <div class="card-static sensor-mini-card" style="border-left: 3px solid var(--color-accent-cyan);" id="card-humidity">
            <span class="text-xs" style="color: var(--color-text-muted);">Kelembapan</span>
            <span class="text-2xl font-bold" style="color: var(--color-accent-cyan);" id="val-humidity">{{ $log ? number_format($log->humidity, 1) : '--' }}</span>
            <span class="text-xs" style="color: var(--color-text-muted);">%</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- =============================== --}}
        {{-- CHART SECTION (2/3 width) --}}
        {{-- =============================== --}}
        <div class="lg:col-span-2 card-static" style="position: relative;">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold">Grafik Sensor</h2>
                <div class="flex items-center gap-1">
                    <button class="chart-range-btn" data-range="1h">1h</button>
                    <button class="chart-range-btn active" data-range="6h">6h</button>
                    <button class="chart-range-btn" data-range="24h">24h</button>
                    <button class="chart-range-btn" data-range="7d">7d</button>
                </div>
            </div>

            {{-- Loading Overlay --}}
            <div id="chart-loading" style="position: absolute; inset: 0; display: none; z-index: 10; border-radius: var(--radius-lg); background: rgba(var(--color-bg-primary-rgb, 15, 23, 42), 0.85); align-items: center; justify-content: center; flex-direction: column; gap: 12px;">
                <div style="width: 36px; height: 36px; border: 3px solid var(--color-border-card); border-top-color: var(--color-accent-teal); border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
                <span class="text-sm" style="color: var(--color-text-secondary);">Memuat data...</span>
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
                <h3 class="text-sm font-bold mb-4">Cetak Laporan PDF</h3>
                <form action="{{ route('device.export-pdf', $device->id) }}" method="GET" id="pdf-form">
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
                        Download PDF
                    </button>
                </form>
            </div>

            {{-- Edit Device Cue --}}
            <div class="card-static">
                <h3 class="text-sm font-bold mb-3">Pengaturan Alat</h3>
                <p class="text-xs mb-4" style="color: var(--color-text-muted);">
                    Ubah nama alat atau sesuaikan ambang batas notifikasi peringatan sensor.
                </p>
                <a href="{{ route('device.edit', $device->id) }}" class="btn btn-secondary btn-sm w-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    Edit Detail Alat
                </a>
            </div>

        </div>
    </div>

</div>

<style>
@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>
@endsection

@push('scripts')
<script>
const DEVICE_ID = {{ $device->id }};
let currentRange = '6h';
let sensorChart = null;
let currentMode = '{{ $device->operation_mode }}';
let currentFanStatus = '{{ $device->fan_status }}';

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
// LOADING STATE HELPERS
// ============================================
function showChartLoading() {
    const el = document.getElementById('chart-loading');
    el.style.display = 'flex';
}
function hideChartLoading() {
    const el = document.getElementById('chart-loading');
    el.style.display = 'none';
}

// ============================================
// LIVE POLLING (every 5s)
// ============================================
setInterval(pollSensorData, 5000);

async function pollSensorData() {
    try {
        const res = await fetch(`/api/dashboard/live?device_id=${DEVICE_ID}`, {
            headers: { 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' }
        });
        const data = await res.json();
        
        const thresholdTemp = {{ $device->temp_threshold ?? 35.0 }};
        const thresholdAmonia = {{ $device->amonia_threshold ?? 25.0 }};
        const thresholdHumidity = {{ $device->humidity_threshold ?? 90.0 }};

        if (data.sensors) {
            updateVal('val-internal-temp', data.sensors.internal_temp, thresholdTemp);
            updateVal('val-amonia', data.sensors.amonia_level, thresholdAmonia);
            updateVal('val-room-temp', data.sensors.room_temp, null);
            updateVal('val-humidity', data.sensors.humidity, thresholdHumidity);
            document.getElementById('last-update').textContent = data.sensors.timestamp;
        }
        if (data.fan) updateFanUI(data.fan.mode, data.fan.status);
    } catch (e) { console.error('Poll error:', e); }
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
// CHART REFRESH
// ============================================
setInterval(refreshChart, 60000);

async function refreshChart() {
    showChartLoading();
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
    finally { hideChartLoading(); }
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
</script>
@endpush
