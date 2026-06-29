<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use DB;
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
     * Create a new controller instance.
     *
     * @return void
     */
    protected $db_name;
    public function __construct(Request $request)
    {
        if (!empty($request->year)){
            $this->db_name = $request->year; 
        } else {
            $this->db_name = "2025_2026";
        }
        // $dynamicConnectionName = 'dynamic';
        // $dynamicConfig = Config::get("database.connections.{$dynamicConnectionName}");
        // $dynamicConfig['database'] = $this->db_name;
        // Config::set('database.connections.dynamic', $dynamicConfig);
        // DB::reconnect('dynamic');
        // DB::connection('dynamic');
        // $this->middleware('guest')->except('logout');
    }
    
    public function redirectTo()
    {
        // You can set the $redirectTo value dynamically here based on your conditions.
        // For example, if you have a session variable 'custom_redirect', you can use it like this:
        Session::put('db_names', $this->db_name);
        return $this->redirectTo;
    }

    public function logout(Request $request)
    {
        $isStaff = \Illuminate\Support\Facades\Auth::guard('staff')->check();

        \Illuminate\Support\Facades\Auth::guard('web')->logout();
        \Illuminate\Support\Facades\Auth::guard('staff')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        setcookie('selectedYear', '', time() - 3600, '/');

        if ($isStaff) {
            return redirect('/staff-login');
        }

        return redirect('/');
    }
}
