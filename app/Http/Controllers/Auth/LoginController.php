<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Target academic database name.
     *
     * @var string
     */
    protected $db_name;

    public function username()
    {
        return 'scholar_no';
    }

    public function __construct(Request $request)
    {
        if (!empty($request->year)){
            $this->db_name = $request->year; 
        } else {
            $this->db_name = "2025_2026";
        }
    }

    /**
     * Attempt to log the user into the application using the requested academic session database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        $year = $request->input('year') ?: $this->db_name ?: '2025_2026';
        $normalizedYear = str_replace('-', '_', trim((string)$year));

        Config::set('database.connections.dynamic.database', $normalizedYear);
        Config::set('database.connections.mysql.database', $normalizedYear);
        Config::set('database.default', 'dynamic');
        DB::purge('dynamic');
        DB::purge('mysql');

        return $this->guard()->attempt(
            $this->credentials($request), $request->filled('remember')
        );
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        $year = $request->input('year') ?: $this->db_name ?: '2025_2026';
        $normalizedYear = str_replace('-', '_', trim((string)$year));

        // If student user has an assigned session_name, prefer that academic session
        if (isset($user->type) && strtolower(trim((string)$user->type)) !== 'a' && !empty($user->session_name)) {
            $normalizedYear = str_replace('-', '_', trim((string)$user->session_name));
        }

        Session::put('selectedYear', $normalizedYear);
        Session::put('db_names', $normalizedYear);
        Session::put('auth_portal', 'web');

        Cookie::queue('selectedYear', $normalizedYear, 60 * 24 * 30);
        Cookie::queue('auth_portal', 'web', 60 * 24 * 30);

        Config::set('database.connections.dynamic.database', $normalizedYear);
        Config::set('database.connections.mysql.database', $normalizedYear);
        Config::set('database.default', 'dynamic');
        DB::purge('dynamic');
        DB::purge('mysql');

        return redirect()->intended($this->redirectPath());
    }

    public function redirectTo()
    {
        return $this->redirectTo;
    }

    public function logout(Request $request)
    {
        $isStaff = Auth::guard('staff')->check()
            || $request->session()->get('auth_portal') === 'staff'
            || $request->cookie('auth_portal') === 'staff';

        Auth::guard('web')->logout();
        Auth::guard('staff')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Cookie::queue(Cookie::forget('auth_portal'));
        Cookie::queue(Cookie::forget('selectedYear'));

        if ($isStaff) {
            return redirect('/staff-login');
        }

        return redirect('/');
    }
}
