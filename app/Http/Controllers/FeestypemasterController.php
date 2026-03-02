<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feestype;
use App\Models\CommanModel;
use DB;
use Illuminate\Support\Facades\Config;

class FeestypemasterController extends Controller{
    //

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
    // print_r($db_name);die();
    }

    public function show(){
        $feestype = Feestype::where('is_delete',0)->get();
        return view('backend.fees_index', compact('feestype'));
    }

    public function create(Request $request){
        Feestype::create($request->post());
        return redirect()->route('fees-type-master')->with('success','Fess Type Master has been created successfully.');
    }

    public function edit($id){
        // print_r($id);
        // exit;
        $role = Feestype::whereId($id)->get();
        $feestype = Feestype::orderBy('id','desc')->paginate(5);
        return view('backend.fees_index',compact('role','feestype'));
    }
    public function update(Request $request){
        // print_r($request->all());
        // exit;

        
        $data = [
            'remarks' => $request->remarks,
            'feestype' => $request->feestype,

        ];
        echo"<pre>";
        print_r($data);
        // exit;
        Feestype::whereId($request->id)->update($data);
        echo "done";
        return redirect()->route('fees-type-master')->with('success','data has been Updated successfully.');
        // return view('backend.Transport.parymaster');
        // return view('backend.Transport.parytmasterlist')->with('success','party  Master has been Updated successfully.');
    }
    public function store(Request $request){
        // print_r($request->all());
        // exit;
        $data = [
            'feestype' => $request->feestype,
            'remarks' => $request->remarks,
            // 'date' => $request->date,
            // 'amount' => $request->amount,
            // 'select_option' => $request->select_option,
        ];
        Feestype::create($request->post());
        return redirect()->route('fees-type-master')->with('success','Fess Type Master has been Updated successfully.');
    }

    public function fees_type_master_delete($id)
    {

        $a = explode('-',$id);
        $b = $a[1];        
        $c = $a[0];

        $delete_resp = DB::connection('dynamic')->table("$c")->where('id', "$b")->update(['is_delete' => '1']);//CommanModel::soft_delete($c,['id'=>$b]);
        if($delete_resp=='TRUE'){
            return redirect()->back()->with('success', 'Record successfully removed');
        }elseif($delete_resp=='FALSE'){
            return redirect()->back()->with('error', 'Record not removed');
        }else{
            return redirect()->back()->with('success', 'Record successfully removed');
        }
    }
}