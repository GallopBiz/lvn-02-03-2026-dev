<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Busfees;
use App\Models\CommanModel;
use DB;
use Illuminate\Support\Facades\Config;

class BusfeesmasterController extends Controller
{
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

    public function index(){
        $busfees = DB::connection('dynamic')
        ->table('busfees')
        ->where('is_delete', '=', 0)
        ->get();
        //Busfees::where('is_delete',0)->get();
        return view('backend.bus_fees_index', compact('busfees'));
        
    }

    public function create(Request $request){
        $data = [
            'select_batch' => $request->select_batch,
            'busfeestypename' => $request->busfeestypename,
            'date' => $request->date,
            'amount' => $request->amount,
        ];
        // print_r($data);exit;
        DB::connection('dynamic')->table('busfees')->insert($data);//Busfees::create($request->post());
        return redirect()->route('bus-fees-master')->with('success','Bus Fess has been created successfully.');
    }

    public function view($id){
        $bus_feess = Busfees::whereId($id)->get();
        $busfees = Busfees::orderBy('id','desc')->paginate(5);
        return view('backend.bus_fees_index',compact('bus_feess','busfees'));
    }

    public function edit($id){
        $bus_feess = DB::connection('dynamic')->table('busfees')->where('id',$id)->get();//Busfees::whereId($id)->get();
        return view('backend.bus_fees_index',compact('bus_feess'));
    }

    public function store(Request $request){
        $data = [
            'select_batch' => $request->select_batch,
            'busfeestypename' => $request->busfeestypename,
            'date' => $request->date,
            'amount' => $request->amount,
            'select_option' => $request->select_option,

        ];
        DB::connection('dynamic')->table('busfees')->where('id', $request->id)->update($data);
         //Busfees::whereId($request->id)->update($data);
        return redirect()->route('bus-fees-master')->with('success','Fess Type Master has been Updated successfully.');
    }
    public function busfees_delete($id)
    {
        // echo $id;
        // exit;
        $a = explode('-',$id);
        $b = $a[1];        
        $c = $a[0];
        $delete_resp = DB::connection('dynamic')
        ->table("$c")
        ->where('id', $b)
        ->update(['is_delete' => 1]);
        //CommanModel::soft_delete($c,['id'=>$b]);
        if($delete_resp=='TRUE'){
            return redirect()->back()->with('success', 'Record successfully removed');
        }elseif($delete_resp=='FALSE'){
            return redirect()->back()->with('error', 'Record not removed');
        }else{
            return redirect()->back()->with('success', 'Record successfully removed');
        }
    }
    public function delete($id){
        $feestype = Busfees::findOrFail($id);
        $feestype->delete();
        return redirect()->route('bus-fees-master')->with('success','Fess Type Master has been Deleted successfully.');
    }
}
