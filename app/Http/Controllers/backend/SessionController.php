<?php

namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

class SessionController extends Controller{
    public function index(){
        return view('backend.session.index');
    }

    public function create(Request $request){
        $database = $request->start_session.'-'.$request->end_session;
        echo 'Hello ';
    }
}