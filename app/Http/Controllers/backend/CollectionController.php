<?php

namespace App\Http\Controllers\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CommanModel;
use DB;

class CollectionController extends Controller{

    public function index(){
        $data_nextyear = DB::table('totalnextyear')->where('is_delete','=','0')->get();
        return view('backend.collection.index',compact('data_nextyear'));
    }
}

?>