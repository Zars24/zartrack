<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        if (! Auth::user()->is_active) {
            Auth::logout();
            throw ValidationException::withMessages(['email' => 'Akun Anda tidak aktif. Hubungi admin.']);
        }

        \App\Models\ActivityLog::record('auth.login', Auth::user(), 'Login berhasil');

        return redirect()->intended(Auth::user()->homeRoute());
    }

    public function destroy(Request $request)
    {
        \App\Models\ActivityLog::record('auth.logout', $request->user(), 'Logout');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
