<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classes;
use App\Models\Classname;
use App\Models\CommanModel;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use DB;

class ClassesController extends Controller

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

        $dynamicConnectionName = 'dynamic';
        $dynamicConfig = Config::get("database.connections.{$dynamicConnectionName}");
        $dynamicConfig['database'] = $db_name;
        Config::set('database.connections.dynamic', $dynamicConfig);
        DB::reconnect('dynamic');

    }

    public function index(){
		$maintenances = DB::connection('dynamic')->table('classes')->where('is_delete',0)->get();//Classes::where('is_delete',0)->get();
        $maintenancegs = DB::connection('dynamic')->table('class_name')->where('is_delete',0)->get();//Classname::where('is_delete',0)->get();
        $select_main = DB::connection('dynamic')->table('class_name')->where('is_delete',0)->get();//Classname::All();
        return view('backend.Fees-module.classes', compact('select_main','maintenancegs','maintenances'));
    }

    public function view($id){
        $maintenance_s = Classes::whereId($id)->get();
        $maintenances = Classes::orderBy('id','desc')->get();
        $maintenancegs = Classname::orderBy('id','desc')->get();
        $select_main = Classname::All();
        return view('backend.Fees-module.classes',compact('select_main','maintenancegs','maintenance_s','maintenances'));
    }

    public function editg($id){
        $maintenance_groups = Classname::whereId($id)->get();
        $maintenancegs = Classname::orderBy('id','desc')->get();
        $maintenances = Classes::orderBy('id','desc')->get();
        return view('backend.Fees-module.classes',compact('maintenance_groups','maintenancegs','maintenances'));
    }


    public function store(Request $request)
    {   
        $existingRecord = Classes::where('class_name', $request->class_name)
                                 ->where('section_name', $request->section_name)->where('is_delete',0)
                                 ->first();

        if ($existingRecord) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'A record with this class name and section name combination already exists.');
        }
        if (!empty($request->id)) {
            Classes::updateOrCreate(
                ['id' => $request->id],
                [
                    'class_name' => $request->class_name,
                    'section_name' => $request->section_name,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                ]
            );
        } else {
            $starttime = date("h:i A", strtotime($request->start_time));
            $endtime = date("h:i A", strtotime($request->end_time));
            $data = [
                'class_name'=>$request->class_name,
                'section_name'=>$request->section_name,
                'start_time'=>$starttime,
                'end_time'=>$endtime


            ];
            Classes::create($data);
        }
        return redirect()
            ->action([ClassesController::class, 'index'])
            ->with('success', 'class Master has been saved successfully.');
    }

    public function storeg(Request $request){
        $class_name = $request['class_name'];

        if (strlen($class_name) < 2) { // Check if the length is less than 2
            $class_name = '0' . $class_name; // Add '0' prefix
        }

        $newClassName = ['class_name' => $class_name];
        // echo"<pre>";print_r($newClassName);exit;
        $existingRecord = Classname::where('class_name', $newClassName)->where('is_delete',0)
                                 ->first();
        // print_r($existingRecord);exit;
        if ($existingRecord) {
            Session::put('class_error','A record with this class name and section name combination already exists.');
            return redirect()
                ->back();
                // ->withInput()
                // ->with('error', 'A record with this class name and section name combination already exists.');
        }
        if (!empty($request->id)){
            Classname::updateorcreate([
                'id' => $request->id
            ],
            [
                'class_name' => $request->class_name
            ]);
        } else {
            // $current_length = strlen($request['class_name']);
            $class_name = $request['class_name'];

            if (strlen($class_name) < 2) { // Check if the length is less than 2
                $class_name = '0' . $class_name; // Add '0' prefix
            }

            $newClassName = ['class_name' => $class_name];

            Classname::create($newClassName);
        }
        return redirect()->action([ClassesController::class, 'index'])->with('success','Maintenance Master has been Save successfully.');
    }
    public function editclasses(Request $request){
        $starttime = date("h:i A", strtotime($request->start_time));
        $endtime = date("h:i A", strtotime($request->end_time));
        // print_r($starttime);exit;

        $data = [
            'class_name'=>$request->class_name,
            'section_name'=>$request->section_name,
            'start_time'=>$starttime,
            'end_time'=>$endtime


        ];
        // print_r($request->all());exit;
        classes::whereId($request->id)->update($data);
        return redirect()->action([ClassesController::class, 'index'])->with('success','Record updated.');

    }
    public function classname_delete($id)
    {
        // echo $id;
        $a = explode('-',$id);
        $b = $a[1];        
        $c = $a[0];
        $delete_resp = CommanModel::soft_delete($c,['id'=>$b]);        
        if($delete_resp=='TRUE'){
            return redirect()->back()->with('success', 'Record successfully removed');
        }elseif($delete_resp=='FALSE'){
            return redirect()->back()->with('error', 'Record not removed1');
        }
    }
    public function classes_delete($id)
    {
        // echo $id;
        $a = explode('-',$id);
        $b = $a[1];        
        $c = $a[0];
        $delete_resp = CommanModel::soft_delete($c,['id'=>$b]);
        if($delete_resp=='TRUE'){
            return redirect()->back()->with('success', 'Record successfully removed');
        }elseif($delete_resp=='FALSE'){
            return redirect()->back()->with('error', 'Record not removed2');
        }
    }
 
    public function delete($id){
        $rtopapers = Classes::findOrFail($id);
        $rtopapers->delete();
        return redirect()->route('classes')->with('success','Bus Stop has been Deleted successfully.');
    }
}
