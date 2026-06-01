@extends('layouts.guest')

@section('title', 'Daftar Akun — Smart Tempeh Monitoring')

@section('content')
<div class="flex items-center justify-center min-h-screen">
    <div class="card-static w-full max-w-md animate-fade-in-up">
        {{-- Logo & Title --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4" style="background: rgba(96, 165, 250, 0.1);">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" style="color: var(--color-accent-blue);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold">Buat Akun Baru</h1>
            <p class="mt-1 text-sm" style="color: var(--color-text-secondary);">Daftar untuk mulai memantau fermentasi tempe</p>
        </div>

        {{-- Error Messages --}}
        @if($errors->any())
            <div class="alert alert-error animate-shake mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Registration Form --}}
        <form method="POST" action="{{ route('register') }}" id="register-form">
            @csrf

            {{-- Name --}}
            <div class="mb-4">
                <label for="name" class="form-label">Nama Lengkap</label>
                <div class="form-input-icon-wrapper">
                    <svg class="form-input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-input" placeholder="Nama lengkap Anda" required autofocus>
                </div>
                @error('name') <span class="text-xs" style="color: var(--color-accent-red);">{{ $message }}</span> @enderror
            </div>

            {{-- Email --}}
            <div class="mb-4">
                <label for="email" class="form-label">Email</label>
                <div class="form-input-icon-wrapper">
                    <svg class="form-input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-input" placeholder="name@example.com" required>
                </div>
                @error('email') <span class="text-xs" style="color: var(--color-accent-red);">{{ $message }}</span> @enderror
            </div>

            {{-- Password --}}
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="form-input-icon-wrapper">
                    <svg class="form-input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <input type="password" id="password" name="password" class="form-input form-input-has-toggle" placeholder="Minimal 6 karakter" required>
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password', this)" tabindex="-1" aria-label="Tampilkan password">
                        <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg class="eye-closed hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.024 10.024 0 014.13-5.246M8.82 8.82L4.7 4.7M9.88 9.88a3 3 0 104.24 4.24m2.07-2.07a9.962 9.962 0 012.33 3.95M21 12a9.964 9.964 0 00-3.21-6.88M3 3l18 18" />
                        </svg>
                    </button>
                </div>
                @error('password') <span class="text-xs" style="color: var(--color-accent-red);">{{ $message }}</span> @enderror
            </div>

            {{-- Confirm Password --}}
            <div class="mb-4">
                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                <div class="form-input-icon-wrapper">
                    <svg class="form-input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-input form-input-has-toggle" placeholder="Ketik ulang password" required>
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password_confirmation', this)" tabindex="-1" aria-label="Tampilkan password">
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

            {{-- WhatsApp Number --}}
            <div class="mb-6">
                <label for="whatsapp_number" class="form-label">Nomor WhatsApp</label>
                <div class="form-input-icon-wrapper">
                    <svg class="form-input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    <input type="text" id="whatsapp_number" name="whatsapp_number" value="{{ old('whatsapp_number') }}" class="form-input" placeholder="628xxxxxxxxxx" required>
                </div>
                <p class="text-xs mt-1" style="color: var(--color-text-muted);">Format: 628xxxxxxxxxx (tanpa tanda +)</p>
                @error('whatsapp_number') <span class="text-xs" style="color: var(--color-accent-red);">{{ $message }}</span> @enderror
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn btn-primary w-full" id="btn-register">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Daftar Sekarang
            </button>
        </form>

        {{-- Divider --}}
        <div class="flex items-center gap-3 my-6">
            <div class="flex-1" style="height: 1px; background: var(--color-border-card);"></div>
            <span class="text-xs font-medium" style="color: var(--color-text-muted);">atau</span>
            <div class="flex-1" style="height: 1px; background: var(--color-border-card);"></div>
        </div>

        {{-- Google Register Button --}}
        <a href="{{ route('auth.google') }}" class="btn w-full flex items-center justify-center gap-3" id="btn-google-register"
           style="background: rgba(255,255,255,0.05); border: 1px solid var(--color-border-card); color: var(--color-text-primary); padding: 0.75rem 1rem; border-radius: 0.75rem; font-weight: 500; transition: all 0.2s ease; text-decoration: none;"
           onmouseover="this.style.background='rgba(255,255,255,0.1)'; this.style.borderColor='rgba(255,255,255,0.2)'; this.style.transform='translateY(-1px)'"
           onmouseout="this.style.background='rgba(255,255,255,0.05)'; this.style.borderColor='var(--color-border-card)'; this.style.transform='translateY(0)'">
            <svg width="20" height="20" viewBox="0 0 48 48">
                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
            </svg>
            Buat Akun dengan Google
        </a>

        {{-- Login Link --}}
        <div class="text-center mt-6 pt-6" style="border-top: 1px solid var(--color-border-card);">
            <p class="text-sm" style="color: var(--color-text-muted);">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="font-medium hover:underline" style="color: var(--color-accent-teal);" id="link-login">Masuk di sini</a>
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
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
