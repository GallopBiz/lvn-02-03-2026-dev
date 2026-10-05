<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Staff guard check
        if (Auth::guard('staff')->check()) {
            $staff = Auth::guard('staff')->user();
            $role = strtolower(trim($staff->role ?? ''));
            $isSpatieAdmin = method_exists($staff, 'hasRole') && $staff->hasRole('Admin');
            
            if ($role === 'admin' || $isSpatieAdmin) {
                return $next($request);
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Permission Denied: You do not have permission to access Admin pages.'], 403);
            }

            return redirect()->route('staff.dashboard')->with('error', 'Permission Denied: You do not have permission to access Admin pages.');
        }

        // 2. Web guard check (student_registration table)
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            $type = strtolower(trim($user->type ?? ''));
            
            if ($type === 'a') {
                return $next($request);
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Permission Denied: You do not have permission to access Admin pages.'], 403);
            }

            return redirect()->route('admin.dashboard')->with('error', 'Permission Denied: You do not have permission to access Admin pages.');
        }

        // 3. Unauthenticated
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect('/')->with('error', 'Please log in with an Admin account to access this page.');
    }
}
