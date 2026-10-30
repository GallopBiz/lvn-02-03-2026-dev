<?php

namespace App\Http\Middleware;

use App\Services\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CheckMenuPermission
{
    /**
     * Routes that any authenticated user (staff or student) is permitted to access without module checks.
     *
     * @var array
     */
    protected $exemptRoutes = [
        'staff/dashboard',
        'staff.dashboard',
        'staff/change-password',
        'staff.password.change',
        'staff.password.update',
        'staff/logout',
        'staff.logout',
        'notifications',
        'notifications/*',
        'staff-notifications',
        'staff-notifications/*',
        'admin-dashboard',
        'admin.dashboard',
        'Dashboard',
        'dashboard',
        'student_calender',
        'student_fees_leadger',
        'student_announcement',
        'razorpay-payment',
        'razorpay-payment/*',
        'student/update-password',
        'student.update-password',
        'route-vehicle-map',
        'backend.route.vehicle.map',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $action
     * @param  string|null  $module
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ?string $action = null, ?string $module = null)
    {
        $user = Auth::guard('web')->user() ?: Auth::guard('staff')->user();

        // If unauthenticated, let auth middleware handle redirection
        if (!$user) {
            return $next($request);
        }

        // Administrators bypass all permission checks
        if (PermissionService::isAdmin($user)) {
            return $next($request);
        }

        // Check exempt routes
        $path = trim($request->path(), '/');
        $routeName = $request->route() ? $request->route()->getName() : '';

        foreach ($this->exemptRoutes as $exempt) {
            if ($path === $exempt || Str::is($exempt, $path) || ($routeName && Str::is($exempt, $routeName))) {
                return $next($request);
            }
        }

        // Student users are restricted to their own student routes and dashboard
        $isStudent = Auth::guard('web')->check() && strtolower(trim((string)($user->type ?? ''))) !== 'a';
        if ($isStudent) {
            $message = "Permission Denied: Students are not authorized to access this module.";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => $message], 403);
            }
            return redirect()->route('admin.dashboard')->with('error', $message);
        }

        // If action or module not explicitly specified in route definition, detect automatically
        if (!$action || !$module) {
            $detected = PermissionService::detectModuleAndActionFromRequest($request);
            $action = $action ?: $detected['action'];
            $module = $module ?: $detected['module'];
        }

        // Check permission
        if (PermissionService::can($action, $module, $user)) {
            return $next($request);
        }

        // Unauthorized response
        $message = "Permission Denied: You do not have permission to {$action} in this module.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['error' => $message], 403);
        }

        $fallbackRoute = Auth::guard('staff')->check() ? 'staff.dashboard' : 'admin.dashboard';

        return redirect()->route($fallbackRoute)->with('error', $message);
    }
}

