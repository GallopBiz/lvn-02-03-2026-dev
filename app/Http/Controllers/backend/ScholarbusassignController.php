<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BusStaf;
use App\Models\CommanModel;
use App\Models\Inquiry_registration;
use App\Models\Student_registration;
use App\Models\Schedulemasterall;
use App\Models\Schedulemaster;
use App\Models\AddVehial;
use App\Models\Routemaster;
use App\Models\Routeareabusmaster;
use App\Models\Scholarbusassign;
use Illuminate\Support\Facades\Config;
use DB;

class ScholarbusassignController extends Controller
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
    public function index()
    {
        $schedulemasters = Schedulemaster::where('is_delete', 0)->get();
        // $datas = DB::table('student_registration')->select('class_name')->distinct()->get();
        $classlist = DB::table('classes')->select('class_name')->distinct()->get();
        // return view('backend.Transport.Scholarbusassign',compact('schedulemasters','datas'));
        return view('backend.Transport.Scholarbusassign', compact('schedulemasters', 'classlist'));
    }

    public function student_get(Request $request)
    {
        // print_r($request->all());exit;
        $fdate = $request->get('license_lssue');
        $date = $request->get('date');
        $class_name = $request->get('classname');
        // print_r($fdate);exit;
        $section_name = $request->get('section_name');
        $stor_data = [];
        $datas = DB::connection('dynamic')->table('student_registration')
        ->select('student_name', 'id', 'form_number', 'scholar_no' ,'json_str')
        ->where('class_name', $class_name)
        ->where('json_str->required_school_transport', 1)
        ->get();
    
        // echo"<pre>";print_r($datas);exit;
        // print_r($date);exit;
        foreach ($datas as $data) {
            $add_stu = json_decode($data->json_str);
            // $stu_add = $data->json_unquote(json_extract(`json_str`, '$."present_address"'));
            //    echo"<pre>"; print_r($add_stu->present_address);exit;
            // $try_stu_add = json_decode($stu_add);
            $arr = Scholarbusassign::select('*')->where('student_id_select_p', $data->id)->first();
            // echo"<pre>";print_r($add_stu->present_address);exit;
            if (!empty($arr->pick_shedule_name) || !empty($arr->studentaddcheck)) {
                $stu_check = 'disabled="true"';
            } else {
                $stu_check = '';
            }
            if (!empty($arr->studentaddcheck) || !empty($arr->pick_shedule_name)) {
                $stu_check_add = '';
            } else {
                $stu_check_add = 'disabled="true"';
            }
            //-------------------------------------------//
            //------------------------------------------//
            // if(empty($arr->pick_shedule_name)|| !empty($arr->studentaddcheck)){
            //     $stu_check_add = '';
            // }else{
            //     $stu_check_add = 'disabled="false"';
            // }
            // print_r($stu_check);

            $stor_data[] = [
                'id' => $data->id,
                'student_name' => $data->student_name,
                'student_check_value' => $add_stu->present_address,
                'stu_driver' =>  empty($add_stu->driver_name) ? '-' : $add_stu->driver_name,
                'student_add' => empty($arr->studentaddcheck) ? '-' : $arr->studentaddcheck,
                'form_number' => $data->form_number,
                'scholar_no' => $data->scholar_no,
                'pick_shedule_name' => empty($arr->pick_shedule_name) ? '-' : $arr->pick_shedule_name,
                'student_add_check' => empty($arr->studentaddcheck) ? '-' : $arr->studentaddcheck,
                'pick_up_routes' => empty($arr->pick_up_routes) ? '-' : $arr->pick_up_routes,
                'pickup_area_name' => empty($arr->pickup_area_name) ? '-' : $arr->pickup_area_name,
                'pickup_bus_stop_names' => empty($arr->pickup_bus_stop_names) ? '-' : $arr->pickup_bus_stop_names,
                'pickup_bus_no' => empty($arr->pickup_bus_no) ? '-' : $arr->pickup_bus_no,
                'drop_shedule_name' => empty($arr->drop_shedule_name) ? '-' : $arr->drop_shedule_name,
                'drop_up_route' => empty($arr->drop_up_route) ? '-' : $arr->drop_up_route,
                'drop_area_name' => empty($arr->drop_area_name) ? '-' : $arr->drop_area_name,
                'drop_bus_stop_name' => empty($arr->drop_bus_stop_name) ? '-' : $arr->drop_bus_stop_name,
                'pickup_bool' => $stu_check_add,
                'drop_bool' => empty($arr->drop_shedule_name) ? 'disabled="true"' : '',
                'pickup_bool_s' => $stu_check,
                'drop_bool_s' => empty($arr->drop_shedule_name) ? '' : 'disabled="true"'
            ];
            // $arr[] = $data->id;
        }
        // exit;
        // echo"<pre>";print_r($stor_data);exit;
        $schedulemasters = Schedulemaster::where('is_delete', 0)->get();
        // print_r($schedulemasters);exit;
        $datas = DB::connection('dynamic')->table('student_registration')->select('class_name')->distinct()->get();
        $classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        return view('backend.Transport.Scholarbusassign', compact('schedulemasters', 'datas', 'class_name', 'classlist', 'stor_data', 'fdate'));
        // return json_encode($stor_data);

    }

    public function create(Request $request)
    {
        BusStaf::create($request->post());
        return redirect()->route('driver-conductor-master')->with('success', ' has been created successfully.');
    }

    public function view($id)
    {
        $bus_stafs = BusStaf::whereId($id)->get();
        $busStafs = BusStaf::orderBy('id', 'desc')->paginate(5);
        return view('backend.Transport.BusStaff', compact('bus_stafs', 'busStafs'));
    }

    public function store(Request $request)
    {
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
        return redirect()->route('driver-conductor-master')->with('success', 'Bus Stop has been Updated successfully.');
    }

    public function delete($id)
    {
        $BusStaf = BusStaf::findOrFail($id);
        $BusStaf->delete();
        return redirect()->route('driver-conductor-master')->with('success', 'Bus Stop has been Deleted successfully.');
    }
    public function busstaff_delete(Request $request)
    {
        // print_r($request->all());
        // exit;
        $table = $request->table_name;
        $delete_resp = CommanModel::soft_delete($table, ['id' => $request->delete_id]);
        if ($delete_resp == 'TRUE') {
            return redirect()->back()->with('success', 'Record successfully removed');
        } elseif ($delete_resp == 'FALSE') {
            return redirect()->back()->with('error', 'Record not removed');
        }
    }

    public function scholarbusassign_student_shedule_namee(Request $request)
    {
        $shedule_name = $request->get('shedule_name');
        $data = Schedulemasterall::select("schedule_check_one")->where('schedule_name', $shedule_name)->get();
        $arr[] = ' -- Select Pick Route Name -- ';
        foreach ($data as $val) {
            $arrs = json_decode($val->schedule_check_one);
            foreach ($arrs as $arr1) {
                $arr[] = $arr1->route_name;
            }
        }

        if (!empty($arr)) {
            return json_encode(array_unique($arr));
        } else {
            return json_encode([" -- Select Pick Route Name -- "]);
        }
    }

    public function scholarbusassign_student_shedule_name_drop(Request $request)
    {
        $shedule_name = $request->get('shedule_name');
        $data = Schedulemasterall::select("schedule_check_one")->where('schedule_name', $shedule_name)->get();
        $arr[] = ' -- Select Pick Route Name -- ';
        foreach ($data as $val) {
            $arrs = json_decode($val->schedule_check_one);
            foreach ($arrs as $arr1) {
                $arr[] = $arr1->route_name;
            }
        }

        if (!empty($arr)) {
            return json_encode(array_unique($arr));
        } else {
            return json_encode([" -- Select Pick Route Name -- "]);
        }
    }

    public function scholarbusassign_student_pick_up_routes(Request $request)
    {
        $pickup_area_name = $request->get('pickup_area_name');
        $data = Routeareabusmaster::select("area_name")->where('route_name', $pickup_area_name)->get();
        $arr[] = ' -- Select Pick Area Name -- ';
        foreach ($data as $val) {
            $arr[] = $val->area_name;
        }

        if (!empty($arr)) {
            return json_encode(array_unique($arr));
        } else {
            return json_encode([" -- Select Pick Area Name -- "]);
        }
    }

    public function scholarbusassign_student_drop_routes(Request $request)
    {
        $pickup_area_name = $request->get('pickup_area_name');
        $data = Routeareabusmaster::select("area_name")->where('route_name', $pickup_area_name)->get();
        $arr[] = ' -- Select Pick Area Name -- ';
        foreach ($data as $val) {
            $arr[] = $val->area_name;
        }

        if (!empty($arr)) {
            return json_encode(array_unique($arr));
        } else {
            return json_encode([" -- Select Pick Area Name -- "]);
        }
    }

    public function scholarbusassign_student_bus_stop_name(Request $request)
    {
        $bus_stop_name = $request->get('bus_stop_name');
        $data = Routeareabusmaster::select("bus_stop_name")->where('area_name', $bus_stop_name)->get();
        $arr[] = ' -- Select Pick Up Bus Stop Name -- ';
        foreach ($data as $val) {
            $arr[] = $val->bus_stop_name;
        }

        if (!empty($arr)) {
            return json_encode(array_unique($arr));
        } else {
            return json_encode([" -- Select Pick Up Bus Stop Name -- "]);
        }
    }

    public function scholarbusassign_student_bus_stop_name_drop(Request $request)
    {
        $bus_stop_name = $request->get('bus_stop_name');
        $data = Routeareabusmaster::select("bus_stop_name")->where('area_name', $bus_stop_name)->get();
        $arr[] = ' -- Select Pick Up Bus Stop Name -- ';
        foreach ($data as $val) {
            $arr[] = $val->bus_stop_name;
        }

        if (!empty($arr)) {
            return json_encode(array_unique($arr));
        } else {
            return json_encode([" -- Select Pick Up Bus Stop Name -- "]);
        }
    }

    public function scholarbusassign_student_bus_no(Request $request)
    {
        $shedule_name = $request->get('shedule_name');
        $pick_up_routes = $request->get('pick_up_routes');

        $data = Schedulemasterall::select("schedule_check_one")->where('schedule_name', $shedule_name)->get();
        $arr[] = ' -- Select Pick Up Bus No -- ';
        foreach ($data as $val) {
            $arrs = json_decode($val->schedule_check_one);
            foreach ($arrs as $arr1) {
                if ($pick_up_routes == $arr1->route_name) {
                    $arr[] = $arr1->vehicelno;
                }
            }
        }

        if (!empty($arr)) {
            return json_encode(array_unique($arr));
        } else {
            return json_encode([" -- Select Pick Up Bus No -- "]);
        }
    }

    public function scholarbusassign_post_pickup(Request $request)
    {
        if (!empty($request->post('type_to_create'))) {
            $id = $request->post('student_id_select_p');
            Scholarbusassign::where('student_id_select_p', $id)->update([
                'pick_shedule_name' => $request->post('pick_shedule_name'),
                'pick_up_routes' => $request->post('pick_up_routes'),
                'pickup_area_name' => $request->post('pickup_area_name'),
                'pickup_bus_stop_names' => $request->post('pickup_bus_stop_names'),
                'pickup_bus_no' => $request->post('pickup_bus_no')
            ]);
        } else {
            Scholarbusassign::create($request->post());
        }
        return redirect()->route('student_get', ['class_name' => $request->post('class_name')])->with('success', ' has been created successfully.');
    }

    public function scholarbusassign_post_drop(Request $request)
    {

        $id = $request->post('student_id_select_d');

        if (!empty($request->post('type_to_create'))) {
            Scholarbusassign::where('student_id_select_p', $id)->update([
                'drop_shedule_name' => $request->post('drop_shedule_name'),
                'drop_up_route' => $request->post('drop_up_route'),
                'drop_area_name' => $request->post('drop_area_name'),
                'drop_bus_stop_name' => $request->post('drop_bus_stop_name')
            ]);
        } else {
            Scholarbusassign::where('student_id_select_p', $id)->update([
                'drop_shedule_name' => $request->post('drop_shedule_name'),
                'drop_up_route' => $request->post('drop_up_route'),
                'drop_area_name' => $request->post('drop_area_name'),
                'drop_bus_stop_name' => $request->post('drop_bus_stop_name')
            ]);
        }
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
        return redirect()->route('student_get', ['class_name' => $request->post('class_name')])->with('success', ' has been created successfully.');
    }

    public function data_foredit_pickup(Request $request)
    {
        $id = $request->get('number');
        $data = Scholarbusassign::select("*")->where('student_id_select_p', $id)->first();

        return json_encode($data);
    }
    public function data_studata(Request $request)
    {
        $driver_name = $request->get('stu_driver');
        $stu_bus_no = '';
        $bus =  DB::connection('dynamic')->table('schedulemasterall')->select('schedule_check_one')->where('schedule_check_one', 'like', '%' . $driver_name . '%')->get();
        foreach ($bus as $item) {
            $decodedData = json_decode($item->schedule_check_one, true);

            foreach ($decodedData as $key => $value) {
                if (isset($value['driver_name']) && $value['driver_name'] === $driver_name) {
                    $stu_bus_no =  $value['vehicelno'];
                }
            }
        }
        return json_encode($stu_bus_no);
    }
}
