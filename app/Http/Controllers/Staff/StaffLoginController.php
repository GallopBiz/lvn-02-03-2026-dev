<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
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

            // Store selected session in session and cookie
            if ($selectedYear) {
                $request->session()->put('selectedYear', $selectedYear);
                setcookie('selectedYear', $selectedYear, time() + (86400 * 30), "/"); // 30 days
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
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->regenerate(); // Ensure a fresh session
        // Clear selectedYear cookie
        setcookie('selectedYear', '', time() - 3600, '/');
        return redirect('/staff-login');
    }

    private function switchDynamicDatabase(string $databaseName): void
    {
        Config::set('database.connections.dynamic.database', $databaseName);
        Config::set('database.default', 'dynamic');
        DB::purge('dynamic');
        DB::reconnect('dynamic');
        DB::setDefaultConnection('dynamic');
    }
}
