@extends('layouts.app')

@section('title', 'Edit Profile — Smart Tempeh Monitoring')

@section('content')
<div class="stagger-children" style="max-width: 720px; margin: 0 auto;">

    {{-- Header & Back Button --}}
    <div class="flex flex-col md:flex-row md:items-center justify-center mb-8 relative gap-4">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm md:absolute md:left-0 self-start" id="btn-back-dashboard">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali
        </a>
        <div class="text-left md:text-center w-full">
            <h1 class="text-2xl font-bold">Profil Pengguna</h1>
            <p class="text-sm mt-1" style="color: var(--color-text-muted);">Kelola informasi akun Anda</p>
        </div>
    </div>

    {{-- =============================== --}}
    {{-- PROFILE INFORMATION --}}
    {{-- =============================== --}}
    <div class="card-static mb-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="flex items-center justify-center w-12 h-12 rounded-xl" style="background: rgba(96, 165, 250, 0.1);">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" style="color: var(--color-accent-blue);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold">Informasi Profil</h2>
                <p class="text-xs" style="color: var(--color-text-muted);">Perbarui nama, email, dan nomor WhatsApp Anda</p>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" id="profile-form">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $user->name) }}" required id="profile-name">
                @error('name') <span class="text-xs" style="color: var(--color-accent-red);">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="form-label">Email</label>
                @if($user->google_id)
                <input type="email" name="email" class="form-input opacity-60 cursor-not-allowed" style="background-color: rgba(0,0,0,0.15);" value="{{ old('email', $user->email) }}" required id="profile-email" readonly>
                <p class="text-xs mt-1" style="color: var(--color-text-muted);">✓ Email terkoneksi ke Google (Otentikasi Utama)</p>
                @else
                <input type="email" name="email" class="form-input" value="{{ old('email', $user->email) }}" required id="profile-email">
                @endif
                @error('email') <span class="text-xs" style="color: var(--color-accent-red);">{{ $message }}</span> @enderror
            </div>

            <div class="mb-6">
                <label class="form-label">Nomor WhatsApp</label>
                <input type="text" name="whatsapp_number" class="form-input" value="{{ old('whatsapp_number', $user->whatsapp_number) }}" placeholder="628xxxxxxxxxx" id="profile-whatsapp">
                <p class="text-xs mt-1" style="color: var(--color-text-muted);">Format: 628xxxxxxxxxx (tanpa tanda +)</p>
                @error('whatsapp_number') <span class="text-xs" style="color: var(--color-accent-red);">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="btn btn-primary" id="btn-save-profile">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                Simpan Perubahan
            </button>
        </form>
    </div>

    {{-- =============================== --}}
    {{-- SOCIAL ACCOUNT CONNECTION --}}
    {{-- =============================== --}}
    <div class="card-static mb-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="flex items-center justify-center w-12 h-12 rounded-xl" style="background: rgba(66, 133, 244, 0.1);">
                <svg width="24" height="24" viewBox="0 0 48 48">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z" />
                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z" />
                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z" />
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z" />
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold">Koneksi Akun Sosial</h2>
                <p class="text-xs" style="color: var(--color-text-muted);">Tautkan akun Google Anda untuk login yang lebih mudah</p>
            </div>
        </div>

        @if(session('google'))
        <div class="alert alert-error mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('google') }}
        </div>
        @endif

        @error('google')
        <div class="alert alert-error mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ $message }}
        </div>
        @enderror

        @if($user->google_id)
        <div class="p-4 rounded-xl" style="background: var(--color-bg-card); border: 1px solid var(--color-border-card);">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center bg-white shrink-0">
                        <svg width="20" height="20" viewBox="0 0 48 48">
                            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z" />
                            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z" />
                            <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z" />
                            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-medium text-sm">Google Account</h3>
                        <p class="text-xs" style="color: var(--color-text-muted);">Terhubung</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg shrink-0" style="background: rgba(52, 211, 153, 0.1); border: 1px solid rgba(52, 211, 153, 0.2);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" style="color: var(--color-accent-teal);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="text-xs font-semibold whitespace-nowrap" style="color: var(--color-accent-teal);">Linked</span>
                </div>
            </div>
            
            <form id="form-unlink-google" action="{{ route('profile.unlink-google') }}" method="POST" style="display: none;">
                @csrf
                <input type="hidden" name="password" id="hidden-password-input">
            </form>
            <div class="border-t pt-4 mt-4 flex justify-end" style="border-color: var(--color-border-card);">
                <button type="button" class="btn btn-danger shrink-0" onclick="confirmUnlinkGoogle()">
                    Putus Koneksi
                </button>
            </div>
        </div>
        @else
        <div class="flex items-center justify-between p-4 rounded-xl" style="background: var(--color-bg-card); border: 1px solid var(--color-border-card);">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-white shrink-0">
                    <svg width="20" height="20" viewBox="0 0 48 48">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z" />
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z" />
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z" />
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-medium text-sm">Google Account</h3>
                    <p class="text-xs" style="color: var(--color-text-muted);">Belum terhubung</p>
                </div>
            </div>
            <a href="{{ route('profile.link-google') }}" class="btn btn-sm shrink-0" style="background: var(--color-accent-blue); color: white;">
                Tautkan Akun Google
            </a>
        </div>
        @endif
    </div>

    {{-- =============================== --}}
    {{-- CHANGE PASSWORD --}}
    {{-- =============================== --}}
    <div class="card-static">
        <div class="flex items-center gap-3 mb-6">
            <div class="flex items-center justify-center w-12 h-12 rounded-xl" style="background: rgba(248, 113, 113, 0.1);">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" style="color: var(--color-accent-red);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold">Ganti Password</h2>
                <p class="text-xs" style="color: var(--color-text-muted);">Masukkan password lama untuk verifikasi</p>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.password') }}" id="password-form">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="form-label">Password Lama</label>
                <div class="relative">
                    <input type="password" name="old_password" class="form-input form-input-has-toggle" required placeholder="Masukkan password saat ini" id="old-password">
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('old-password', this)" tabindex="-1" aria-label="Tampilkan password">
                        <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg class="eye-closed hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.024 10.024 0 014.13-5.246M8.82 8.82L4.7 4.7M9.88 9.88a3 3 0 104.24 4.24m2.07-2.07a9.962 9.962 0 012.33 3.95M21 12a9.964 9.964 0 00-3.21-6.88M3 3l18 18" />
                        </svg>
                    </button>
                </div>
                @error('old_password') <span class="text-xs" style="color: var(--color-accent-red);">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="form-label">Password Baru</label>
                <div class="relative">
                    <input type="password" name="new_password" class="form-input form-input-has-toggle" required placeholder="Minimal 6 karakter" id="new-password">
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('new-password', this)" tabindex="-1" aria-label="Tampilkan password">
                        <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg class="eye-closed hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.024 10.024 0 014.13-5.246M8.82 8.82L4.7 4.7M9.88 9.88a3 3 0 104.24 4.24m2.07-2.07a9.962 9.962 0 012.33 3.95M21 12a9.964 9.964 0 00-3.21-6.88M3 3l18 18" />
                        </svg>
                    </button>
                </div>
                @error('new_password') <span class="text-xs" style="color: var(--color-accent-red);">{{ $message }}</span> @enderror
            </div>

            <div class="mb-6">
                <label class="form-label">Konfirmasi Password Baru</label>
                <div class="relative">
                    <input type="password" name="new_password_confirmation" class="form-input form-input-has-toggle" required placeholder="Ketik ulang password baru" id="new-password-confirm">
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('new-password-confirm', this)" tabindex="-1" aria-label="Tampilkan password">
                        <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg class="eye-closed hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.024 10.024 0 014.13-5.246M8.82 8.82L4.7 4.7M9.88 9.88a3 3 0 104.24 4.24m2.07-2.07a9.962 9.962 0 012.33 3.95M21 12a9.964 9.964 0 00-3.21-6.88M3 3l18 18" />
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-danger" id="btn-change-password">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                Ubah Password
            </button>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function confirmUnlinkGoogle() {
        if (typeof Swal === 'undefined') {
            const pwd = prompt('Masukkan password manual Anda untuk memutus koneksi ini:');
            if (pwd) {
                document.getElementById('hidden-password-input').value = pwd;
                document.getElementById('form-unlink-google').submit();
            }
            return;
        }

        Swal.fire({
            title: 'Putus Koneksi Google',
            text: 'Yakin ingin memutus koneksi akun Google? Anda harus menggunakan password untuk login ke depannya.',
            input: 'password',
            inputPlaceholder: 'Password manual',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Putus Koneksi',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#0D9488',
            cancelButtonColor: '#334155',
            background: 'var(--color-bg-card, #1e293b)',
            color: 'var(--color-text-primary, #f1f5f9)',
            inputValidator: (value) => {
                if (!value) {
                    return 'Password wajib diisi!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('hidden-password-input').value = result.value;
                document.getElementById('form-unlink-google').submit();
            }
        });
    }

    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        
        const eyeOpen = btn.querySelector('.eye-open');
        const eyeClosed = btn.querySelector('.eye-closed');
        
        if (input.type === 'password') {
            input.type = 'text';
            eyeOpen.classList.add('hidden');
            eyeClosed.classList.remove('hidden');
        } else {
            input.type = 'password';
            eyeOpen.classList.remove('hidden');
            eyeClosed.classList.add('hidden');
        }
    }
</script>
@endpush