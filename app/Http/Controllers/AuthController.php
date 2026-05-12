<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    // =============================================
    // LOGIN
    // =============================================

    /**
     * Show the login form.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Check if user exists first
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'Akun tidak ditemukan!',
            ])->onlyInput('email');
        }

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password salah!',
        ])->onlyInput('email');
    }

    // =============================================
    // REGISTRATION (Standard)
    // =============================================

    /**
     * Show the registration form.
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle standard registration.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'        => ['required', 'string', 'min:6', 'confirmed'],
            'whatsapp_number' => ['required', 'string', 'max:20'],
        ], [
            'name.required'            => 'Nama lengkap wajib diisi.',
            'email.required'           => 'Email wajib diisi.',
            'email.email'              => 'Format email tidak valid.',
            'email.unique'             => 'Email sudah terdaftar.',
            'password.required'        => 'Password wajib diisi.',
            'password.min'             => 'Password minimal 6 karakter.',
            'password.confirmed'       => 'Konfirmasi password tidak cocok.',
            'whatsapp_number.required' => 'Nomor WhatsApp wajib diisi.',
        ]);

        $user = User::create([
            'name'            => $validated['name'],
            'email'           => $validated['email'],
            'password'        => Hash::make($validated['password']),
            'whatsapp_number' => $validated['whatsapp_number'],
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    // =============================================
    // GOOGLE OAUTH
    // =============================================

    /**
     * Redirect the user to Google's OAuth page.
     */
    /**
     * Redirect the user to Google's OAuth page.
     */
    public function redirectToGoogle()
    {
        // Jika user sudah login, otomatis tandai ini sebagai proses TAUTAN (Linking)
        if (Auth::check()) {
            session(['linking_google' => true]);
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the callback from Google.
     *
     * - If logged in (Linking): Validate email uniqueness, update google_id.
     * - Existing user → login immediately.
     * - New user → store OAuth data in session, redirect to complete-profile form.
     */
    /**
     * Handle the callback from Google.
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            $errorMsg = 'Gagal terhubung dengan Google. Silakan coba lagi.';
            return Auth::check()
                ? redirect()->route('profile.edit')->withErrors(['google' => $errorMsg])
                : redirect()->route('login')->withErrors(['email' => $errorMsg]);
        }

        // ==========================================
        // 1. LINKING ACCOUNT (User is already logged in)
        // ==========================================
        if (Auth::check() && $request->session()->pull('linking_google')) {
            $currentUser = Auth::user();

            $googleEmail = $googleUser->getEmail();
            $googleId = $googleUser->getId();

            // Cek apakah email Google sudah dipakai akun lain
            $existingUserWithEmail = User::where('email', $googleEmail)
                ->where('id', '!=', $currentUser->id)
                ->first();

            // Cek apakah Google ID sudah dipakai akun lain
            $existingUserWithGoogleId = User::where('google_id', $googleId)
                ->where('id', '!=', $currentUser->id)
                ->first();

            if ($existingUserWithEmail || $existingUserWithGoogleId) {
                return redirect()->route('profile.edit')->withErrors([
                    'google' => 'Gagal menautkan! Akun Google atau Email tersebut sudah terhubung dengan pengguna lain di sistem.'
                ]);
            }

            // Sinkronisasi otomatis: Perbarui google_id DAN sinkronkan email ke email Google
            User::where('id', $currentUser->id)->update([
                'email'     => $googleEmail,
                'google_id' => $googleId,
            ]);

            return redirect()->route('profile.edit')->with('success', 'Akun Google berhasil ditautkan dan Email telah disinkronisasi!');
        }

        // ==========================================
        // 2. NORMAL LOGIN / REGISTRATION
        // ==========================================

        // Cari user berdasarkan google_id atau email
        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            // Update google_id jika user lama baru pertama kali login via Google
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }

            Auth::login($user, true);
            return redirect()->intended(route('dashboard'));
        }

        // User Baru — simpan data ke session dan arahkan ke form lengkapi profil
        session([
            'oauth_data' => [
                'name'      => $googleUser->getName(),
                'email'     => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
            ],
        ]);

        return redirect()->route('register.complete');
    }

    // =============================================
    // COMPLETE PROFILE (Google OAuth new users)
    // =============================================

    /**
     * Show the complete-profile form for new Google OAuth users.
     */
    public function showCompleteProfile()
    {
        $oauthData = session('oauth_data');

        if (!$oauthData) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sesi OAuth telah kedaluwarsa. Silakan coba lagi.',
            ]);
        }

        return view('auth.complete-profile', compact('oauthData'));
    }

    /**
     * Handle the complete-profile form submission.
     */
    public function completeProfile(Request $request)
    {
        $oauthData = session('oauth_data');

        if (!$oauthData) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sesi OAuth telah kedaluwarsa. Silakan coba lagi.',
            ]);
        }

        $validated = $request->validate([
            'password'        => ['required', 'string', 'min:6', 'confirmed'],
            'whatsapp_number' => ['required', 'string', 'max:20'],
        ], [
            'password.required'        => 'Password wajib diisi.',
            'password.min'             => 'Password minimal 6 karakter.',
            'password.confirmed'       => 'Konfirmasi password tidak cocok.',
            'whatsapp_number.required' => 'Nomor WhatsApp wajib diisi.',
        ]);

        $user = User::create([
            'name'            => $oauthData['name'],
            'email'           => $oauthData['email'],
            'google_id'       => $oauthData['google_id'],
            'password'        => Hash::make($validated['password']),
            'whatsapp_number' => $validated['whatsapp_number'],
        ]);

        // Clean up session
        $request->session()->forget('oauth_data');

        Auth::login($user, true);

        return redirect()->route('dashboard');
    }

    // =============================================
    // LOGOUT
    // =============================================

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
