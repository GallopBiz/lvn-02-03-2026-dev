<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BusStaf;
use App\Models\CommanModel;
use App\Models\Inquiry_registration;
use App\Models\Schedulemasterall;
use App\Models\Schedulemaster;
use App\Models\AddVehial;
use App\Models\Routemaster;
use App\Models\Routeareabusmaster;
use App\Models\Teacherbusassign;
use DB;

class TeacherBusAssignController extends Controller
{
    public function index(){
        $schedulemasters = Schedulemaster::where('is_delete',0)->get();
        // return view('backend.Transport.Teacherbusassign',compact('schedulemasters'));
        // return view('backend.Transport.Teacherbusassign');
        
        $classlist = DB::table('classes')->select('class_name')->distinct()->get();
        // return view('backend.Transport.Scholarbusassign',compact('schedulemasters','datas'));
        return view('backend.Transport.Teacherbusassign',compact('schedulemasters','classlist'));
    }
    public function student_get(Request $request){
        $date = $request->get('date');
        $class_name = $request->get('class_name');
        $section_name = $request->get('section_name');

        ## Add custom filter conditions || !empty($class_name) || !empty($date) || !empty($section_name)
        // if(!empty($date)){
        //     $records1 = Inquiry_registration::select('*')
        //     ->orwhere("date","=", $date);
        // }
        // if(!empty($class_name)){
        //     $records1->orwhere("class_name","=", $class_name);
        // }
        // if(!empty($section_name)){
        //     $records1->orwhere("section_name","=", $section_name);
        // }
        // $data = $records1->get();

        $datas = Inquiry_registration::select('student_name','id')->get();
        foreach($datas as $data){
            $arr = Teacherbusassign::select('*')->where('student_id_select_p',$data->id)->first();
            
            $stor_data[] = [
                'id' => $data->id,
                'student_name' => $data->student_name,
                'pick_shedule_name' => empty($arr->pick_shedule_name) ? '-' : $arr->pick_shedule_name,
                'pick_up_routes' => empty($arr->pick_up_routes) ? '-' : $arr->pick_up_routes,
                'pickup_area_name' => empty($arr->pickup_area_name) ? '-' : $arr->pickup_area_name,
                'pickup_bus_stop_names' => empty($arr->pickup_bus_stop_names) ? '-' : $arr->pickup_bus_stop_names,
                'pickup_bus_no' => empty($arr->pickup_bus_no) ? '-' : $arr->pickup_bus_no,
                'drop_shedule_name' => empty($arr->drop_shedule_name) ? '-' : $arr->drop_shedule_name,
                'drop_up_route' => empty($arr->drop_up_route ) ? '-' : $arr->drop_up_route,
                'drop_area_name' => empty($arr->drop_area_name) ? '-' : $arr->drop_area_name,
                'drop_bus_stop_name' => empty($arr->drop_bus_stop_name) ? '-' : $arr->drop_bus_stop_name,
                'pickup_bool' => empty($arr->pick_shedule_name) ? 'disabled="true"' : '',
                'drop_bool' => empty($arr->drop_shedule_name) ? 'disabled="true"' : '',
                'pickup_bool_s' => empty($arr->pick_shedule_name) ? '' : 'disabled="true"',
                'drop_bool_s' => empty($arr->drop_shedule_name) ? '' : 'disabled="true"',
            ];
            // $arr[] = $data->id;
        }
        return json_encode($stor_data);

    }

    public function create(Request $request){
        BusStaf::create($request->post());
        return redirect()->route('driver-conductor-master')->with('success',' has been created successfully.');
    }

    public function view($id){
        $bus_stafs = BusStaf::whereId($id)->get();
        $busStafs = BusStaf::orderBy('id','desc')->paginate(5);
        return view('backend.Transport.BusStaff',compact('bus_stafs','busStafs'));
    }

    public function store(Request $request){
        $data = [
            'role' => $request->role,
            'ename' => $request->ename,
            'mobile_number' => $request->mobile_number,
            'aadhar_number' => $request->aadhar_number,
            'sssmid' => $request->sssmid,
            'current_address' => $request->current_address,
            'parmanent_address' => $request->parmanent_address,
            'license_no' => $request->license_no,
            'license_expire' => $request->license_expire,
            'license_lssue' => $request->license_lssue,
            'voter_id_no' => $request->voter_id_no,
            'joining_date' => $request->joining_date,
            'leaving_date' => $request->leaving_date,
            'leaving_date1' => $request->leaving_date1,
            'call_no' => $request->call_no,
            'offical_mobile_no' => $request->offical_mobile_no,
            'remarks' => $request->remarks,
            'healthstatus' => $request->healthstatus,
        ];
        BusStaf::whereId($request->id)->update($data);
        return redirect()->route('driver-conductor-master')->with('success','Bus Stop has been Updated successfully.');
    }

    public function delete($id){
        $BusStaf = BusStaf::findOrFail($id);
        $BusStaf->delete();
        return redirect()->route('driver-conductor-master')->with('success','Bus Stop has been Deleted successfully.');
    }
    public function busstaff_delete(Request $request)
    {
        // print_r($request->all());
        // exit;
        $table = $request->table_name;
        $delete_resp = CommanModel::soft_delete($table,['id'=>$request->delete_id]);
        if($delete_resp=='TRUE'){
            return redirect()->back()->with('success', 'Record successfully removed');
        }elseif($delete_resp=='FALSE'){
            return redirect()->back()->with('error', 'Record not removed');
        }
    }

    public function scholarbusassign_student_shedule_namee(Request $request){
        $shedule_name = $request->get('shedule_name');
        $data = Schedulemasterall::select("schedule_check_one")->where('schedule_name',$shedule_name)->get();
        $arr[] = ' -- Select Pick Route Name -- ';
        foreach($data as $val){
            $arrs = json_decode($val->schedule_check_one);
            foreach($arrs as $arr1){
                $arr[] = $arr1->route_name;
            }
        }
        
        if(!empty($arr)){
            return json_encode(array_unique($arr));
        } else {
            return json_encode([" -- Select Pick Route Name -- "]);
        }
    }

    public function scholarbusassign_student_shedule_name_drop(Request $request){
        $shedule_name = $request->get('shedule_name');
        $data = Schedulemasterall::select("schedule_check_one")->where('schedule_name',$shedule_name)->get();
        $arr[] = ' -- Select Pick Route Name -- ';
        foreach($data as $val){
            $arrs = json_decode($val->schedule_check_one);
            foreach($arrs as $arr1){
                $arr[] = $arr1->route_name;
            }
        }
        
        if(!empty($arr)){
            return json_encode(array_unique($arr));
        } else {
            return json_encode([" -- Select Pick Route Name -- "]);
        }
    }

    public function scholarbusassign_student_pick_up_routes(Request $request){
        $pickup_area_name = $request->get('pickup_area_name');
        $data = Routeareabusmaster::select("area_name")->where('route_name',$pickup_area_name)->get();
        $arr[] = ' -- Select Pick Area Name -- ';
        foreach($data as $val){
            $arr[] = $val->area_name;
        }
        
        if(!empty($arr)){
            return json_encode(array_unique($arr));
        } else {
            return json_encode([" -- Select Pick Area Name -- "]);
        }
    }

    public function scholarbusassign_student_drop_routes(Request $request){
        $pickup_area_name = $request->get('pickup_area_name');
        $data = Routeareabusmaster::select("area_name")->where('route_name',$pickup_area_name)->get();
        $arr[] = ' -- Select Pick Area Name -- ';
        foreach($data as $val){
            $arr[] = $val->area_name;
        }
        
        if(!empty($arr)){
            return json_encode(array_unique($arr));
        } else {
            return json_encode([" -- Select Pick Area Name -- "]);
        }
    }

    public function scholarbusassign_student_bus_stop_name(Request $request){
        $bus_stop_name = $request->get('bus_stop_name');
        $data = Routeareabusmaster::select("bus_stop_name")->where('area_name',$bus_stop_name)->get();
        $arr[] = ' -- Select Pick Up Bus Stop Name -- ';
        foreach($data as $val){
            $arr[] = $val->bus_stop_name;
        }
        
        if(!empty($arr)){
            return json_encode(array_unique($arr));
        } else {
            return json_encode([" -- Select Pick Up Bus Stop Name -- "]);
        }
    }

    public function scholarbusassign_student_bus_stop_name_drop(Request $request){
        $bus_stop_name = $request->get('bus_stop_name');
        $data = Routeareabusmaster::select("bus_stop_name")->where('area_name',$bus_stop_name)->get();
        $arr[] = ' -- Select Pick Up Bus Stop Name -- ';
        foreach($data as $val){
            $arr[] = $val->bus_stop_name;
        }
        
        if(!empty($arr)){
            return json_encode(array_unique($arr));
        } else {
            return json_encode([" -- Select Pick Up Bus Stop Name -- "]);
        }
    }

    public function scholarbusassign_student_bus_no(Request $request){
        $shedule_name = $request->get('shedule_name');
        $pick_up_routes = $request->get('pick_up_routes');

        $data = Schedulemasterall::select("schedule_check_one")->where('schedule_name',$shedule_name)->get();
        $arr[] = ' -- Select Pick Up Bus No -- ';
        foreach($data as $val){
            $arrs = json_decode($val->schedule_check_one);
            foreach($arrs as $arr1){
                if ($pick_up_routes == $arr1->route_name){
                    $arr[] = $arr1->vehicelno;
                }
            }
        }
        
        if(!empty($arr)){
            return json_encode(array_unique($arr));
        } else {
            return json_encode([" -- Select Pick Up Bus No -- "]);
        }
    }

    public function scholarbusassign_post_pickup(Request $request){
        if(!empty($request->post('type_to_create'))){
            $id = $request->post('student_id_select_p');
            Teacherbusassign::where('student_id_select_p', $id)->update([
                'pick_shedule_name' => $request->post('pick_shedule_name'),
                'pick_up_routes' => $request->post('pick_up_routes'),
                'pickup_area_name' => $request->post('pickup_area_name'),
                'pickup_bus_stop_names' => $request->post('pickup_bus_stop_names'),
                'pickup_bus_no' => $request->post('pickup_bus_no')
            ]);
        } else {
            // print_r($request->all());
            // exit;
            Teacherbusassign::create($request->post());
        }
        return redirect()->route('teacherbusassign')->with('success',' has been created successfully.');
    }

    public function scholarbusassign_post_drop(Request $request){

       
        $id = $request->post('student_id_select_d');
            // print_r($request->all());
        if(!empty($request->post('type_to_create'))){
            // print("create");
            // exit;
            Teacherbusassign::where('student_id_select_p', $id)->update([
                'drop_shedule_name' => $request->post('drop_shedule_name'),
                'drop_up_route' => $request->post('drop_up_route'),
                'drop_area_name' => $request->post('drop_area_name'),
                'drop_bus_stop_name' => $request->post('drop_bus_stop_name')
            ]);
        } else {
            // print_r("update");
            // exit;
            Teacherbusassign::where('student_id_select_p', $id)->update([
                'drop_shedule_name' => $request->post('drop_shedule_name'),
                'drop_up_route' => $request->post('drop_up_route'),
                'drop_area_name' => $request->post('drop_area_name'),
                'drop_bus_stop_name' => $request->post('drop_bus_stop_name')
            ]);
        }
        // print_r($request->all());
        // exit;
        // Scholarbusassign::updateorcreate([
        //     'student_id_select_p' => $id,
        // ],
        // [
        //     'drop_shedule_name' => $request->post('drop_shedule_name'),
        //     'drop_up_route' => $request->post('drop_up_route'),
        //     'drop_area_name' => $request->post('drop_area_name'),
        //     'drop_bus_stop_name' => $request->post('drop_bus_stop_name')
        // ]);

        // Scholarbusassign::create($request->post());
        return redirect()->route('teacherbusassign')->with('success',' has been created successfully.');
    }

    public function data_foredit_pickup(Request $request){
        $id = $request->get('number');
        $data = Teacherbusassign::select("*")->where('student_id_select_p',$id)->first();

        return json_encode($data);
    }
}
