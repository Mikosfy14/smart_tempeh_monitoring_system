@extends('layouts.guest')

@section('title', 'Login — Smart Tempeh Monitoring')

@section('content')
<div class="flex items-center justify-center min-h-screen">
    <div class="card-static w-full max-w-md animate-fade-in-up">
        {{-- Logo & Title --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4" style="background: rgba(45, 212, 191, 0.1);">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" style="color: var(--color-accent-teal);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold">Smart Tempeh Monitoring</h1>
            <p class="mt-1 text-sm" style="color: var(--color-text-secondary);">Sign in to your monitoring dashboard</p>
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
        <form method="POST" action="{{ route('login') }}" id="login-form">
            @csrf

            {{-- Email --}}
            <div class="mb-4">
                <label for="email" class="form-label">Email Address</label>
                <div class="form-input-icon-wrapper">
                    <svg class="form-input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-input" placeholder="name@example.com" required autofocus>
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
            <button type="submit" class="btn btn-primary w-full" id="btn-login">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Sign In
            </button>
        </form>

        {{-- Admin Link --}}
        <div class="text-center mt-6 pt-6" style="border-top: 1px solid var(--color-border-card);">
            <a href="{{ route('admin.login') }}" class="text-sm font-medium hover:underline" style="color: var(--color-text-muted);" id="link-admin-login">
                Admin Access →
            </a>
        </div>
    </div>
</div>
@endsection
