<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpertAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('panel.dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // فقط کارشناس و ادمین مجاز هستند
            if (!in_array($user->role, ['expert', 'admin'])) {
                Auth::logout();
                return back()->withErrors(['email' => 'شما دسترسی به پنل ندارید.']);
            }

            $request->session()->regenerate();
            return redirect()->route('panel.dashboard');
        }

        return back()->withErrors(['email' => 'ایمیل یا رمز عبور اشتباه است.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}