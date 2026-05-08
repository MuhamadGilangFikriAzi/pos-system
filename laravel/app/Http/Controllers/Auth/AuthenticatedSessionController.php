<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Cek akun aktif
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Akun Anda dinonaktifkan. Hubungi admin.',
                ]);
            }

            $request->session()->regenerate();

            // Update last login
            $user->last_login_at = now();
            $user->last_login_ip = $request->ip();
            $user->save();

            // Log aktivitas login
            ActivityLog::log('login', "Login sebagai {$user->name} ({$user->role})");

            return redirect()->intended(route('pos.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function destroy(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            // Log aktivitas logout
            ActivityLog::log('logout', "Logout {$user->name}");
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
