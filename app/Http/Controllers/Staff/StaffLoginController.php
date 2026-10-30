<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StaffLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('staff.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'year' => ['required', 'regex:/^\d{4}_\d{4}$/'],
        ]);

        $selectedYear = $request->input('year');
        $this->switchDynamicDatabase($selectedYear);

        $credentials = $request->only('username', 'password');
        Log::info('Staff login attempt', ['credentials' => $credentials]);
        if (Auth::guard('staff')->attempt($credentials)) {
            Log::info('Staff login successful', ['username' => $credentials['username']]);
            $request->session()->regenerate();

            // Mark session & cookie as staff auth portal
            $request->session()->put('auth_portal', 'staff');
            Cookie::queue('auth_portal', 'staff', 60 * 24 * 30);

            // Store selected session in session and cookie
            if ($selectedYear) {
                $request->session()->put('selectedYear', $selectedYear);
                $request->session()->put('db_names', $selectedYear);
                Cookie::queue('selectedYear', $selectedYear, 60 * 24 * 30); // 30 days
            }

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
        $request->session()->forget('auth_portal');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->regenerate(); // Ensure a fresh session
        // Clear cookies safely
        Cookie::queue(Cookie::forget('auth_portal'));
        Cookie::queue(Cookie::forget('selectedYear'));
        return redirect('/staff-login');
    }

    private function switchDynamicDatabase(string $databaseName): void
    {
        Config::set('database.connections.dynamic.database', $databaseName);
        Config::set('database.connections.mysql.database', $databaseName);
        Config::set('database.default', 'dynamic');
        DB::purge('dynamic');
        DB::purge('mysql');
        DB::reconnect('dynamic');
        DB::setDefaultConnection('dynamic');
    }
}
