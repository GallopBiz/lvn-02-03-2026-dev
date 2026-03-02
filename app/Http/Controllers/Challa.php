<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Echallan;

class Challa extends Controller
{
    public function index(){
        return view('backend.Transport.addchallan');
    }
    public function registerchallan(Request $request){
        echo"<pre>";
        print_r($request->all());
        $EChallan= new Echallan;
        $EChallan->Challan=$request['Challan'];
        $EChallan->Vehicle=$request['vehicle'];
        $EChallan->Amount=$request['Amount'];
        $EChallan->VType=$request['VType'];
        $EChallan->EntryDate=$request['EDate'];
        $EChallan->ChallanDate=$request['CDate'];
        $EChallan->Detechion=$request['Detechion'];
        $EChallan->Generation=$request['Generation'];
        $EChallan->Reason=$request['Reason'];
        $EChallan->Remark=$request['Remark'];
        $EChallan->Action=$request['Action'];
       
        $EChallan->save();
        echo"done"; 
        return redirect('challanlist');
    }
    public function challanlist(){
        $listofchallan= Echallan::all();
        return view('backend.Transport.challanlist',compact('listofchallan'));

    }
}
