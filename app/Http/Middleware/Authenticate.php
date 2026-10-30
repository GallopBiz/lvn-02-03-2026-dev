<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Determine if the user is logged in to any of the given guards.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $guards
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function authenticate($request, array $guards)
    {
        // When no specific guard is specified, check both 'web' and 'staff' guards
        if (empty($guards) || $guards === [null]) {
            $guards = ['web', 'staff'];
        }

        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                return $this->auth->shouldUse($guard);
            }
        }

        $this->unauthenticated($request, $guards);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            // Determine if the unauthenticated user was using the staff portal
            $isStaffPortal = $request->is('staff')
                || $request->is('staff/*')
                || $request->cookie('auth_portal') === 'staff'
                || ($request->hasSession() && $request->session()->get('auth_portal') === 'staff')
                || str_contains((string) $request->headers->get('referer'), 'staff');

            if ($isStaffPortal) {
                return route('staff.login');
            }

            return route('login');
        }
    }
}
