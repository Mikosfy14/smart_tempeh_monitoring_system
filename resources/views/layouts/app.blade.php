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

        {{-- Center: Add Device Button --}}
        <div class="flex items-center gap-3">
            <button class="btn btn-primary btn-sm" onclick="openModal('register-modal')" id="btn-add-device">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                Tambah Alat
            </button>
        </div>

        {{-- Right: User + Profile + Logout --}}
        <div class="flex items-center gap-4">
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 text-sm font-medium" style="color: var(--color-text-secondary); text-decoration: none;" id="btn-profile-link">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                {{ Auth::user()->name }}
            </a>
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

    {{-- REGISTER DEVICE MODAL --}}
    <div class="modal-overlay" id="register-modal">
        <div class="modal-content">
            <div class="flex items-center justify-between mb-6">
                <h3 class="modal-title mb-0">Daftarkan Rak Baru</h3>
                <button class="btn-icon" onclick="closeModal('register-modal')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <p class="text-sm mb-4" style="color: var(--color-text-secondary);">Masukkan Device ID yang tercetak pada perangkat ESP32 Anda.</p>
            <div id="register-errors" class="alert alert-error mb-4" style="display: none;"></div>
            <form id="register-device-form" onsubmit="registerDevice(event)">
                <div class="mb-4">
                    <label class="form-label">Device ID</label>
                    <input type="text" name="device_id" class="form-input" placeholder="Contoh: TEMPE-001" required id="reg-device-id" style="text-transform: uppercase;">
                </div>
                <div class="mb-6">
                    <label class="form-label">Label Rak <span style="color: var(--color-text-muted);">(opsional)</span></label>
                    <input type="text" name="label_rak" class="form-input" placeholder="Contoh: Rak Tempe Lantai 2" id="reg-label-rak">
                </div>
                <button type="submit" class="btn btn-primary w-full" id="btn-submit-register">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                    Daftarkan Alat
                </button>
            </form>
        </div>
    </div>

    @vite(['resources/js/app.js'])

    <script>
        window.csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        function openModal(id) { document.getElementById(id).classList.add('active'); }
        function closeModal(id) { document.getElementById(id).classList.remove('active'); }

        async function registerDevice(e) {
            e.preventDefault();
            const errEl = document.getElementById('register-errors');
            errEl.style.display = 'none';
            const btn = document.getElementById('btn-submit-register');
            btn.disabled = true;
            btn.textContent = 'Mendaftarkan...';

            const body = {
                device_id: document.getElementById('reg-device-id').value.toUpperCase(),
                label_rak: document.getElementById('reg-label-rak').value || null,
            };

            try {
                const res = await fetch('{{ route("device.register") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify(body)
                });
                const data = await res.json();

                if (!data.success) {
                    errEl.textContent = data.message;
                    errEl.style.display = 'flex';
                    btn.disabled = false;
                    btn.textContent = 'Daftarkan Alat';
                    return;
                }

                closeModal('register-modal');
                document.getElementById('register-device-form').reset();
                window.location.reload();
            } catch (err) {
                btn.disabled = false;
                btn.textContent = 'Daftarkan Alat';
                window.location.reload();
            }
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
