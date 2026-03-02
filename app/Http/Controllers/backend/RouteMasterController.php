<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class RouteMasterController extends Controller
{
    public function index(Request $request)
{
    $db_name = $request->cookie('selectedYear', '2024_2025');

    $dynamicConnectionName = 'dynamic';
    $dynamicConfig = Config::get("database.connections.{$dynamicConnectionName}");
    $dynamicConfig['database'] = $db_name;
    Config::set("database.connections.{$dynamicConnectionName}", $dynamicConfig);
    DB::reconnect($dynamicConnectionName);

    $routes = DB::connection($dynamicConnectionName)
                ->table('routemaster')
                ->where('is_delete', 0)
                ->orderBy('id', 'asc')
                ->get();

    return view('backend.Transport.route-list', compact('routes'));
}
}
