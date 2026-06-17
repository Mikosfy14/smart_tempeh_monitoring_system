@extends('layouts.guest')

@section('title', 'Admin Login — Rizhomatix')

@section('content')
<div class="flex items-center justify-center min-h-screen">
    <div class="card-static w-full max-w-md animate-fade-in-up">
        {{-- Logo & Title --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4" style="background: rgba(251, 191, 36, 0.1);">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" style="color: var(--color-accent-amber);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold">Admin Panel</h1>
            <p class="mt-1 text-sm" style="color: var(--color-text-secondary);">Rizhomatix — Administrator</p>
        </div>

        {{-- Error Messages --}}
        @if($errors->any())
            <div class="alert alert-error animate-shake mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Login Form --}}
        <form method="POST" action="{{ route('admin.login') }}" id="admin-login-form">
            @csrf

            {{-- Username --}}
            <div class="mb-4">
                <label for="username" class="form-label">Username</label>
                <div class="form-input-icon-wrapper">
                    <svg class="form-input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <input type="text" id="username" name="username" value="{{ old('username') }}" class="form-input" placeholder="admin" required autofocus>
                </div>
            </div>

            {{-- Password --}}
            <div class="mb-6">
                <label for="password" class="form-label">Password</label>
                <div class="form-input-icon-wrapper">
                    <svg class="form-input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn w-full font-semibold" style="background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #0a0e1a;" id="btn-admin-login">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Access Admin Panel
            </button>
        </form>

        {{-- Back to User Login --}}
        <div class="text-center mt-6 pt-6" style="border-top: 1px solid var(--color-border-card);">
            <a href="{{ route('login') }}" class="text-sm font-medium hover:underline" style="color: var(--color-text-muted);" id="link-user-login">
                ← Back to User Login
            </a>
        </div>
    </div>
</div>
@endsection
