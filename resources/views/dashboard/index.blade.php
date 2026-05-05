@extends('layouts.app')

@section('title', 'Dashboard — Smart Tempeh Monitoring')

@section('content')
<div class="stagger-children">

    @if(!$activeDevice)
        <div class="card-static text-center py-16">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mx-auto mb-4" style="color: var(--color-text-muted);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
            </svg>
            <h2 class="text-xl font-bold mb-2">No Device Connected</h2>
            <p class="text-sm" style="color: var(--color-text-secondary);">Contact your administrator to link a monitoring device to your account.</p>
        </div>
    @else

    {{-- SENSOR METRIC CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <div class="metric-card metric-card--temp" id="card-internal-temp">
            <div class="flex items-center gap-3 mb-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg" style="background: rgba(248, 113, 113, 0.1);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" style="color: var(--color-accent-red);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                </div>
                <span class="metric-label">Internal Temp</span>
            </div>
            <div class="metric-value" style="color: var(--color-accent-red);" id="val-internal-temp">{{ $latestLog ? number_format($latestLog->internal_temp, 1) : '--' }}</div>
            <span class="text-sm" style="color: var(--color-text-muted);">°C</span>
        </div>

        <div class="metric-card metric-card--amonia" id="card-amonia">
            <div class="flex items-center gap-3 mb-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg" style="background: rgba(251, 191, 36, 0.1);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" style="color: var(--color-accent-amber);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" /></svg>
                </div>
                <span class="metric-label">Ammonia Level</span>
            </div>
            <div class="metric-value" style="color: var(--color-accent-amber);" id="val-amonia">{{ $latestLog ? number_format($latestLog->amonia_level, 1) : '--' }}</div>
            <span class="text-sm" style="color: var(--color-text-muted);">ppm</span>
        </div>

        <div class="metric-card metric-card--room" id="card-room-temp">
            <div class="flex items-center gap-3 mb-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg" style="background: rgba(96, 165, 250, 0.1);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" style="color: var(--color-accent-blue);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                </div>
                <span class="metric-label">Room Temp</span>
            </div>
            <div class="metric-value" style="color: var(--color-accent-blue);" id="val-room-temp">{{ $latestLog ? number_format($latestLog->room_temp, 1) : '--' }}</div>
            <span class="text-sm" style="color: var(--color-text-muted);">°C</span>
        </div>

        <div class="metric-card metric-card--humidity" id="card-humidity">
            <div class="flex items-center gap-3 mb-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg" style="background: rgba(34, 211, 238, 0.1);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" style="color: var(--color-accent-cyan);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707" /></svg>
                </div>
                <span class="metric-label">Humidity</span>
            </div>
            <div class="metric-value" style="color: var(--color-accent-cyan);" id="val-humidity">{{ $latestLog ? number_format($latestLog->humidity, 1) : '--' }}</div>
            <span class="text-sm" style="color: var(--color-text-muted);">%</span>
        </div>
    </div>

    {{-- HISTORY CHART --}}
    <div class="card-static mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold">Sensor History</h2>
            <div class="flex gap-2">
                <button class="btn btn-sm btn-secondary chart-range-btn" data-range="1h" id="chart-btn-1h">1H</button>
                <button class="btn btn-sm btn-secondary chart-range-btn active" data-range="6h" id="chart-btn-6h">6H</button>
                <button class="btn btn-sm btn-secondary chart-range-btn" data-range="24h" id="chart-btn-24h">24H</button>
                <button class="btn btn-sm btn-secondary chart-range-btn" data-range="7d" id="chart-btn-7d">7D</button>
            </div>
        </div>
        <div style="height: 320px; position: relative;">
            <canvas id="sensor-chart"></canvas>
        </div>
    </div>

    {{-- FAN CONTROL + DEVICE INFO --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="card-static">
            <h2 class="text-lg font-bold mb-6">Fan Control</h2>
            <div class="flex items-center gap-8">
                <div class="text-center">
                    <p class="text-sm mb-3" style="color: var(--color-text-secondary);">Mode</p>
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-medium" id="mode-label-auto" style="color: var(--color-accent-green);">AUTO</span>
                        <button class="toggle-switch {{ $activeDevice->operation_mode === 'MANUAL' ? 'active' : '' }}" id="mode-toggle" onclick="toggleMode()"></button>
                        <span class="text-sm font-medium" id="mode-label-manual" style="color: var(--color-text-muted);">MANUAL</span>
                    </div>
                </div>
                <div class="text-center flex-1">
                    <p class="text-sm mb-3" style="color: var(--color-text-secondary);">Power</p>
                    <button class="power-btn mx-auto {{ $activeDevice->fan_status === 'ON' ? 'on' : '' }}" id="power-btn" {{ $activeDevice->operation_mode === 'AUTO' ? 'disabled' : '' }} onclick="toggleFan()">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    </button>
                </div>
                <div class="text-center">
                    <p class="text-sm mb-3" style="color: var(--color-text-secondary);">Status</p>
                    <span class="badge {{ $activeDevice->fan_status === 'ON' ? 'badge-green' : 'badge-red' }}" id="fan-status-badge">{{ $activeDevice->fan_status }}</span>
                </div>
            </div>
            <p class="text-sm mt-6" style="color: var(--color-text-muted);" id="fan-status-text">
                @if($activeDevice->operation_mode === 'AUTO') Mode AUTO — System controlled @else Mode MANUAL — Fan {{ $activeDevice->fan_status }} @endif
            </p>
        </div>

        <div class="card-static">
            <h2 class="text-lg font-bold mb-4">Device Info</h2>
            <div class="space-y-3">
                <div class="flex justify-between py-2" style="border-bottom: 1px solid var(--color-border-card);"><span class="text-sm" style="color: var(--color-text-secondary);">Name</span><span class="text-sm font-medium">{{ $activeDevice->device_name }}</span></div>
                <div class="flex justify-between py-2" style="border-bottom: 1px solid var(--color-border-card);"><span class="text-sm" style="color: var(--color-text-secondary);">Token</span><span class="text-sm font-mono" style="color: var(--color-accent-teal);">{{ Str::limit($activeDevice->device_token, 20) }}</span></div>
                <div class="flex justify-between py-2" style="border-bottom: 1px solid var(--color-border-card);"><span class="text-sm" style="color: var(--color-text-secondary);">Mode</span><span class="badge {{ $activeDevice->operation_mode === 'AUTO' ? 'badge-green' : 'badge-amber' }}" id="device-mode-badge">{{ $activeDevice->operation_mode }}</span></div>
                <div class="flex justify-between py-2"><span class="text-sm" style="color: var(--color-text-secondary);">Last Update</span><span class="text-sm" id="last-update">{{ $latestLog ? $latestLog->created_at->diffForHumans() : 'No data' }}</span></div>
            </div>
        </div>
    </div>

    @endif
</div>
@endsection

@push('scripts')
@include('dashboard._scripts')
@endpush
