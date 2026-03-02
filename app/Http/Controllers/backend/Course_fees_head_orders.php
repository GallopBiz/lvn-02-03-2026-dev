<?php

namespace App\Http\Controllers\backend;

use Illuminate\Http\Request;
use App\Models\CommanModel;
use App\Models\Course_fees_head_master;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Config;

class Course_fees_head_orders extends Controller
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

    public function course_fees_head_orders_list(){
        $course_fees_head_orders_list_arr = DB::connection('dynamic')->table('course_fees_head_master')->where('is_delete',0)->orderBy('id','DESC')->get();//Course_fees_head_master::where('is_delete',0)->orderBy('order','DESC')->get();
        return view('backend.course_fees_head_orders.course_fees_head_list',compact('course_fees_head_orders_list_arr'));
    }

    public function course_fees_head_orders_save(Request $request){
            
        $insertArr = ['ac_head_name'=>$request->ac_head_name,'remarks'=>$request->remarks];

        DB::connection('dynamic')->table('course_fees_head_master')->insert($insertArr);// Course_fees_head_master::Create($insertArr);        
     
        return redirect()->back()->with('success','data created successfully.');
    }

    public function course_fees_head_orders_edit($id){
        $course_fees_head_orders_list_edit = DB::connection('dynamic')->table('course_fees_head_master')->where('id',$id)->get();// CommanModel::getRowWhere('course_fees_head_master',['id'=>$id]);
        return view('backend.course_fees_head_orders.course_fees_head_edit',compact('course_fees_head_orders_list_edit'));
    }
 
    public function course_fees_head_orders_sortable(Request $request)
    {
        
        $course_fees_head_master_Arr = DB::connection('dynamic')->table('course_fees_head_master')->get(); //Course_fees_head_master::all();

        foreach ($course_fees_head_master_Arr as $each) {
            foreach ($request->order as $order) {
                if ($order['id'] == $each->id) {
                 $each->update(['order' => $order['position']]);
                }
            }
        }
        
        return response('Update Successfully.', 200);
    }

    public function delete($id){
        $feestype = Busstop::findOrFail($id);
        $feestype->delete();
        return redirect()->route('bus-stop')->with('success','Bus Stop has been Deleted successfully.');
    }

    public function course_fees_head_delete($id)
    {
        // echo $id;
        // exit;
        $a = explode('-',$id);
        $b = $a[1];        
        $c = $a[0];
        $delete_resp = DB::connection('dynamic')->table('course_fees_head_master')->where('id',$b)->update(['is_delete'=>1]); //CommanModel::soft_delete($c,['id'=>$b]);
        if($delete_resp=='TRUE'){
            return redirect()->back()->with('success', 'Record successfully removed');
        }elseif($delete_resp=='FALSE'){
            return redirect()->back()->with('error', 'Record not removed');
        } else {
            return redirect()->back()->with('success', 'Record successfully removed');
        }
    }
}