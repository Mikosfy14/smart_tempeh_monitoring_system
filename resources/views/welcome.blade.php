<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>e-Tempeh | Smart Fermentation Monitoring</title>

    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
            color: #0f172a; /* text-slate-900 */
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        /* Custom color for teal matching the dashboard */
        .text-primary { color: #0D9488; } /* Teal-600 */
        .bg-primary { background-color: #0D9488; }
        .hover-bg-primary:hover { background-color: #0F766E; } /* Teal-700 */
        .border-primary { border-color: #0D9488; }
        .text-primary-dark { color: #115E59; } /* Teal-800 */
    </style>
</head>
<body class="antialiased text-gray-900">

    {{-- 1. Navbar (Transparan & Sticky) --}}
    <nav class="flex justify-between items-center py-4 px-6 md:px-12 fixed w-full top-0 z-50 backdrop-blur-md bg-white/80 border-b border-gray-100 transition-all duration-300">
        <div class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
            </svg>
            <span class="text-2xl font-bold text-primary-dark tracking-tight">Rizhomatix</span>
        </div>
    </nav>

    {{-- 2. Hero Section (Gaya Split Layout) --}}
    <header class="min-h-[100dvh] flex items-center pt-24 pb-32 md:pt-32 md:pb-48 px-6 lg:px-20 bg-gradient-to-b from-teal-50/70 to-white relative overflow-hidden">    
        {{-- Decorative background blobs --}}
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-[500px] h-[500px] bg-teal-100/50 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 -ml-20 mb-20 w-[400px] h-[400px] bg-cyan-100/40 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto w-full grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-8 items-center relative z-10">
            {{-- Kolom Kiri --}}
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-100 text-teal-800 text-sm font-semibold mb-6">
                    <span class="flex h-2 w-2 rounded-full bg-teal-500"></span>
                    Sistem Cerdas Generasi 2.0
                </div>
                <h1 class="text-5xl md:text-6xl font-extrabold text-gray-900 leading-[1.15] mb-6 tracking-tight">
                    Revolusi Produksi Tempe dengan <span class="text-primary">Teknologi IoT</span>
                </h1>
                <p class="text-lg md:text-xl text-gray-600 mb-8 leading-relaxed">
                    Tinggalkan cara lama. Pantau suhu, atur kelembapan, dan pastikan kualitas fermentasi tempe Anda selalu konsisten melalui satu sentuhan cerdas.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('login') }}" class="bg-primary hover-bg-primary text-white px-8 py-3.5 rounded-xl font-semibold text-lg transition-all shadow-lg shadow-teal-500/30 text-center flex items-center justify-center gap-2">
                        Buka Dashboard
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                    <a href="#fitur" class="bg-white border-2 border-gray-200 hover:border-teal-500 text-gray-700 hover:text-teal-700 px-8 py-3.5 rounded-xl font-semibold text-lg transition-all text-center">
                        Pelajari Sistem
                    </a>
                </div>
            </div>

            {{-- Kolom Kanan --}}
            <div class="relative mx-auto w-full max-w-lg lg:max-w-full">
                {{-- Abstract Dashboard Illustration --}}
                <div class="animate-float">
                    <svg viewBox="0 0 600 400" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto drop-shadow-2xl rounded-2xl bg-white border border-gray-100">
                        {{-- Topbar --}}
                        <path d="M0 16C0 7.16344 7.16344 0 16 0H584C592.837 0 600 7.16344 600 16V48H0V16Z" fill="#F8FAFC"/>
                        <circle cx="32" cy="24" r="6" fill="#E2E8F0"/>
                        <circle cx="52" cy="24" r="6" fill="#E2E8F0"/>
                        <circle cx="72" cy="24" r="6" fill="#E2E8F0"/>
                        {{-- Sidebar --}}
                        <rect x="0" y="48" width="120" height="352" fill="#F8FAFC"/>
                        <rect x="16" y="72" width="88" height="12" rx="6" fill="#CBD5E1"/>
                        <rect x="16" y="104" width="64" height="12" rx="6" fill="#E2E8F0"/>
                        <rect x="16" y="136" width="76" height="12" rx="6" fill="#E2E8F0"/>
                        {{-- Content Grid --}}
                        <rect x="144" y="72" width="136" height="88" rx="12" fill="#F1F5F9"/>
                        <rect x="296" y="72" width="136" height="88" rx="12" fill="#F1F5F9"/>
                        <rect x="448" y="72" width="136" height="88" rx="12" fill="#F1F5F9"/>
                        {{-- Metric Values inside small cards --}}
                        <rect x="160" y="88" width="24" height="24" rx="12" fill="#0D9488" fill-opacity="0.2"/>
                        <circle cx="172" cy="100" r="6" fill="#0D9488"/>
                        <rect x="160" y="128" width="64" height="16" rx="4" fill="#64748B"/>
                        
                        <rect x="312" y="88" width="24" height="24" rx="12" fill="#3B82F6" fill-opacity="0.2"/>
                        <circle cx="324" cy="100" r="6" fill="#3B82F6"/>
                        <rect x="312" y="128" width="48" height="16" rx="4" fill="#64748B"/>

                        {{-- Big Chart Card --}}
                        <rect x="144" y="184" width="440" height="192" rx="12" fill="#F1F5F9"/>
                        <rect x="168" y="208" width="120" height="16" rx="4" fill="#94A3B8"/>
                        {{-- Chart Line --}}
                        <path d="M168 320 L240 250 L320 280 L420 210 L500 240 L560 170" stroke="#0D9488" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                        {{-- Gradient below chart --}}
                        <path d="M168 320 L240 250 L320 280 L420 210 L500 240 L560 170 L560 344 L168 344 Z" fill="url(#paint0_linear)" opacity="0.1"/>
                        <defs>
                            <linearGradient id="paint0_linear" x1="364" y1="170" x2="364" y2="344" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#0D9488"/>
                                <stop offset="1" stop-color="#0D9488" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
            </div>
        </div>
    </header>

    {{-- 3. Floating Stats Bar --}}
    <div class="relative z-20 px-6">
        <div class="-mt-16 md:-mt-20 max-w-5xl mx-auto bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100 p-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center divide-y md:divide-y-0 md:divide-x divide-gray-100">
                <div class="px-4">
                    <p class="text-4xl md:text-5xl font-extrabold text-primary mb-2">±0.5°C</p>
                    <p class="text-gray-500 font-medium">Akurasi Sensor</p>
                </div>
                <div class="px-4 pt-8 md:pt-0">
                    <p class="text-4xl md:text-5xl font-extrabold text-primary mb-2">99.8%</p>
                    <p class="text-gray-500 font-medium">Server Uptime</p>
                </div>
                <div class="px-4 pt-8 md:pt-0">
                    <p class="text-4xl md:text-5xl font-extrabold text-primary mb-2">2.5s</p>
                    <p class="text-gray-500 font-medium">Respon Alert</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Features Grid (Solusi Masalah) --}}
    <section id="fitur" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4">Mengapa memilih Rizhomatix?</h2>
                <div class="w-24 h-1.5 bg-primary mx-auto rounded-full mb-6"></div>
                <p class="text-gray-600 max-w-2xl mx-auto text-lg">Solusi terintegrasi untuk mengatasi kendala tradisional dan membawa bisnis tempe Anda ke tingkat berikutnya.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                {{-- Card 1 --}}
                <div class="bg-white rounded-2xl shadow-md shadow-gray-200/50 p-8 border border-gray-100 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl hover:shadow-teal-100 group">
                    <div class="w-16 h-16 rounded-2xl bg-teal-50 text-primary flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Bebas Cemas Cuaca</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Fermentasi tradisional bergantung pada cuaca alami. e-Tempeh mengontrol sirkulasi udara cerdas untuk menjaga kestabilan suhu di dalam rak secara presisi.
                    </p>
                </div>

                {{-- Card 2 --}}
                <div class="bg-white rounded-2xl shadow-md shadow-gray-200/50 p-8 border border-gray-100 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl hover:shadow-teal-100 group">
                    <div class="w-16 h-16 rounded-2xl bg-teal-50 text-primary flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Minimalisir Gagal Batch</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Lonjakan panas alami kedelai sering terlambat ditangani hingga membusuk. Sistem kami mendeteksi anomali suhu dan menangani ini sebelum terlambat.
                    </p>
                </div>

                {{-- Card 3 --}}
                <div class="bg-white rounded-2xl shadow-md shadow-gray-200/50 p-8 border border-gray-100 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl hover:shadow-teal-100 group">
                    <div class="w-16 h-16 rounded-2xl bg-teal-50 text-primary flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Pantau Jarak Jauh</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Akses penuh melalui smartphone. Putus ketergantungan pada pengecekan manual berkala, nikmati ketenangan pikiran saat Anda tidak berada di area produksi.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 5. Alternating Info Section --}}
    <section class="py-24 bg-gray-50 overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
            
            {{-- Baris 1: Kiri Teks, Kanan Gambar --}}
            <div class="flex flex-col lg:flex-row items-center gap-16 mb-32">
                <div class="lg:w-1/2">
                    <div class="w-16 h-16 rounded-2xl bg-white shadow-sm flex items-center justify-center mb-6 text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-6 leading-tight">Otomatisasi Kipas & <br>Notifikasi Darurat</h2>
                    <p class="text-lg text-gray-600 leading-relaxed mb-6">
                        Rak fermentasi kami dilengkapi mikrokontroler pintar yang secara langsung mengendalikan kipas pendingin berdasarkan kondisi suhu *real-time*.
                    </p>
                    <p class="text-lg text-gray-600 leading-relaxed mb-8">
                        Jika terjadi anomali ekstrem yang gagal didinginkan, sistem akan segera mengirimkan peringatan darurat melalui WhatsApp, memastikan Anda selalu satu langkah lebih cepat dari kegagalan produksi.
                    </p>
                    <ul class="space-y-4">
                        <li class="flex items-center gap-3 text-gray-700 font-medium">
                            <svg class="w-6 h-6 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Kendali Aktuator Hardware Langsung
                        </li>
                        <li class="flex items-center gap-3 text-gray-700 font-medium">
                            <svg class="w-6 h-6 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            WhatsApp Gateway API Terintegrasi
                        </li>
                    </ul>
                </div>
                <div class="lg:w-1/2 relative">
                    <div class="absolute inset-0 bg-teal-100 transform rotate-3 rounded-3xl"></div>
                    <div class="relative bg-white p-2 rounded-3xl shadow-xl transform transition-transform hover:-rotate-1 duration-500">
                        {{-- Illustration of Actuator & Alert --}}
                        <svg viewBox="0 0 500 400" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto rounded-2xl bg-slate-50">
                            <rect x="50" y="50" width="400" height="300" rx="20" fill="#E2E8F0"/>
                            {{-- Fan Graphic --}}
                            <circle cx="250" cy="180" r="80" fill="#CBD5E1"/>
                            <path d="M250 180 Q290 120 310 160 Q270 200 250 180Z" fill="#94A3B8"/>
                            <path d="M250 180 Q210 240 190 200 Q230 160 250 180Z" fill="#94A3B8"/>
                            <path d="M250 180 Q190 140 210 120 Q250 160 250 180Z" fill="#94A3B8"/>
                            <path d="M250 180 Q310 220 290 240 Q250 200 250 180Z" fill="#94A3B8"/>
                            {{-- Alert Box --}}
                            <rect x="150" y="280" width="200" height="50" rx="12" fill="#10B981" shadow="lg"/>
                            <circle cx="175" cy="305" r="10" fill="#FFFFFF"/>
                            <rect x="195" y="300" width="120" height="10" rx="5" fill="#FFFFFF"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Baris 2: Kiri Gambar, Kanan Teks --}}
            <div class="flex flex-col lg:flex-row-reverse items-center gap-16">
                <div class="lg:w-1/2">
                    <div class="w-16 h-16 rounded-2xl bg-white shadow-sm flex items-center justify-center mb-6 text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-6 leading-tight">Manajemen Batch & <br>Ekspor Data Real-Time</h2>
                    <p class="text-lg text-gray-600 leading-relaxed mb-6">
                        Lacak setiap produksi sebagai satu kesatuan *Batch*. Pantau awal, akhir, dan seluruh rekam jejak suhu selama periode fermentasi tersebut.
                    </p>
                    <p class="text-lg text-gray-600 leading-relaxed mb-8">
                        Dengan fitur ekspor data lanjutan, Anda dapat mencetak laporan produksi dalam bentuk PDF sebagai bukti kualitas operasional dan jaminan mutu (Quality Assurance).
                    </p>
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 text-primary font-bold text-lg hover:text-teal-700 transition-colors group">
                        Daftar Gratis Sekarang
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>
                <div class="lg:w-1/2 relative">
                    <div class="absolute inset-0 bg-blue-100 transform -rotate-3 rounded-3xl"></div>
                    <div class="relative bg-white p-2 rounded-3xl shadow-xl transform transition-transform hover:rotate-1 duration-500">
                        {{-- Graphic of web log/data --}}
                        <svg viewBox="0 0 500 400" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto rounded-2xl bg-white border border-gray-100">
                            {{-- Table Header --}}
                            <rect x="20" y="40" width="460" height="40" rx="8" fill="#F8FAFC"/>
                            <rect x="40" y="55" width="80" height="10" rx="5" fill="#CBD5E1"/>
                            <rect x="150" y="55" width="120" height="10" rx="5" fill="#CBD5E1"/>
                            <rect x="350" y="55" width="100" height="10" rx="5" fill="#CBD5E1"/>
                            
                            {{-- Rows --}}
                            <rect x="20" y="100" width="460" height="50" rx="8" fill="#F1F5F9"/>
                            <rect x="40" y="120" width="60" height="10" rx="5" fill="#94A3B8"/>
                            <rect x="150" y="120" width="140" height="10" rx="5" fill="#94A3B8"/>
                            <rect x="350" y="115" width="80" height="20" rx="10" fill="#0D9488" fill-opacity="0.2"/>

                            <rect x="20" y="170" width="460" height="50" rx="8" fill="#F1F5F9"/>
                            <rect x="40" y="190" width="60" height="10" rx="5" fill="#94A3B8"/>
                            <rect x="150" y="190" width="100" height="10" rx="5" fill="#94A3B8"/>
                            <rect x="350" y="185" width="80" height="20" rx="10" fill="#0D9488" fill-opacity="0.2"/>

                            <rect x="20" y="240" width="460" height="50" rx="8" fill="#F1F5F9"/>
                            <rect x="40" y="260" width="60" height="10" rx="5" fill="#94A3B8"/>
                            <rect x="150" y="260" width="160" height="10" rx="5" fill="#94A3B8"/>
                            <rect x="350" y="255" width="80" height="20" rx="10" fill="#0D9488" fill-opacity="0.2"/>

                            {{-- PDF Document Icon floating --}}
                            <rect x="360" y="280" width="80" height="100" rx="8" fill="#EF4444" shadow="lg" class="animate-float"/>
                            <rect x="370" y="300" width="40" height="8" rx="4" fill="#FFFFFF"/>
                            <rect x="370" y="320" width="60" height="8" rx="4" fill="#FFFFFF"/>
                            <rect x="370" y="340" width="50" height="8" rx="4" fill="#FFFFFF"/>
                        </svg>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- 6. Clean Footer --}}
    <footer class="bg-white border-t border-gray-100 pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <div class="md:col-span-2">
                    <div class="flex items-center gap-2 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                        </svg>
                        <span class="text-xl font-bold text-primary-dark">Rizhomatix</span>
                    </div>
                    <p class="text-gray-500 leading-relaxed max-w-sm">
                        Menghadirkan teknologi Internet of Things (IoT) untuk mengotomatisasi dan memonitor kualitas produksi tempe lokal secara profesional.
                    </p>
                </div>
                <div>
                    <h4 class="font-bold text-gray-900 mb-4">Navigasi</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-500 hover:text-primary transition-colors">Beranda</a></li>
                        <li><a href="#fitur" class="text-gray-500 hover:text-primary transition-colors">Fitur Sistem</a></li>
                        <li><a href="{{ route('login') }}" class="text-gray-500 hover:text-primary transition-colors">Masuk Dashboard</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-gray-900 mb-4">Kontak Kami</h4>
                    <ul class="space-y-3 text-gray-500">
                        <li>Rizhomatix support</li>
                        <li>support@rizhomatix.com</li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-100 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-gray-400 text-sm">
                    &copy; {{ date('Y') }} e-Tempeh Project. Hak Cipta Dilindungi.
                </p>
                <div class="flex gap-4">
                    <span class="text-sm font-medium text-gray-400 bg-gray-50 px-3 py-1 rounded-full border border-gray-100">
                        Smart Fermentation Monitoring V2.0
                    </span>
                </div>
            </div>
        </div>
    </footer>

    {{-- Initialize Icons --}}
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
