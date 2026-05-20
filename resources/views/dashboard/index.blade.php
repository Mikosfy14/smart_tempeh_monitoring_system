@extends('layouts.app')

@section('title', 'Dashboard — Smart Tempeh Monitoring')

@section('content')
<div class="stagger-children">

    @if($devices->count() === 0)
    {{-- =============================== --}}
    {{-- EMPTY STATE --}}
    {{-- =============================== --}}
    <div class="card-static text-center py-20">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl mb-6" style="background: rgba(45, 212, 191, 0.1);">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" style="color: var(--color-accent-teal);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
            </svg>
        </div>
        <h2 class="text-2xl font-bold mb-3">Belum Ada Rak Terdaftar</h2>
        <p class="text-sm mb-10 mx-auto" style="color: var(--color-text-secondary); max-width: 400px;">
            Daftarkan rak pertama Anda dengan memasukkan Device ID yang tercetak pada perangkat ESP32.
        </p>
        <button class="btn btn-primary btn-lg" onclick="openModal('register-modal')" id="btn-empty-register">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Daftarkan Rak Pertama Anda
        </button>
    </div>

    @else
    {{-- =============================== --}}
    {{-- MULTI-CARD GRID --}}
    {{-- =============================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6" id="device-cards-grid">
        @foreach($devices as $device)
        @php $log = $device->latestLog; @endphp
        <div class="card-static device-card" id="device-card-{{ $device->id }}" data-device-id="{{ $device->id }}">

            {{-- Card Header --}}
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold">{{ $device->label_rak ?? $device->device_name }}</h3>
                    <p class="text-xs mt-1" style="color: var(--color-text-muted);">ID: {{ $device->device_id }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="badge {{ $device->is_online ? 'badge-green' : 'badge-red' }}" style="font-size: 0.6rem; padding: 2px 6px;" data-online-badge>{{ $device->is_online ? 'Online' : 'Offline' }}</span>
                    <button class="btn-icon" style="color: var(--color-text-muted);" onclick="unregisterDevice({{ $device->id }}, '{{ $device->label_rak ?? $device->device_id }}')" title="Lepas alat">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            @php $cardSensorStatus = $device->sensor_status; @endphp
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="sensor-mini-card" style="border-left: 3px solid var(--color-accent-red);">
                    <div class="flex items-center justify-between">
                        <span class="text-xs" style="color: var(--color-text-muted);">Suhu Internal</span>
                        <span class="sensor-status-dot {{ $cardSensorStatus['ds18b20'] === 'ok' ? 'sensor-status--ok' : 'sensor-status--error' }}" data-sensor-status="ds18b20"></span>
                    </div>
                    <span class="text-lg font-bold" style="color: var(--color-accent-red);" data-sensor="internal_temp">{{ $log ? number_format($log->internal_temp, 1).'°C' : '--' }}</span>
                </div>
                <div class="sensor-mini-card" style="border-left: 3px solid var(--color-accent-amber);">
                    <div class="flex items-center justify-between">
                        <span class="text-xs" style="color: var(--color-text-muted);">Amonia</span>
                        <span class="sensor-status-dot {{ $cardSensorStatus['mq135'] === 'ok' ? 'sensor-status--ok' : 'sensor-status--error' }}" data-sensor-status="mq135"></span>
                    </div>
                    <span class="text-lg font-bold" style="color: var(--color-accent-amber);" data-sensor="amonia_level">{{ $log ? number_format($log->amonia_level, 1).' ppm' : '--' }}</span>
                </div>
                <div class="sensor-mini-card" style="border-left: 3px solid var(--color-accent-blue);">
                    <div class="flex items-center justify-between">
                        <span class="text-xs" style="color: var(--color-text-muted);">Suhu Ruang</span>
                        <span class="sensor-status-dot {{ $cardSensorStatus['dht22'] === 'ok' ? 'sensor-status--ok' : 'sensor-status--error' }}" data-sensor-status="dht22"></span>
                    </div>
                    <span class="text-lg font-bold" style="color: var(--color-accent-blue);" data-sensor="room_temp">{{ $log ? number_format($log->room_temp, 1).'°C' : '--' }}</span>
                </div>
                <div class="sensor-mini-card" style="border-left: 3px solid var(--color-accent-cyan);">
                    <div class="flex items-center justify-between">
                        <span class="text-xs" style="color: var(--color-text-muted);">Kelembapan</span>
                        <span class="sensor-status-dot {{ $cardSensorStatus['dht22'] === 'ok' ? 'sensor-status--ok' : 'sensor-status--error' }}" data-sensor-status="dht22-hum"></span>
                    </div>
                    <span class="text-lg font-bold" style="color: var(--color-accent-cyan);" data-sensor="humidity">{{ $log ? number_format($log->humidity, 1).'%' : '--' }}</span>
                </div>
            </div>

            {{-- Fan Control Row --}}
            <div class="flex items-center justify-between py-3" style="border-top: 1px solid var(--color-border-card);">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-medium" data-mode-label style="color: {{ $device->operation_mode === 'AUTO' ? 'var(--color-accent-green)' : 'var(--color-accent-amber)' }};">{{ $device->operation_mode }}</span>
                    <button class="toggle-switch toggle-switch--sm {{ $device->operation_mode === 'MANUAL' ? 'active' : '' }}" data-mode-toggle onclick="toggleCardMode({{ $device->id }})"></button>
                </div>
                <div class="flex items-center gap-2">
                    <span class="badge {{ $device->fan_status === 'ON' ? 'badge-green' : 'badge-red' }}" data-fan-badge>{{ $device->fan_status }}</span>
                    <button class="power-btn power-btn--sm {{ $device->fan_status === 'ON' ? 'on' : '' }}" data-fan-btn {{ $device->operation_mode === 'AUTO' ? 'disabled' : '' }} onclick="toggleCardFan({{ $device->id }})">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Last Update --}}
            <p class="text-xs mt-2" style="color: var(--color-text-muted);" data-last-update>
                {{ $log ? 'Update: '.$log->created_at->diffForHumans() : 'Belum ada data' }}
            </p>

            {{-- Detail Button --}}
            <a href="{{ route('device.detail', $device->id) }}" class="btn btn-secondary btn-sm w-full mt-3" style="text-align: center;" id="btn-detail-{{ $device->id }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Lihat Detail
            </a>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- UNREGISTER CONFIRM MODAL --}}
<div class="modal-overlay" id="unregister-modal">
    <div class="modal-content text-center">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full mb-4" style="background: rgba(248, 113, 113, 0.1);">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" style="color: var(--color-accent-red);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
        </div>
        <h3 class="text-lg font-bold mb-2">Lepas Alat</h3>
        <p class="text-sm mb-6" style="color: var(--color-text-secondary);">Lepas <strong id="unreg-device-name" class="text-white"></strong>? Data histori sensor tetap tersimpan.</p>
        <input type="hidden" id="unreg-device-id">
        <div class="flex gap-3">
            <button class="btn btn-secondary flex-1" onclick="closeModal('unregister-modal')">Batal</button>
            <button class="btn btn-danger flex-1" onclick="confirmUnregister()" id="btn-confirm-unreg">Lepas</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // ============================================
    // MULTI-CARD POLLING — Poll all device cards every 5s
    // ============================================
    const deviceCards = document.querySelectorAll('.device-card');
    const cardStates = {};

    // Initialize card states
    deviceCards.forEach(card => {
        const id = card.dataset.deviceId;
        const modeLabel = card.querySelector('[data-mode-label]');
        cardStates[id] = {
            mode: modeLabel ? modeLabel.textContent.trim() : 'AUTO',
            fanStatus: card.querySelector('[data-fan-badge]')?.textContent.trim() || 'OFF',
        };
    });

    // Poll all cards
    if (deviceCards.length > 0) {
        setInterval(pollAllCards, 5000);
    }

    async function pollAllCards() {
        for (const card of deviceCards) {
            const deviceId = card.dataset.deviceId;
            try {
                const res = await fetch(`/api/dashboard/live?device_id=${deviceId}`, {
                    headers: {
                        'X-CSRF-TOKEN': window.csrfToken,
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.sensors) {
                    updateCardSensors(card, data.sensors);
                }
                if (data.fan) {
                    updateCardFan(card, deviceId, data.fan.mode, data.fan.status);
                }
                updateCardStatus(card, data);
            } catch (e) {
                /* silent fail */ }
        }
    }

    function updateCardSensors(card, s) {
        const set = (attr, val, suffix) => {
            const el = card.querySelector(`[data-sensor="${attr}"]`);
            if (el) el.textContent = val !== null ? parseFloat(val).toFixed(1) + suffix : '--';
        };
        set('internal_temp', s.internal_temp, '°C');
        set('amonia_level', s.amonia_level, ' ppm');
        set('room_temp', s.room_temp, '°C');
        set('humidity', s.humidity, '%');
        const upd = card.querySelector('[data-last-update]');
        if (upd && s.timestamp) upd.textContent = 'Update: ' + s.timestamp;

        // Critical pulse if temp > 35
        if (parseFloat(s.internal_temp) > 35) {
            card.classList.add('animate-pulse-critical');
        } else {
            card.classList.remove('animate-pulse-critical');
        }
    }

    function updateCardStatus(card, data) {
        // Update online/offline badge
        if (data.is_online !== undefined) {
            const onlineBadge = card.querySelector('[data-online-badge]');
            if (onlineBadge) {
                onlineBadge.textContent = data.is_online ? 'Online' : 'Offline';
                onlineBadge.className = 'badge ' + (data.is_online ? 'badge-green' : 'badge-red');
                onlineBadge.style.cssText = 'font-size: 0.6rem; padding: 2px 6px;';
            }
        }

        // Update per-sensor status dots
        if (data.sensor_status) {
            const updateDot = (attr, status) => {
                const dot = card.querySelector(`[data-sensor-status="${attr}"]`);
                if (dot) dot.className = 'sensor-status-dot ' + (status === 'ok' ? 'sensor-status--ok' : 'sensor-status--error');
            };
            updateDot('ds18b20', data.sensor_status.ds18b20);
            updateDot('mq135', data.sensor_status.mq135);
            updateDot('dht22', data.sensor_status.dht22);
            updateDot('dht22-hum', data.sensor_status.dht22);
        }
    }

    function updateCardFan(card, deviceId, mode, status) {
        cardStates[deviceId] = {
            mode,
            fanStatus: status
        };
        const modeLabel = card.querySelector('[data-mode-label]');
        const modeToggle = card.querySelector('[data-mode-toggle]');
        const fanBadge = card.querySelector('[data-fan-badge]');
        const fanBtn = card.querySelector('[data-fan-btn]');

        if (modeLabel) {
            modeLabel.textContent = mode;
            modeLabel.style.color = mode === 'AUTO' ? 'var(--color-accent-green)' : 'var(--color-accent-amber)';
        }
        if (modeToggle) modeToggle.classList.toggle('active', mode === 'MANUAL');
        if (fanBadge) {
            fanBadge.textContent = status;
            fanBadge.className = 'badge ' + (status === 'ON' ? 'badge-green' : 'badge-red');
        }
        if (fanBtn) {
            fanBtn.classList.toggle('on', status === 'ON');
            fanBtn.disabled = mode === 'AUTO';
        }
    }

    // ============================================
    // FAN CONTROL PER CARD
    // ============================================
    async function toggleCardMode(deviceId) {
        const state = cardStates[deviceId];
        const newMode = state.mode === 'AUTO' ? 'MANUAL' : 'AUTO';
        const newFan = newMode === 'AUTO' ? 'OFF' : state.fanStatus;
        await sendCardFanControl(deviceId, newMode, newFan);
    }

    async function toggleCardFan(deviceId) {
        const state = cardStates[deviceId];
        if (state.mode === 'AUTO') return;
        const newFan = state.fanStatus === 'ON' ? 'OFF' : 'ON';
        await sendCardFanControl(deviceId, state.mode, newFan);
    }

    async function sendCardFanControl(deviceId, mode, fanStatus) {
        const card = document.getElementById('device-card-' + deviceId);
        updateCardFan(card, deviceId, mode, fanStatus); // optimistic

        try {
            const res = await fetch('/api/device/control', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    device_id: deviceId,
                    mode,
                    fan_status: fanStatus
                })
            });
            const data = await res.json();
            if (data.success) updateCardFan(card, deviceId, data.fan.mode, data.fan.status);
        } catch (e) {
            console.error('Fan control error:', e);
        }
    }

    // ============================================
    // UNREGISTER DEVICE
    // ============================================
    function unregisterDevice(id, name) {
        document.getElementById('unreg-device-id').value = id;
        document.getElementById('unreg-device-name').textContent = name;
        openModal('unregister-modal');
    }

    async function confirmUnregister() {
        const deviceId = document.getElementById('unreg-device-id').value;
        try {
            await fetch('{{ route("device.unregister") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    device_id: deviceId
                })
            });
            closeModal('unregister-modal');
            window.location.reload();
        } catch (e) {
            alert('Gagal melepas alat.');
        }
    }
</script>
@endpush