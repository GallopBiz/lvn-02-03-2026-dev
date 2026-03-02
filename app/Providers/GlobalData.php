<?php

namespace App\Providers;
use App\Http\Controllers\Controller;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use DB;

class GlobalData extends ServiceProvider
{

    public function register()
    {
        
        $inqArr = DB::connection('dynamic')->table('student_registration')->get();
        $this->app->bind('global_areas', function () use ($inqArr) {
            return $inqArr;
        });
    }
}

