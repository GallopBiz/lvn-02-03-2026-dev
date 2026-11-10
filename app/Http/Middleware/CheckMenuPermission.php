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
        $staffOnly = $action === 'staff';
        if ($staffOnly) {
            $action = null;
        }

        $route = $request->route();
        $routeName = $route ? $route->getName() : '';
        $path = trim($request->path(), '/');
        $staffPortal = $request->cookie('auth_portal') === 'staff'
            || ($request->hasSession() && $request->session()->get('auth_portal') === 'staff')
            || Str::startsWith($routeName, 'staff.')
            || Str::startsWith($path, 'staff/')
            || in_array('auth:staff', $route ? $route->gatherMiddleware() : [], true);

        if ($staffOnly && !$staffPortal) {
            return $next($request);
        }

        $user = $staffOnly
            ? Auth::guard('staff')->user()
            : PermissionService::getCurrentUser();

        if ($staffOnly) {
            $staffExemptRoutes = [
                'staff/dashboard',
                'staff.dashboard',
                'staff/change-password',
                'staff.password.change',
                'staff.password.update',
                'staff/logout',
                'staff.logout',
                'staff-logout',
                'staff.login',
                'staff.login.perform',
                'staff/academic/caste-wise-report',
                'staff.academic.caste-wise-report',
                'leaverequests-staff',
                'leaverequests.staff',
                'compoffrequests-staff',
                'compoffrequests.staff',
            ];

            foreach ($staffExemptRoutes as $exempt) {
                if ($path === $exempt || Str::is($exempt, $path) || ($routeName && Str::is($exempt, $routeName))) {
                    return $next($request);
                }
            }
        }

        // If unauthenticated, let auth middleware handle redirection
        if (!$user) {
            if ($staffOnly && $staffPortal) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['error' => 'Unauthenticated.'], 401);
                }

                return redirect()->route('staff.login');
            }

            return $next($request);
        }

        // Administrators bypass all permission checks
        if (PermissionService::isAdmin($user)) {
            return $next($request);
        }

        // Check exempt routes for routes using the general permission middleware.
        $path = trim($request->path(), '/');
        $routeName = $request->route() ? $request->route()->getName() : '';

        if (!$staffOnly) {
            foreach ($this->exemptRoutes as $exempt) {
                if ($path === $exempt || Str::is($exempt, $path) || ($routeName && Str::is($exempt, $routeName))) {
                    return $next($request);
                }
            }
        }

        // Student users are restricted to their own student routes and dashboard
        $isStudent = !$staffPortal
            && Auth::guard('web')->check()
            && strtolower(trim((string) ($user->type ?? ''))) !== 'a';
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

        if ($staffOnly) {
            abort(403, $message);
        }

        $fallbackRoute = Auth::guard('staff')->check() ? 'staff.dashboard' : 'admin.dashboard';

        return redirect()->route($fallbackRoute)->with('error', $message);
    }
}
