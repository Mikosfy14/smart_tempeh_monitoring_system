<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard — Smart Tempeh Monitoring')</title>
    <meta name="description" content="@yield('description', 'Real-time tempeh fermentation monitoring dashboard')">

    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>

    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen">

    {{-- Top Navigation Bar --}}
    <nav class="nav-bar">
        {{-- Logo --}}
        <div class="nav-logo">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
            </svg>
            <span>Smart Tempeh Monitoring</span>
        </div>

        {{-- Center: Device Selector --}}
        <div class="flex items-center gap-3">
            @if(isset($devices) && $devices->count() > 0)
                <label for="device-selector" class="text-sm" style="color: var(--color-text-secondary);">Device:</label>
                <select id="device-selector" class="device-select" onchange="switchDevice(this.value)">
                    @foreach($devices as $device)
                        <option value="{{ $device->id }}" {{ (isset($activeDevice) && $activeDevice->id == $device->id) ? 'selected' : '' }}>
                            {{ $device->device_name }}
                        </option>
                    @endforeach
                </select>
                <span class="status-dot {{ isset($activeDevice) ? 'status-dot--online' : 'status-dot--offline' }}"></span>
            @else
                <span class="text-sm" style="color: var(--color-text-muted);">No devices linked</span>
            @endif
        </div>

        {{-- Right: User + Logout --}}
        <div class="flex items-center gap-4">
            <span class="text-sm font-medium" style="color: var(--color-text-secondary);">
                {{ Auth::user()->name }}
            </span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm" id="btn-logout">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </nav>

    {{-- Flash Messages --}}
    @if(session('error'))
        <div id="flash-error" class="alert alert-error mx-6 mt-4 animate-fade-in-up">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div id="flash-success" class="alert alert-success mx-6 mt-4 animate-fade-in-up">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Main Content --}}
    <main class="p-6">
        @yield('content')
    </main>

    @vite(['resources/js/app.js'])

    <script>
        // CSRF token for AJAX
        window.csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Device switching
        function switchDevice(deviceId) {
            window.location.href = '/dashboard?device_id=' + deviceId;
        }

        // Auto-hide flash messages
        setTimeout(() => {
            document.querySelectorAll('#flash-error, #flash-success').forEach(el => {
                el.style.transition = 'opacity 0.3s ease';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 300);
            });
        }, 4000);
    </script>

    @stack('scripts')
</body>
</html>
