<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class SetDynamicDatabaseConnectionFromCookie
{
    public function handle($request, Closure $next)
    {	
		$dbConnection = "";
		if(isset($_COOKIE['selectedYear'])) {
            $dbConnection = $_COOKIE['selectedYear'];
        }
		if ($dbConnection) {
		    Config::set('database.connections.dynamic.database', $dbConnection);
			Config::set('database.default', 'dynamic');

            // Reconnect to apply new database settings
            DB::purge('dynamic');
            DB::reconnect('dynamic');
        }

        return $next($request);
    }
}
