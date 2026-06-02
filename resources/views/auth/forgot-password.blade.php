@extends('layouts.guest')

@section('title', 'Lupa Password — Smart Tempeh Monitoring')

@section('content')
<div class="flex items-center justify-center min-h-screen">
    <div class="card-static w-full max-w-md animate-fade-in-up">
        {{-- Logo & Title --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4" style="background: rgba(45, 212, 191, 0.1);">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" style="color: var(--color-accent-teal);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold">Lupa Password</h1>
            <p class="mt-1 text-sm" style="color: var(--color-text-secondary);">Masukkan email Anda dan kami akan mengirimkan tautan untuk mengatur ulang password.</p>
        </div>

        {{-- Session Status --}}
        @if (session('status'))
            <div class="alert alert-success mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('status') }}
            </div>
        @endif

        {{-- Error Messages --}}
        @if($errors->any())
            <div class="alert alert-error animate-shake mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            {{-- Email --}}
            <div class="mb-6">
                <label for="email" class="form-label">Alamat Email</label>
                <div class="form-input-icon-wrapper">
                    <svg class="form-input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-input" placeholder="name@example.com" required autofocus>
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn btn-primary w-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Kirim Link Reset
            </button>
        </form>

        {{-- Back Link --}}
        <div class="text-center mt-6 pt-6" style="border-top: 1px solid var(--color-border-card);">
            <a href="{{ route('login') }}" class="font-medium hover:underline text-sm flex items-center justify-center gap-1" style="color: var(--color-text-muted);">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Login
            </a>
        </div>
    </div>
</div>
@endsection
