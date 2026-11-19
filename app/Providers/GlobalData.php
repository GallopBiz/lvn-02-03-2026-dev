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
                return DB::connection('dynamic')->table('student_registration')
                    ->where(function ($q) {
                        $q->where('is_archived', 0)->orWhereNull('is_archived');
                    })
                    ->where(function ($q) {
                        $q->where('next_year', 0)->orWhereNull('next_year');
                    })
                    ->where(function ($q) {
                        $q->whereNull('status')->orWhere('status', '!=', 't');
                    })
                    ->get();
            } catch (\Throwable $exception) {
                report($exception);

                return collect();
            }
        });
    }
}
