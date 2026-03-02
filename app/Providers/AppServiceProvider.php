<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        // Retrieve the list of databases
        $databases = DB::select('SHOW DATABASES');

        // Extract database names and store in a global array
        $databaseNames = [];
        foreach ($databases as $database) {
            $databaseNames[] = $database->Database;
        }

        // Share the array with all views
        view()->share('databaseNames', $databaseNames);
    }
}
