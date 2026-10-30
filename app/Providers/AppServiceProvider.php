<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use App\Services\PermissionService;
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
        // Load global permission helper functions
        $helperPath = app_path('Helpers/permission_helpers.php');
        if (file_exists($helperPath)) {
            require_once $helperPath;
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Retrieve the list of databases
        try {
            $databases = DB::select('SHOW DATABASES');
            $databaseNames = [];
            foreach ($databases as $database) {
                $databaseNames[] = $database->Database;
            }
            view()->share('databaseNames', $databaseNames);
        } catch (\Throwable $e) {
            view()->share('databaseNames', []);
        }

        // Register custom Blade directives for action permissions
        Blade::if('canAction', function ($action, $module = null) {
            return PermissionService::can($action, $module);
        });

        Blade::if('canView', function ($module = null) {
            return PermissionService::can('view', $module);
        });

        Blade::if('canAdd', function ($module = null) {
            return PermissionService::can('add', $module);
        });

        Blade::if('canEdit', function ($module = null) {
            return PermissionService::can('edit', $module);
        });

        Blade::if('canDelete', function ($module = null) {
            return PermissionService::can('delete', $module);
        });
    }
}
