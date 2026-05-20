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
