<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LogoutController extends Controller
{
    /**
     * Log out account user.
     *
     * @return \Illuminate\Routing\Redirector
     */
    public function perform(Request $request)
    {
        $isStaff = Auth::guard('staff')->check();

        Auth::guard('web')->logout();
        Auth::guard('staff')->logout();
        Session::flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        setcookie('selectedYear', '', time() - 3600, '/');

        if ($isStaff) {
            return redirect('/staff-login');
        }

        return redirect('login');
    }
}
