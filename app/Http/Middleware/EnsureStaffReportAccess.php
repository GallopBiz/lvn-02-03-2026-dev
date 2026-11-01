<?php

namespace App\Http\Middleware;

use App\Services\StaffReportAccessService;
use Closure;
use Illuminate\Http\Request;

class EnsureStaffReportAccess
{
    public function handle(Request $request, Closure $next)
    {
        app(StaffReportAccessService::class)->authorizeRequest($request);

        return $next($request);
    }
}
