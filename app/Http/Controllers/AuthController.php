<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * AuthController
 *
 * Handles user registration, login, and logout.
 * Uses Laravel's built-in session-based authentication — no Sanctum/JWT.
 *
 * WHY session auth?
 * FinTrack is a server-rendered Blade application. Session auth is the
 * natural choice: simpler, no token management overhead, and fully
 * supported by Laravel's built-in guards.
 */
class AuthController extends Controller
{
    // ─── Register ─────────────────────────────────────────────────────────────

    /**
     * Show the registration form.
     * Redirect away if already logged in (no double-session).
     */
    public function register()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    /**
     * Validate, create user, auto-login, redirect to dashboard.
     *
     * WHY auto-login after register?
     * Removes friction. The user just gave us all their credentials —
     * making them log in again is pointless UX friction.
     */
    public function storeRegister(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required'      => 'Nama wajib diisi.',
            'email.required'     => 'Email wajib diisi.',
            'email.unique'       => 'Email sudah terdaftar. Silakan login.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Selamat datang di FinTrack, ' . $user->name . '! 🎉');
    }

    // ─── Login ────────────────────────────────────────────────────────────────

    /**
     * Show the login form.
     */
    public function login()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Validate credentials and log the user in.
     *
     * WHY 'remember = false' by default?
     * Personal finance data is sensitive. We don't auto-persist sessions
     * on shared devices unless explicitly requested.
     */
    public function storeLogin(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate(); // Prevent session fixation attacks

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang kembali, ' . Auth::user()->name . '!');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Email atau password salah.']);
    }

    // ─── Logout ───────────────────────────────────────────────────────────────

    /**
     * Log the user out and invalidate session.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login')
            ->with('success', 'Kamu berhasil keluar. Sampai jumpa! 👋');
    }
}
