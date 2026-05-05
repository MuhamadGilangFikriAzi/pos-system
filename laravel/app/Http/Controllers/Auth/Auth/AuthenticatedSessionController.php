<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request )
    {
         = ->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt(, ->boolean('remember'))) {
            ->session()->regenerate();
            return redirect()->intended(route('pos.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function destroy(Request )
    {
        Auth::guard('web')->logout();
        ->session()->invalidate();
        ->session()->regenerateToken();
        return redirect('/');
    }
}