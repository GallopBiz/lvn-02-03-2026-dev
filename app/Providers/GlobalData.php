<?php

namespace App\Providers;
use App\Http\Controllers\Controller;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class GlobalData extends ServiceProvider
{

    public function register()
    {
        $this->app->bind('global_areas', function () {
            try {
                return DB::connection('dynamic')->table('student_registration')->get();
            } catch (\Throwable $exception) {
                report($exception);

                return collect();
            }
        });
    }
}
