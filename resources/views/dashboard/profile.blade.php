@extends('layouts.app')

@section('title', 'Edit Profile — Smart Tempeh Monitoring')

@section('content')
<div class="stagger-children" style="max-width: 720px; margin: 0 auto;">

    {{-- Back Button --}}
    <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm mb-6" id="btn-back-dashboard">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        Kembali ke Dashboard
    </a>

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
                <input type="email" name="email" class="form-input" value="{{ old('email', $user->email) }}" required id="profile-email">
                @error('email') <span class="text-xs" style="color: var(--color-accent-red);">{{ $message }}</span> @enderror
            </div>

            <div class="mb-6">
                <label class="form-label">Nomor WhatsApp</label>
                <input type="text" name="whatsapp_number" class="form-input" value="{{ old('whatsapp_number', $user->whatsapp_number) }}" placeholder="628xxxxxxxxxx" id="profile-whatsapp">
                <p class="text-xs mt-1" style="color: var(--color-text-muted);">Format: 628xxxxxxxxxx (tanpa tanda +)</p>
                @error('whatsapp_number') <span class="text-xs" style="color: var(--color-accent-red);">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="btn btn-primary" id="btn-save-profile">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                Simpan Perubahan
            </button>
        </form>
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
                <input type="password" name="old_password" class="form-input" required placeholder="Masukkan password saat ini" id="old-password">
                @error('old_password') <span class="text-xs" style="color: var(--color-accent-red);">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="form-label">Password Baru</label>
                <input type="password" name="new_password" class="form-input" required placeholder="Minimal 6 karakter" id="new-password">
                @error('new_password') <span class="text-xs" style="color: var(--color-accent-red);">{{ $message }}</span> @enderror
            </div>

            <div class="mb-6">
                <label class="form-label">Konfirmasi Password Baru</label>
                <input type="password" name="new_password_confirmation" class="form-input" required placeholder="Ketik ulang password baru" id="new-password-confirm">
            </div>

            <button type="submit" class="btn btn-danger" id="btn-change-password">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                Ubah Password
            </button>
        </form>
    </div>

</div>
@endsection
