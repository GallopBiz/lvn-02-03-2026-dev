<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use DB;
use Hash;
use App\Models\CommanModel;
use App\Models\Scholarbusassign;
use App\Models\Inquiry_registration;
use App\Models\Schedulemaster;
use App\Models\Inquiry;
use App\Models\Call;
use DataTables;
use App\Http\Requests\StoreFileRequest;
use Illuminate\Support\Facades\Config;
class FeesMasterStudentController extends Controller
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

    public function index(Request $request)
    {
        
        if ($request->ajax()) {
  
            $data = Inquiry::latest()->get();
  
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('action', function($row){
   
                        //    $btn = '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Edit" class="edit btn btn-primary btn-sm editProduct">Edit</a>';
                           $btn = '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Call" class="Call btn btn-primary btn-sm callStudent">Call</a>';
                           $btn = $btn.' <a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->mobile_number.'" data-original-title="View" class="btn btn-success btn-sm ViewStudent">View</a>';
    
                            return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }
         $classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        
        // return view('backend.inquiry');
        return view('backend.inquiry', compact('classlist',));
    }

    public function view(Request $request)
    {
        $mobile_number = $request->post('mobile_number');

        if ($request->ajax()) {
  
            $data = Call::select('call_note')->where('mobile_number',$mobile_number)->latest()->get();
  
            return $data;
            
        }
        
        return view('backend.inquiry');
    }

    public function store(Request $request)
    {
        Call::updateOrCreate([
                    'id' => $request->product_id],
                [
                    'mobile_number' => $request->mobile_number,
                    'call_tag' => $request->call_tag, 
                    'call_note' => $request->call_note
                ]);        
     
        return response()->json(['success'=>'Call saved successfully.']);
    }

    public function edit($id)
    {
        $product = Inquiry::find($id);
        return response()->json($product);
    }

    /*List All Inquiry*/
    public function student_feesmaster()
    {   
        //$all_inquiry = CommanModel::fetchDataArr('student_registration');
        $all_inquiry = DB::connection('dynamic')->table('student_registration')->where('is_archived', 0)->where('status','=','r')->orderBy('id', 'desc')->get() ; 
        // return view('backend.student_registrations.index',compact('all_inquiry'));
        $schedulemasters = Schedulemaster::where('is_delete', 0)->get();
        $classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();

        return view('backend.FeesTypeMaster.Feesmasterstudent', compact('all_inquiry','classlist','schedulemasters'));
    } 

    /*List All Inquiry*/
    public function save_student_registration(Request $request){   
        $postData = $request->all();
        // unset($postData['application_for']);
        // unset($postData['form_number']);
        // unset($postData['date_of_birth']);
        // unset($postData['class_name']);

        $files = [];

        // Birthcirtificate
        if($request->hasFile('birth_Certificate')){

            $birth_Certificate = 'birth_Certificate'.time().'.'.$request->file('birth_Certificate')->extension();

            $request->file('birth_Certificate')->move(public_path('uploads/admin/birth_Certificate'), $birth_Certificate);

            $files['birth_Certificate'] = $birth_Certificate; 
        }

        // transfer_certificate 
        if($request->hasFile('transfer_certificate')){

            $transfer_certificate = 'transfer_certificate'.time().'.'.$request->file('transfer_certificate')->extension();

            $request->file('transfer_certificate')->move(public_path('uploads/admin/transfer_certificate'), $transfer_certificate);

            $files['transfer_certificate'] = $transfer_certificate; 
        }

        // address_proof 
        if($request->hasFile('address_proof')){

            $address_proof = 'address_proof'.time().'.'.$request->file('address_proof')->extension();

            $request->file('address_proof')->move(public_path('uploads/admin/address_proof'), $address_proof);

            $files['address_proof'] = $address_proof; 
        }

        // last_report_card 
        if($request->hasFile('last_report_card')){

            $last_report_card = 'last_report_card'.time().'.'.$request->file('last_report_card')->extension();

            $request->file('last_report_card')->move(public_path('uploads/admin/last_report_card'), $last_report_card);

            $files['last_report_card'] = $last_report_card; 
        }
        
        $postData['files'] = $files;

        $postData['staff_name'] = $request->staff_name;
        // print_r($postData['staff_name']);exit;

        $jsonStr = json_encode($postData);

        // print_r($jsonStr);

        $insertArr  = [
            'application_for'=>$request->application_for,
            'form_number'=>$request->form_number,
            'date_of_birth'=>$request->student_dob,
            'class_name'=>$request->classname,
            'student_name'=>$request->studentname,
            // 'driver'=>$request->driver_name,
            'staff_name' => $request->staff_name[0],
    	    'scholar_no'=>$request->scholar_no,
            'session_name'=>$request->session_name,
            'password'=>Hash::make("LVN@123"),
           // 'student_name'=>$request->student_name,
            'inq_mode'=>'off',
            'status'=>'r',
            // 'json_str' => $jsonStr 
            'json_str'=>json_encode($postData)
        ];
        
        

        DB::connection('dynamic')->table('student_registration')->insert($insertArr); //CommanModel::insertData('student_registration',$insertArr);
        DB::connection('dynamic')->table('inquiry_registration')->where('id',$request->iid)->delete(); // $all_inquiry = CommanModel::deleteData('inquiry_registration',['id'=>$request->iid]);
        $uid = DB::connection('dynamic')->table('student_registration')->get()->last()->id;
        DB::connection('dynamic')->table('model_has_roles')->insert(['role_id' => 2,'model_id' => $uid]);
        //return redirect()->back()->with('success', 'Student successfully registred'); 
       //return view('backend.registrationviewlist',1301); 
       return redirect()->route('registrationviewlist',['id'=>$uid])->with('success', 'Student Successfully Registred With Scholar Number - ' .$request->scholar_no );
        //registrationviewlist/1301 
    
    }


    /*getDataByFormNumber*/
    public function getDataByFormNumber(Request $request)
    {           
        $form_number = $request->form_number;
       // $inquiry_data = CommanModel::getRowWhere('inquiry_registration',['form_number'=>$form_number])->orwhere('student_name'=>$form_number);
       $inquiry_data = DB::connection('dynamic')->table('inquiry_registration')
       ->select('*')
       ->where('status', '=', 'p')
       ->where('form_number', '=', $form_number)
       ->orWhere('student_name', '=', $form_number)
       ->orWhereJsonContains('json_str->fathername', $form_number)
       ->orWhereJsonContains('json_str->fathermobile', $form_number)
       ->orWhereJsonContains('json_str->mothermobile', $form_number)
       ->orWhereJsonContains('json_str->mothername', $form_number)
       ->first();   
        //  $inquiry_data = Inquiry_registration::select('*')->where('status', '=', 'p')->where("form_number","=", $form_number)->orwhere('student_name',"=",$form_number)->orwhereJsonContains('json_str->fathername',$form_number)->orwhereJsonContains('json_str->fathermobile',$form_number)->orwhereJsonContains('json_str->mothermobile',$form_number)->orwhereJsonContains('json_str->mothername',$form_number)->first();
         $da=json_decode($inquiry_data->json_str);
         $statename= $da->state;
         $statevalue = CommanModel::fetchDataWhere('states',['name'=>$statename]);
         foreach($statevalue as $v){ $statevalue= $v->id; }
         
        $passArr = ['inq_data'=>$inquiry_data,'inq_str_data'=>json_decode($inquiry_data->json_str),'statevalue'=>$statevalue];
 
        return $passArr;
    }

    public function getDataByFormNumberstudent_registration(Request $request)
    {           
        $form_number = $request->form_number;
       // $inquiry_data = CommanModel::getRowWhere('inquiry_registration',['form_number'=>$form_number])->orwhere('student_name'=>$form_number);
       
         $inquiry_data = Inquiry_registration::select('*')->where('status', '=', 'i')->where("form_number","=", $form_number)->orwhere('student_name',"=",$form_number)->orwhereJsonContains('json_str->fathername',$form_number)->orwhereJsonContains('json_str->fathermobile',$form_number)->orwhereJsonContains('json_str->mothermobile',$form_number)->orwhereJsonContains('json_str->mothername',$form_number)->first();
         $da=json_decode($inquiry_data->json_str);
         $statename= $da->state;
         $statevalue = CommanModel::fetchDataWhere('states',['name'=>$statename]);
         foreach($statevalue as $v){ $statevalue= $v->id; }
         
        $passArr = ['inq_data'=>$inquiry_data,'inq_str_data'=>json_decode($inquiry_data->json_str),'statevalue'=>$statevalue];
 
        return $passArr;
    }
    

    /*Edit Inquiry*/
    public function inquiry_edit($id)
    {   
        $inquiry_data = CommanModel::getRowWhere('inquiry_registration',['id'=>$id]);
        return view('backend.inquiry.edit',compact('inquiry_data'));
    }

    /*update Inquiry*/
    public function update_inquiry(Request $request)
    {   
        $update_id = $request->update_id;

        $dataForUpdate = [
            'application_for'=>$request->application_for,
            'date_of_birth'=>$request->dob,
            'class_name'=>$request->class_name,
            'student_name'=>$request->student_name,
            'session_name'=>$request->session_name,
            'phone_number'=>$request->phone_number,
            'mobile_number'=>$request->mobile_number,
        ];

        $inquiry_data = CommanModel::updateData('inquiry_registration',['id'=>$update_id],$dataForUpdate);

        return redirect('inquiry-data-show');
    }

    /*Add registration*/
    public function add_student_registrations(){
        /*r status for registration*/
        // $inqArr = CommanModel::fetchDataWhere('inquiry_registration',['save_status'=>'Form Selected'],['status'=>'i']);
        $inqArr = DB::connection('dynamic')->table('inquiry_registration')->where('save_status','=','Form Selected')->where('status','=','i')->get();

        // $inqArr = CommanModel::fetchDataWhere('student_registration',['status'=>'r']);
        $stutdentsArr = DB::connection('dynamic')->table('student_registration')->where('is_archived', 0)->get(); //CommanModel::fetchDataArr('student_registration');
        $classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        $drivername = DB::connection('dynamic')->table('busstaff')->where('role', 'Driver')->get();
        // print_r($drivername);exit;
        // return view('backend.student_registrations.add',compact('inqArr','stutdentsArr'));
     $uid = DB::connection('dynamic')->table('student_registration')->where('is_archived', 0)->get()->last()->id;
        $uid = $uid +1;
        return view('backend.student_registrations.add', compact('inqArr','drivername','stutdentsArr','classlist','uid'));
    }

    public function selection_process(){
       // $all_inquiry = CommanModel::fetchDataArr('inquiry_registration');
         $all_inquiry = DB::connection('dynamic')->table('inquiry_registration')->orderBy('id', 'desc')->where("status","=", "i")->where("next_year","=",null)->get();
        
         $totalPending = Inquiry_registration::select('*')->where("status","=", "i")->whereJsonContains('json_str->folloupdate_status',"Pending")->get();
         $totalCancel = Inquiry_registration::select('*')->where("status","=", "i")->whereJsonContains('json_str->folloupdate_status',"Cancel")->get();
         $totaladmission = DB::connection('dynamic')->table('student_registration')->where('is_archived', 0)->where("status","=", "r")->get();
        return view('backend.student_registrations.selection_process',compact('all_inquiry','totalPending','totalCancel','totaladmission'));
    }

    public function save_selection_process(Request $request){
        $id = $request->id;
        $dataForUpdate = [
            'save_status'=>$request->save_status,
        ];
        $inquiry_data = CommanModel::updateData('inquiry_registration',['id'=>$id],$dataForUpdate);
        return response()->json(['success'=>'saved successfully.']);
    }

    public function scholarbusassign_post_pickup_fees(Request $request)
    {
        // print_r($request->post());exit;
        if (!empty($request->post('type_to_create'))) {
            $id = $request->post('student_id_select_p');
            //Scholarbusassign::where('student_id_select_p', $id)->update([
            DB::connection('dynamic')->table('scholarbusassign')->where('student_id_select_p', $id)->update([
                'pick_shedule_name' => $request->post('pick_shedule_name'),
                'pick_up_routes' => $request->post('pick_up_routes'),
                'pickup_area_name' => $request->post('pickup_area_name'),
                'pickup_bus_stop_names' => $request->post('pickup_bus_stop_names'),
                'pickup_bus_no' => $request->post('pickup_bus_no')
            ]);
        } else {
            $data = [
                // "class_name" => $request->post('class_name'),
                // "studentaddcheck" => $request->post('studentaddcheck'),
                // "latLngInput" => $request->post('latLngInput'),
                "pickup_bus_no" => $request->post('pickup_bus_no'),
                "student_id_select_p" => $request->post('student_id_select_p'),
            ];
            DB::connection('dynamic')->table('scholarbusassign')->insert($data);//Scholarbusassign::create($request->post());
        }
        return response()->json(['success'=>'saved successfully.']);
    }
    public function filter_student_registration(Request $request){
        $session_name = $request->post('session_name');
        $form_number = $request->post('form_number');
        $save_status = $request->post('save_status');
        $class_name = $request->post('classname');
        $gender = $request->post('gender');
        $studentname = $request->post('student_name');
        $jsonArr = [
            'session_name'=>$request->post('session_name'),
             'studentname'=>$request->post('student_name'),
            'save_status'=> $request->post('save_status'),
            'gender'=> $request->post('gender'),
            'class_name'=>$request->post('classname'),
            'form_number'=>$request->post('form_number'),
            
        ];

        // print_r($jsonArr);exit;


        // Total records
        // $records = Inquiry::select('*');
        ## Add custom filter conditions || !empty($class_name) || !empty($gender) || !empty($studentname)
        if(!empty($session_name)){
            $records1 = DB::connection('dynamic')->table('inquiry_registration')//::select('*')
            ->where("session_name","=", $session_name)->orderBy('id', 'desc');
            }
        if(!empty($form_number)){
            $records1->where("form_number","=", $form_number)->orderBy('id', 'desc');
        }
        if(!empty($save_status)){
            $records1->where("save_status","=", $save_status)->orderBy('id', 'desc');
        }
        if(!empty($class_name)){
            $records1->where("class_name","=", $class_name)->orderBy('id', 'desc');
        }
        if(!empty($gender)){
            $records1->where("gender","=", $gender)->orderBy('id', 'desc');
        }
        if(!empty($studentname)){
            $records1->where("student_name","=", $studentname)->orderBy('id', 'desc');
        }
        $all_inquiry = $records1->get();
        // print_r($all_inquiry);die();
        $classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        return view('backend.student_registrations.selection_process',compact('all_inquiry','jsonArr', 'classlist'));
    }

    
    // public function student_fees_filer(Request $request){
    //     $session_name = $request->post('session_name');
    //     $form_number = $request->post('form_number');
    //     $save_status = $request->post('save_status');
    //     $class_name = $request->post('class');
    //     $studentname = $request->post('student_name');
       
    //     $jsonArr = [
    //         'session_name'=>$request->post('session_name'),
    //          'student_name'=>$request->post('student_name'),
    //         'serial_number'=> $request->post('serial_number'),
    //         'class_name'=>$request->post('classname'),
    //         'form_number'=>$request->post('form_number'),
            
    //     ];
    //     // echo"<pre>";print_r($jsonArr);exit;

    //     // Total records
    //     // $records = Inquiry::select('*');
    //     ## Add custom filter conditions || !empty($class_name) || !empty($gender) || !empty($studentname)
    //     if(!empty($session_name)){
    //         $records1 = DB::connection('dynamic')->table('student_registration')->where("session_name","=", $session_name)->orderBy('id', 'desc');
    //     }
        
    //     if(!empty($form_number)){
    //         $records1->where("form_number","=", $form_number)->orderBy('id', 'desc');
    //     }
    //     if(!empty($save_status)){
    //         $records1->where("save_status","=", $save_status)->orderBy('id', 'desc');
    //     }
    //     if(!empty($class_name)){
    //         $records1->where("class_name","=", $class_name)->orderBy('id', 'desc');
    //     }
    //     if(!empty($studentname)){
    //         $records1->where("student_name","=", $studentname)->orderBy('id', 'desc');
    //     }
    //     $all_inquiry = $records1->get();
    //     $classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
    //     return view('backend.FeesTypeMaster.Feesmasterstudent',compact('all_inquiry','jsonArr','classlist'));
    // }

    public function student_fees_filer(Request $request){
        $session_name = $request->post('session_name');
        $form_number = $request->post('form_number');
        $save_status = $request->post('save_status');
        $class_name = $request->post('classname');
        $studentname = $request->post('student_name');
       
        $jsonArr = [
            'session_name'=>$request->post('session_name'),
            'student_name'=>$request->post('student_name'),
            'serial_number'=> $request->post('serial_number'),
            'class_name'=>$request->post('classname'),
            'form_number'=>$request->post('form_number'),
        ];
    
        // Initialize $records1
        $records1 = DB::connection('dynamic')->table('student_registration')->where('is_archived', 0)->orderBy('id', 'desc');
    
        // Add custom filter conditions
        if(!empty($session_name)){
            $records1->where("session_name", "=", $session_name);
        }
        
        if(!empty($form_number)){
            $records1->where("form_number", "=", $form_number);
        }
    
        if(!empty($save_status)){
            $records1->where("save_status", "=", $save_status);
        }
    
        if(!empty($class_name)){
            $records1->where("class_name", "=", $class_name);
        }
    
        if(!empty($studentname)){
            $records1->where("student_name", "=", $studentname);
        }
		
		$all_inquiry = $records1->get();
        $classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        
        return view('backend.FeesTypeMaster.Feesmasterstudent', compact('all_inquiry', 'jsonArr', 'classlist'));
    }
    
    public function advance_nextyear()
    {
        $nextfees = DB::connection('dynamic')->table('abvance_nextyear_fees')->where('deleted_at','=',0)->get();
        return view('backend.FeesTypeMaster.advance_nextyear', compact('nextfees'));
    }
    
    public function next_year_amount_add(Request $request){
        if (!empty($request->id)) {
            // Update existing record
            DB::connection('dynamic')->table('abvance_nextyear_fees')
                ->where('id', $request->id)
                ->update(['amount' => $request->amount]);
        } else {
            // Create new record
            DB::connection('dynamic')->table('abvance_nextyear_fees')->insert($request->except('_token', 'id'));
        }

        return redirect()->action([FeesMasterStudentController::class, 'advance_nextyear'])
        ->with('success', 'Amount has been saved successfully.');
    }

    // view_next_year_amount
    public function view_next_year_amount($id){
        $nextfees_s = DB::connection('dynamic')->table('abvance_nextyear_fees')->where('id','=',$id)->get();//Areamaster::whereId($id)->get();
        $nextfees = DB::connection('dynamic')->table('abvance_nextyear_fees')->get();//Areamaster::orderBy('id','desc')->get();
        return view('backend.FeesTypeMaster.advance_nextyear',compact('nextfees_s','nextfees'));
    }

    public function delete_next_year_amount($id){
        // echo $id;
        $a = explode('-',$id);
        $b = $a[1];        
        $c = $a[0];
        $delete_resp = CommanModel::soft_delete_at($c,['id'=>$b]);
        if($delete_resp=='TRUE'){
            return redirect()->back()->with('success', 'Record successfully removed');
        }elseif($delete_resp=='FALSE'){
            return redirect()->back()->with('error', 'Record not removed');
        }
    }
}
