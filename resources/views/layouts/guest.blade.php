<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Smart Tempeh Monitoring')</title>
    <meta name="description" content="@yield('description', 'IoT-based Smart Tempeh Fermentation Monitoring System')">

    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    {{-- Flash Messages --}}
    @if(session('error'))
        <div id="flash-error" class="alert alert-error fixed top-4 left-1/2 -translate-x-1/2 z-50 animate-fade-in-up" style="min-width: 300px;">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div id="flash-success" class="alert alert-success fixed top-4 left-1/2 -translate-x-1/2 z-50 animate-fade-in-up" style="min-width: 300px;">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <main class="w-full">
        @yield('content')
    </main>

    @vite(['resources/js/app.js'])
    @stack('scripts')

    <script>
        // Auto-hide flash messages after 4 seconds
        setTimeout(() => {
            document.querySelectorAll('#flash-error, #flash-success').forEach(el => {
                el.style.transition = 'opacity 0.3s ease';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 300);
            });
        }, 4000);
    </script>
</body>
</html>
