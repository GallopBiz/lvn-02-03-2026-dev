<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class StaffLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('staff.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');
        Log::info('Staff login attempt', ['credentials' => $credentials]);
        if (Auth::guard('staff')->attempt($credentials)) {
            Log::info('Staff login successful', ['username' => $credentials['username']]);
            $request->session()->regenerate();
            return redirect()->intended('/staff/dashboard');
        }
        Log::warning('Staff login failed', ['username' => $credentials['username']]);
        return back()->withErrors([
            'username' => 'Invalid credentials.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('staff')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/staff-login');
    }
}
