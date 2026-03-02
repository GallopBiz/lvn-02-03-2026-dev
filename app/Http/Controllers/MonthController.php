<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MonthController extends Controller
{
    public function monthWiseDays(Request $request)
    {
        $month =$request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        print_r($daysInMonth);
        return view('backend.AcademicsModules.Attandencereports',compact('month', 'year', 'daysInMonth'));

        exit;

        return view('month-wise-days', compact('month', 'year', 'daysInMonth'));
    }   
}
