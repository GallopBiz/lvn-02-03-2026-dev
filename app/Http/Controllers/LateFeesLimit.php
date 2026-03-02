<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CommanModel;

class LateFeesLimit extends Controller
{
    public function index(){
        $data = CommanModel::getRowWhere('late_fees_max_limit',['id'=>1]);
        return view('backend.Fees-module.Late_Fees_Maximum',compact('data'));
    }

    public function update_late_fees_limit(Request $request){
        $update_id = $request->update_id;

        $updateData = [
            'from_this_no_of_days'=>$request->from_this_no_of_days,
            'to_this_no_of_days'=>$request->to_this_no_of_days,
            'max_late_fees'=>$request->max_late_fees,
        ];

        CommanModel::updateData('late_fees_max_limit',['id'=>$update_id],$updateData);
        return redirect()->back()->with('success','Data updated successfully');
    }
}
 