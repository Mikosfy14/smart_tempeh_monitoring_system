<script>
const DEVICE_ID = {{ $activeDevice->id ?? 'null' }};
let currentRange = '6h';
let sensorChart = null;
let currentMode = '{{ $activeDevice->operation_mode ?? "AUTO" }}';
let currentFanStatus = '{{ $activeDevice->fan_status ?? "OFF" }}';

if (DEVICE_ID) {
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
                legend: { position: 'top', labels: { color: '#94a3b8', usePointStyle: true, pointStyle: 'circle', padding: 20, font: { family: 'Inter', size: 12 } } },
                tooltip: { backgroundColor: '#1e293b', titleColor: '#f1f5f9', bodyColor: '#94a3b8', borderColor: '#334155', borderWidth: 1, cornerRadius: 8, padding: 12 }
            },
            scales: {
                x: { grid: { color: 'rgba(30,41,59,0.5)' }, ticks: { color: '#64748b', font: { family: 'Inter', size: 11 } } },
                y: { grid: { color: 'rgba(30,41,59,0.5)' }, ticks: { color: '#64748b', font: { family: 'Inter', size: 11 } } }
            }
        }
    });

    document.querySelectorAll('.chart-range-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.chart-range-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentRange = this.dataset.range;
            refreshChart();
        });
    });

    setInterval(pollSensorData, 5000);
    setInterval(refreshChart, 60000);
}

async function pollSensorData() {
    if (!DEVICE_ID) return;
    try {
        const res = await fetch(`/api/dashboard/live?device_id=${DEVICE_ID}`, { headers: { 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' } });
        const data = await res.json();
        if (data.sensors) {
            updateVal('val-internal-temp', data.sensors.internal_temp, 35);
            updateVal('val-amonia', data.sensors.amonia_level, 25);
            updateVal('val-room-temp', data.sensors.room_temp, null);
            updateVal('val-humidity', data.sensors.humidity, null);
            document.getElementById('last-update').textContent = data.sensors.timestamp;
        }
        if (data.fan) updateFanUI(data.fan.mode, data.fan.status);
    } catch (e) { console.error('Poll error:', e); }
}

function updateVal(id, value, threshold) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = value !== null ? parseFloat(value).toFixed(1) : '--';
    const card = el.closest('.metric-card');
    if (threshold && parseFloat(value) > threshold) card.classList.add('animate-pulse-critical');
    else card.classList.remove('animate-pulse-critical');
}

async function refreshChart() {
    if (!DEVICE_ID || !sensorChart) return;
    try {
        const res = await fetch(`/api/dashboard/chart?device_id=${DEVICE_ID}&range=${currentRange}`, { headers: { 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' } });
        const data = await res.json();
        sensorChart.data.labels = data.labels;
        sensorChart.data.datasets[0].data = data.internal_temp;
        sensorChart.data.datasets[1].data = data.amonia_level;
        sensorChart.data.datasets[2].data = data.room_temp;
        sensorChart.data.datasets[3].data = data.humidity;
        sensorChart.update('none');
    } catch (e) { console.error('Chart error:', e); }
}

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

    toggle.classList.toggle('active', mode === 'MANUAL');
    document.getElementById('mode-label-auto').style.color = mode === 'AUTO' ? 'var(--color-accent-green)' : 'var(--color-text-muted)';
    document.getElementById('mode-label-manual').style.color = mode === 'MANUAL' ? 'var(--color-accent-amber)' : 'var(--color-text-muted)';
    powerBtn.disabled = mode === 'AUTO';
    powerBtn.classList.toggle('on', status === 'ON');
    badge.className = 'badge ' + (status === 'ON' ? 'badge-green' : 'badge-red');
    badge.textContent = status;
    modeBadge.className = 'badge ' + (mode === 'AUTO' ? 'badge-green' : 'badge-amber');
    modeBadge.textContent = mode;
    text.textContent = mode === 'AUTO' ? 'Mode AUTO — System controlled' : `Mode MANUAL — Fan ${status}`;
}
</script>
