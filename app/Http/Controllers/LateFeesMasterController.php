<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Late_fees_master;
use App\Models\CommanModel;
use DB;
use Illuminate\Support\Facades\Config;

class LateFeesMasterController extends Controller
{ 
    public function __construct(Request $request)
    {
        if (isset($_COOKIE['selectedYear'])) {
            $selectedYear = $_COOKIE['selectedYear'];
            // Now you can use $selectedYear in your PHP code
            $selectedYear;
        }
        
        if (!empty($request->session)){
            $db_name = $request->session; 
        } else if (!empty($request->session_name)){
            $db_name = $request->session_name;
        } else {
            if (isset($_COOKIE['selectedYear'])) {
                $db_name = $_COOKIE['selectedYear'];
            } else {
                $db_name = "2023_2024";
            }
            
        }
        // $db_name = "2023_2024";

        $dynamicConnectionName = 'dynamic';
        $dynamicConfig = Config::get("database.connections.{$dynamicConnectionName}");
        $dynamicConfig['database'] = $db_name;
        Config::set('database.connections.dynamic', $dynamicConfig);
        DB::reconnect('dynamic');

    }

    public function late_fees_master(){
        $late_fees_master_arr = DB::connection('dynamic')->table('late_fees_master')->where('is_delete',0)->orderBy('id','DESC')->get(); //Late_fees_master::where('is_delete',0)->get();
        return view('backend.Fees-module.Late_Fees_master',compact('late_fees_master_arr'));
    }

    public function save_late_fees_master(Request $request){

        $data = [
            'late_fees_amount' => $request->late_fees_amount,
            'from_amount' => $request->from_amount,
            'to_amount' => $request->to_amount,
            'upto' => $request->upto,
        ];

        DB::connection('dynamic')->table('late_fees_master')->insert($data); // Late_fees_master::create($data);

        return redirect()->route('late-fees-master')->with('success','Record created successfully');
    }

    public function late_fees_master_soft_delete($id)
    {   
        //  echo $id;
        // exit;
        $a = explode('-',$id);
        $b = $a[1];        
        $c = $a[0];
        $delete_resp = DB::connection()->table('late_fees_master')->where('id',$b)->update([['is_delete'=>1]]); //CommanModel::soft_delete($c,['id'=>$b]);
        if($delete_resp=='TRUE'){
            return redirect()->back()->with('success', 'Record successfully removed');
        }elseif($delete_resp=='FALSE'){
            return redirect()->back()->with('error', 'Record not removed');
        } else {
            return redirect()->back()->with('success', 'Record successfully removed');
        }
    }

    public function late_fees_master_edit($id)
    {
        $edit_data = DB::connection('dynamic')->table('late_fees_master')->where('id',$id)->first(); //Late_fees_master::find($id);
        return view('backend.Fees-module.Late_Fees_master', compact('edit_data'));
    }

    public function late_fees_master_update(Request $request, $id){
        $data = [
            'late_fees_amount' => $request->input('late_fees_amount'),
            'from_amount' => $request->input('from_amount'),
            'to_amount' => $request->input('to_amount'),
            'upto' => $request->input('upto'),
        ];
        DB::connection('dynamic')->table('late_fees_master')->where('id',$id)->update($data);
        // $late_fees_master->late_fees_amount = $request->input('late_fees_amount');
        // $late_fees_master->from_amount = $request->input('from_amount');
        // $late_fees_master->to_amount = $request->input('to_amount');
        // $late_fees_master->upto = $request->input('upto');
        // $late_fees_master->update();
        return redirect()->back()->with('success', 'Record Updated successfully');  
    }
}