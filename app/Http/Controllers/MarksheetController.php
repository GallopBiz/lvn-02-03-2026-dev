<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classes;
use App\Models\Classname;
use App\Models\CommanModel;
use App\Models\SubjectCombination;
use Illuminate\Support\Facades\Session;

class MarksheetController extends Controller

{
    public function index(){
        // $busstops = Busstop::where('is_delete',0)->get();
        // $select_main = Areamaster::All();
        $marksheets = '';
      


        $marksheet = SubjectCombination::where('is_delete', 0)->get();
        $subjectCombinations = SubjectCombination::where('is_delete', 0)->get();

        // $terms = DB::table('terms')->get();        
        return view('backend.AcademicsModules.marksheet', compact('marksheets','subjectCombinations','marksheet'));
    }









    }