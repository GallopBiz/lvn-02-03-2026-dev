<?php
namespace App\Http\Controllers\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use App\Models\Course_fees_head_master;
use App\Models\Holds_structure_row;
use DB;
use Hash;
use App\Models\CommanModel;
use App\Models\Student_registration;
use App\Models\Scholarbusassign;
use App\Models\Inquiry;
use App\Models\Call;
use App\Models\State;    
use App\Models\City; 
use App\Models\Inquiry_registration;
use App\Models\Late_fees_master;
use App\Models\HrmsEmployee;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;

// use Illuminate\Support\Facades\DB
//use Request;
use DataTables;

class InquiryEntryController extends Controller
{
    public function __construct(Request $request)
    {
        $dynamicConfig = Config::get('database.connections.dynamic', []);
        $db_name = $request->input('session')
            ?? $request->input('session_name')
            ?? ($_COOKIE['selectedYear'] ?? session('db_names') ?? ($dynamicConfig['database'] ?? env('DB_DATABASE')));
        $dynamicConnectionName = 'dynamic';
		$dynamicConfig['database'] = $db_name;
        Config::set('database.connections.dynamic', $dynamicConfig);
        DB::reconnect('dynamic');
    }

    public function save_class_name()
    {
        $datas = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        // print_r($data['duachart1']);die();
        return view('backend.FeesDue-chart.preinquiryregistration.blade', compact('datas'));
    }


    public function save_student_inquiryentry(Request $request){
       
        $result = [];
            $demoIndex = 1;
            if(!empty($request->siblings_namesecond)){
            for ($i = 0; $i < count($request->siblings_namesecond); $i++) {
                $demoData = [
                    "siblings_namesecond" => (!is_null(($request->siblings_namesecond)))?$request->siblings_namesecond [$i] : '',
                    "sibling_class_second" => (!is_null(($request->sibling_class_second))) ? $request->sibling_class_second[$i] : '',
                     "siblings_section_second" => (!is_null(($request->siblings_section_second ))) ? $request->  siblings_section_second [$i] : '',
                     "siblings_bod_second" => (!is_null(($request->siblings_bod_second  ))) ? $request-> siblings_bod_second[$i]: '',
                    // "term" => $decode_json["term"][$i]

                 'assign_calling' => $request->assign_calling,

                ];
                $result["Demo" . $demoIndex] = $demoData;
                $demoIndex++;
            }

            $data2['siblings_details'] = $result;
        }else{
            $data2['siblings_details'] = null;
            //print_r($data2);
        }
  
        if(!empty($request->caste)){            
            $cast = $request->caste;
        }else{
            $cast = "null";
        }
        $castename = DB::connection('dynamic')->table('caste_name')->where('caste_name',$request->caste)->first();
           if(empty($castename)){
                $insertArr2 = [  
                'caste_name'=> $request->caste,           
                ];
        
            DB::connection('dynamic')->table('caste_name')->insert($insertArr2);

           }
        $presentlyschool = DB::connection('dynamic')->table('presentlyschool')->where('school_name',$request->presentlyschool)->first();
        if(empty($presentlyschool)){
             $insertArr2 = [  
             'school_name'=> $request->presentlyschool,           
             ];
     
         DB::connection('dynamic')->table('presentlyschool')->insert($insertArr2);

        }
        $statename = DB::connection('dynamic')->table('states')->where('id',$request->state)->get();
            foreach($statename as $state){
                $statename = $state->name;
            } 
        $jsonArr = [
            'enquiryno'=>$request->enquiryno,
            'formno'=>$request->formno,
            'enquirydate'=>$request->enquirydate,
            'studentname_prefix'=>$request->studentname_prefix,
            'student_name'=>$request->studentname,
            'student_dob'=>$request->student_dob,
            'fathername_prefix'=>$request->fathername_prefix,
            'fathername'=>$request->fathername,
            'fathermobile'=>$request->fathermobile,
            'class_name'=>$request->classname,
            'searchfather'=>$request->searchfather,
            'fatheroccupation'=>$request->fatheroccupation,
            'mothername_prefix'=>$request->mothername_prefix,
            'mothername'=>$request->mothername,
            'mothermobile'=>$request->mothermobile,
            'motheroccupation'=>$request->motheroccupation,
            'gender'=>$request->gender,
            'admission_type'=>$request->admission_type,
            'email'=>$request->email,
            //'remarks'=>$request->remark,
            'remarkstatus'=>$request->remarkstatus,
            'address'=>$request->address,
            'city'=>$request->city,
            'pincode'=>$request->pincode,
            'state'=> $statename,
            'religion'=>$request->religion,
            'caste'=>$cast,
            'category'=>$request->category,
            'received_amount'=>$request->received_amount,
            'reference_number'=>$request->reference_number,
            'presentlyclass'=>$request->presentlyclass,
            'presentlyschool'=>$request->presentlyschool,
            'hear_aboutus'=>$request->hear_aboutus,
            'follow_up_date'=>$request->follow_up_date,
            'iid'=>$request->iid,
            'inter_dt'=>'',
            'Adm'=>'',
            'folloupdate_status'=>'Pending', 
            'visited'=>'0',
            'followup_Cancel'=>'0',
            'Follows'=>'',
            'Response'=>'',
            'Counseller'=>'',
            'Priority'=>'',
            'followup_remark'=>'',
            'assign_calling'=>'',
            'enquiry_through'=>$request->enquiry_through,
            'siblings_details'=>$data2,
            'amount' => $request->amount,
         'assign_calling' => $request->assign_calling,


           ];
             $jsonStr = json_encode($jsonArr);

        $insertArr = [
            'application_for'=>$request->admission_type,
            'form_number'=>$request->formno,
            'date_of_birth'=>$request->student_dob,
            'class_name'=>$request->classname,
            'section_name' => (!empty($request->section_name) ? $request->section_name : ""),
            'student_name'=>$request->studentname,
            'gender'=>$request->gender,
            'assign_calling' => $request->assign_calling,
            'session_name'=>$request->session,
            'json_str'=> $jsonStr,   
             //'phone_number'=>$request->phone_number,
            'mobile_number'=>$request->fathermobile,
            'inq_mode'=>'on',
            'status'=>'i'            
        ];        
        
        if(!empty($request->searchenquiryno)){ 
            // CommanModel::updateData('inquiry_registration',['id'=>$request->iid],$insertArr);
            DB::connection('dynamic')->table('inquiry_registration')
                ->where('id', $request->iid)
                ->update($insertArr);
         }else{ 
            DB::connection('dynamic')->table('inquiry_registration')->insert($insertArr);
         } 
        // echo "Record inserted successfully.<br/>";
// die();
        //$comman_model->insertData('inquiry_registration',$insertArr);
          return redirect('admin-enquiryform')
                        ->with('success','Record inserted successfully');

        //return redirect('admin-enquiryform');
    }

    public function adminenquirylist(Request $request)
    {
        // Fetch all inquiries from the 'dynamic' database where 'is_delete' is 0 and 'status' is 'i'
        $all_inquiry = DB::connection('dynamic')->table('inquiry_registration')
                        ->where('is_delete', 0)
                        ->where('status', 'i')
                        ->when($request->has('fromdate') && $request->has('todate'), function ($query) use ($request) {
                            $query->whereBetween('enquirydate', [$request->input('fromdate'), $request->input('todate')]);
                        })
                        ->orderByDesc('created_at')
                        ->orderByDesc('id')
                        ->get();

        // Fetch total pending student registrations from the 'dynamic' database
        $totalPending = DB::connection('dynamic')->table('inquiry_registration')
                            ->select('*')
                            ->where('status', '=', 'i')
                            ->whereJsonContains('json_str->folloupdate_status', 'Pending')
                            ->get();

        // Fetch total canceled student registrations from the 'dynamic' database
        $totalCancel = DB::connection('dynamic')->table('inquiry_registration')
                            ->select('*')
                            ->where('status', '=', 'i')
                            ->whereJsonContains('json_str->folloupdate_status', 'Cancel')
                            ->get();

        // Fetch total admitted student registrations from the 'dynamic' database
        $totaladmission = DB::connection('dynamic')->table('inquiry_registration')
                                ->where('status', '=', 'r')
                                ->get();

        // Fetch distinct class names for filtering
        $classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();

        return view('backend.admininquirylist', compact('all_inquiry', 'classlist', 'totalPending', 'totalCancel', 'totaladmission'));
    }
    


    public function adminpreenquiryfeeslist()
    {   
		$this->syncPreviousNextYearInquiries();
		$currentSchoolYear = session('db_names');
	
        $enquirecount = DB::connection('dynamic')->table('inquiry_registration')->where('status','i')->where('session_name', $currentSchoolYear)->count();
        $all_inquiry = DB::connection('dynamic')->table('inquiry_registration')->where('status','i')->orderByDesc('created_at')->orderByDesc('id')->get(); 
		 //$totalPending = Inquiry_registration::select('*')->where("status","=", "i")->whereJsonContains('json_str->folloupdate_status',"Pending")->get();
		 $totalPending = DB::connection('dynamic')->table('inquiry_registration')
                        ->select('*')
                        ->where('status', '=', 'i')
                        ->whereJsonContains('json_str->folloupdate_status', 'Pending')
                        ->get();

        //  $totalCancel = Inquiry_registration::select('*')->where("status","=", "i")->whereJsonContains('json_str->folloupdate_status',"Cancel")->get();
        $totalCancel = DB::connection('dynamic')->table('inquiry_registration')
                        ->select('*')
                        ->where('status', '=', 'i')
                        ->whereJsonContains('json_str->folloupdate_status', 'Cancel')
                        ->get();
 
        $totaladmission = DB::connection('dynamic')->table('inquiry_registration')->where("status","=", "r")->get();

        //return view('backend.student_registrations.index',compact('all_inquiry'));
           $classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        // return view('backend.adminpreinquirylist', compact('all_inquiry','classlist'));

           return view('backend.adminpreinquiryfeeslist',compact('all_inquiry','classlist','totalPending','totalCancel','enquirecount','totaladmission'));
    }
    public function followupdate(Request $request)
    {   
         //$all_inquiry = CommanModel::fetchDataWhere('inquiry_registration',['status'=>'i']);
         $all_inquiry = DB::connection('dynamic')->table('inquiry_registration')->where('status','i')->orderByDesc('created_at')->orderByDesc('id')->get(); //CommanModel::fetchDataWhere('inquiry_registration',['status'=>'i']);
         $date = date("Y-m-d");
         $todatfollowu = DB::connection('dynamic')->table('inquiry_registration')
         ->where('status','i')
         ->whereJsonContains('json_str->follow_up_date',$date)->get();
        //  $todatfollowu = Inquiry_registration::select('*')->where("status","=", "i")->whereJsonContains('json_str->follow_up_date',$date)->get();
         $totalPending = DB::connection('dynamic')->table('inquiry_registration')
         ->where('status','i')
         ->whereJsonContains('json_str->folloupdate_status','Pending')->get();
        //  $totalPending = Inquiry_registration::select('*')->where("status","=", "i")->whereJsonContains('json_str->folloupdate_status',"Pending")->get();
        //  $totalCancel = Inquiry_registration::select('*')->where("status","=", "i")->whereJsonContains('json_str->folloupdate_status',"Cancel")->get();
         $totalCancel = DB::connection('dynamic')->table('inquiry_registration')
         ->where('status','r')
         ->whereJsonContains('json_str->folloupdate_status','Cancel');
         $totaladmission = DB::connection('dynamic')->table('inquiry_registration')->where("status","=", "r")->get();
         //print_r($todatfollowu); 
        //return view('backend.student_registrations.index',compact('all_inquiry'));
        // echo '<pre>';print_r($all_inquiry);die();
        return view('backend.followupdate',compact('all_inquiry','todatfollowu','totalPending','totalCancel','totaladmission'));
    }
    public function duestuamount()

    {
        // echo"hyy";exit;   
        //  //$all_inquiry = CommanModel::fetchDataWhere('inquiry_registration',['status'=>'i']);
        $classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        $all_inquiry = DB::connection('dynamic')
                        ->table('totalnextyear')
                        ->join('student_registration', 'totalnextyear.scholar_no', '=', 'student_registration.scholar_no')
                        ->select('totalnextyear.scholar_no', 'totalnextyear.fees_date', 'totalnextyear.due_date', 'totalnextyear.totalnextyear','totalnextyear.received_type','totalnextyear.receipt_number', 'student_registration.student_name')
                        ->groupBy('totalnextyear.scholar_no', 'totalnextyear.receipt_number')
                        ->get();

        return view('backend.dueamountstu',compact('all_inquiry','classlist'));
    }

    public function followupdateedit($id)
    {
        $all_inquiry = DB::connection('dynamic')->table('inquiry_registration')->where('id',$id)->get();//CommanModel::fetchDataWhere('inquiry_registration',['id'=> $id]);
        //return view('backend.student_registrations.index',compact('all_inquiry'));
        return view('backend.edit_followupdate',compact('all_inquiry'));
    }


     public function filter_followup(Request $request){
        if($request->post('todayfollow')){
            $fromdate = $request->post('fromdate');
            $todate = $request->post('todate');
            // $all_inquiry = DB::connection('dynamic')->table('inquiry_registration')->where('id',$id)->get();//CommanModel::fetchDataWhere('inquiry_registration',['id'=> $id]);
            $records1  = DB::connection('dynamic')->table('inquiry_registration')
            ->select('*')
            ->where('status', '=', 'i')
            ->whereJsonContains('json_str->follow_up_date', $request->post('todayfollow'));
            //Inquiry_registration::select('*')->where("status","=", "i")->whereJsonContains('json_str->follow_up_date',$request->post('todayfollow')); 
        }else{
            $fromdate = $request->post('fromdate');
            $todate = $request->post('todate');
            if(!empty($fromdate)){
                $records1 = DB::connection('dynamic')->table('inquiry_registration')->whereBetween('created_at', [$fromdate,$todate]); 
                //('created_at', [$from.' 00:00:00',$to.' 23:59:59'])->get();
            }
        }
        $all_inquiry = $records1->get();
        return view('backend.filter_result_followup',compact('all_inquiry','fromdate','todate'));
    }



    public function filter_duestuamount(Request $request){
        // Get request parameters
        $class_name = $request->post('classname');
        $student_name = $request->post('student_name');
        $fromdate = $request->post('fromdate');
        $reciptno = $request->post('receipt_number');
        $todate = $request->post('todate');
        $jsonArr = [
             'student_name'=>$request->post('student_name'),
            'fromdate'=> $request->post('fromdate'),
            'receipt_number'=> $request->post('receipt_number'),
            'todate'=> $request->post('todate'),
            'classname'=>$request->post('classname'),
            
        ];
        $EnqSecdata[] = [
            'class_name' => $class_name,
            'reciptno' => $reciptno,
            'student_name' => $student_name
        ];
    
        // Get distinct class names for dropdown
        $classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
    
        // Build the query
        $all_inquiry = DB::connection('dynamic')
        ->table('totalnextyear')
        ->join('student_registration', 'totalnextyear.scholar_no', '=', 'student_registration.scholar_no')
        ->select('totalnextyear.scholar_no', 'totalnextyear.fees_date', 'totalnextyear.due_date', 'totalnextyear.totalnextyear', 'totalnextyear.received_type', 'totalnextyear.receipt_number', 'totalnextyear.created_at', 'student_registration.student_name' , 'student_registration.scholar_no')
        ->when($class_name, function ($query) use ($class_name) {
            return $query->where('student_registration.class_name', $class_name);
        })
        ->when($student_name, function ($query) use ($student_name) {
            return $query->where('student_registration.student_name', 'LIKE', '%' . $student_name . '%');    //, 'student_registration.scholar_no'
        })
        ->when($fromdate && $todate, function ($query) use ($fromdate, $todate) {
            return $query->whereBetween('totalnextyear.created_at', [$fromdate, $todate]);
        })
        ->when($reciptno, function ($query) use ($reciptno) {
            return $query->where('totalnextyear.receipt_number', $reciptno);
        })
        ->groupBy('totalnextyear.scholar_no')
        ->get();
    // echo"<pre>"; print_r($all_inquiry);exit;
        // Return the view with the filtered data
        return view('backend.filter_result_dueamountstu', compact('all_inquiry', 'fromdate', 'todate', 'classlist','student_name','jsonArr','EnqSecdata'));
    }
    
    // public function show($id)
    // {
    //      $user = DB::connection('dynamic')->table('')->where('id',$id)->first();// follow::find($id);
    //     // return view('users.show',compact('user'));
    // }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['caste'] = DB::connection('dynamic')->table('caste_name')->orderByRaw('LOWER(TRIM(caste_name)) ASC')->get();
        $data['all_inquiry2'] = DB::connection('dynamic')->table('inquiry_registration')->where('status','i')->get();// CommanModel::fetchDataWhere('inquiry_registration',['status'=> "i"]);
        $data['all_inquiry'] = DB::connection('dynamic')->table('inquiry_registration')->where('id',$id)->get();// CommanModel::fetchDataWhere('inquiry_registration',['id'=> $id]);
        $data['states'] = DB::connection('dynamic')->table('states')->where('name',$id)->get();// State::get(["name", "id"]);
        //return view('backend.student_registrations.index',compact('all_inquiry'));
        return view('backend.edit_followupdate',$data);
         
    }

    public function savefollowupdate(Request $request)
    { 
        $result = [];
            $demoIndex = 1;
            if(!empty($request->siblings_namesecond) && !empty($request->sibling_class_second) && !empty($request->siblings_section_second) && !empty($request->sibling_class_second)){
            for ($i = 0; $i < count($request->siblings_bod_second); $i++) {
                $demoData = [
                    "siblings_namesecond" => $request->siblings_namesecond[$i],
                    "sibling_class_second" => $request->sibling_class_second[$i],
                     "siblings_section_second" => $request->siblings_section_second[$i],
                     "siblings_bod_second" => $request->siblings_bod_second[$i],
                    // "term" => $decode_json["term"][$i]
                ];
                $result["Demo" . $demoIndex] = $demoData;
                $demoIndex++;
            }

            $data2['siblings_details'] = $result;
        }else{
            $data2['siblings_details'] = null;
            //print_r($data2);
        }

        $statename = DB::connection('dynamic')->table('states')->where('id',$request->state)->get();//CommanModel::fetchDataWhere('states',['id'=>$request->state]);
            foreach($statename as $state){
                $statename = $state->name;
            } 

            if(!empty($request->nextfollowup)){
                $nextfollowup=$request->nextfollowup;
            }else{
                $nextfollowup=$request->followupdate;
            }
        $jsonstr=$request->jsonstr;       

        $data = json_decode($jsonstr, true);
        $followupno1 = ($request->followupno + 1) ;
            
        foreach($data as $x => $val){
           
        if($x == 'follow_up_date'){  echo $data['follow_up_date'] = date("d-m-Y", strtotime($request->nextfollowup));  
          }

            $data['folloupdate_status'] = $request->adm;
            $data['visited']=$request->followupno + 1 ;
         
            $data['Follows']=$request->followupby;
            $data['Response']=$request->response;
           
            $data['followup_remark']=$request->remark;
            $data['assign_calling']=$request->assigncalling;
            }
            $cast = $request->caste;
        $jsonArr = [
             'enquiryno'=>$request->enquiryno2,
             'formno'=>$request->formno,
            'enquirydate'=>$request->enquirydate,
            'studentname_prefix'=>$request->studentname_prefix,
            'student_name'=>$request->studentname,
            'student_dob'=>$request->student_dob,
            'fathername_prefix'=>$request->fathername_prefix,
            'fathername'=>$request->fathername,
            'fathermobile'=>$request->fathermobile,
            'fatheroccupation'=>$request->fatheroccupation,
            'mothername_prefix'=>$request->mothername_prefix,
            'mothername'=>$request->mothername,
            'mothermobile'=>$request->mothermobile,
            'motheroccupation'=>$request->motheroccupation,
            'gender'=>$request->gender,
            'admission_type'=>$request->admission_type,
            'email'=>$request->email,
            'address'=>$request->address,
            'city'=>$request->city,
            'pincode'=>$request->pincode,
            'state'=> $statename,
            'religion'=>$request->religion,
            'caste'=>$cast,
            'category'=>$request->category,
            'received_amount'=>$request->received_amount,
            'presentlyclass'=>$request->presentlyclass,
            'presentlyschool'=>$request->presentlyschool,
            'hear_aboutus'=>$request->hear_aboutus,
            'follow_up_date'=>$nextfollowup,
            'inter_dt'=>'',
            'Adm'=>'',
            'folloupdate_status'=>$request->adm, 
            'remarks'=>$request->remarkf,
            'remarkstatus'=>$request->remarkstatus,
            'visited'=>$request->followupno + 1 ,
            'followup_Cancel'=>'0',
            'Follows'=>$request->followupby,
            // 'Response'=>$request->response,
            'Counseller'=>'',
            'Priority'=>'',
            'followup_remark'=>$request->remark,
            'assign_calling'=>$request->assigncalling,
            'enquiry_through'=>$request->enquiry_through,
            'siblings_details'=>$data2,
            // 'siblings_name'=>$request->siblings_name,
            // 'sibling_class'=>$request->sibling_class,
            // 'siblings_school'=>$request->siblings_school,
            // 'siblings_bod'=>$request->siblings_bod,
            // 'siblings_namesecond'=>$request->siblings_namesecond,
            // 'sibling_class_second'=>$request->sibling_class_second,
            // 'siblings_school_second'=>$request->siblings_school_second,
            // 'siblings_bod_second'=>$request->siblings_bod_second,
            // 'headname'=>$request->headname,
            // 'chq_cash'=>$request->chq_cash,
            // 'back_ac'=>$request->back_ac,
            // 'non_stu'=>$request->non_stu,
           ];
             $jsonStr = json_encode($jsonArr);

        $insertArr = [
            'application_for'=>$request->admission_type,
            'form_number'=>$request->formno,
            'date_of_birth'=>$request->student_dob,
            'class_name'=>$request->classname,
            'student_name'=>$request->studentname,
            'gender'=>$request->gender,
            'session_name'=>$request->session,
            'json_str'=> $jsonStr,
             //'phone_number'=>$request->phone_number,
            'mobile_number'=>$request->fathermobile,
            'inq_mode'=>'on',
            'status'=>'i'            
        ];

        //$data[] = $extra;
        // $newJsonString = json_encode($data);
        //  $insertArr = [
        //     'json_str'=> $newJsonString,
        //     ];

        $inquiry_data = DB::connection('dynamic')->table('inquiry_registration')
                        ->where('id',$request->id)
                        ->update($insertArr); //CommanModel::updateData('inquiry_registration',['id'=>$request->id],$insertArr);
        return redirect()->route('followupdate')->with('success','Follow Up Date has been Updated successfully.');
    }

    public function save_followup_status(Request $request){
        $id = $request->id;
        $jsonStr = $request->jsonstr;
        $cancel = $request->cancelRemark;
        $data = json_decode($jsonStr, true);
               
        foreach($data as $x => $val){
            $data['folloupdate_status'] = $request->save_status;
        }  
        $newJsonString = json_encode($data);
  
        $insertArr = [
            'json_str'=> $newJsonString,
            ];  
        // print_r($insertArr);die();
        $inquiry_data = DB::connection('dynamic')->table('inquiry_registration')
                        ->where('id', $id)
                        ->update($insertArr);//CommanModel::updateData('inquiry_registration',['id'=>$id],$insertArr);
        return response()->json(['success'=>'saved successfully.']);
    }

    public function master_registration_view()
    {   
        
        return view('backend.masterregistration_view');
    }

    public function enquiryeditlist($id)
    {
        $data['states'] = DB::connection('dynamic')->table('states')->get();//State::get(["name", "id"]);
        $data['caste'] = DB::connection('dynamic')->table('caste_name')->orderByRaw('LOWER(TRIM(caste_name)) ASC')->get();
        $data['all_inquiry'] = DB::connection('dynamic')->table('inquiry_registration')->where('id',$id)->get();
        $data['all_inquiry2'] = DB::connection('dynamic')->table('inquiry_registration')->get();
        //return view('backend.student_registrations.index',compact('all_inquiry'));
        return view('backend.edit_enquiry',$data); 
    }

    public function enquiryviewlist($id)
    {
        $data['states'] = DB::connection('dynamic')->table('states')->where('name','id')->get();//State::get(["name", "id"]);
        $data['all_inquiry'] = DB::connection('dynamic')->table('inquiry_registration')->where('id',$id)->get();
        $data['classlist'] = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        //return view('backend.student_registrations.index',compact('all_inquiry'));
        return view('backend.enquiryview',$data); 
    }

    public function scholars_profile($id){
		$data['notificationData1']="";
        $sessionId = session('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d');
        if($id == $sessionId){
            $data['all_inquiry'] = DB::connection('dynamic')->table('inquiry_registration')->where('id',$id)->get();//CommanModel::fetchDataWhere('inquiry_registration',['id'=> $id]);
            $data['id'] = $id;
            return view('backend.enquiryview',$data); 
        } else {
            $data['id'] = $id;
            return view('backend.enquiryview', $data); 
        }
    }

    // public function filter_allenquiry(Request $request){
    //     $session_name = $request->post('session_name');
    //     $form_number = $request->post('form_number');
    //     $class_name = $request->post('classname');
    //     $studentname = $request->post('student_name');
    //     $studentclasses = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();

    //     $EnqSecdata[] = [
    //         'session_name' => $session_name,
    //         'class_name' => $class_name,
    //         'form_number' => $form_number,
    //         'student_name' => $studentname
    //     ];
    //     // print_r($EnqSecdata);exit;
    //     $jsonArr = [
    //         'session_name'=>$request->post('session_name'),
    //          'student_name'=>$request->post('student_name'),
    //         'formno'=> $request->post('form_number'),
    //         'class'=>$request->post('class'),
            
    //     ];
       
    //     if(!empty($session_name)){
    //         // $records1 = DB::connection('dynamic')->table('inquiry_registration')->where(['status' => 'i'],["session_name"=> $session_name])->orderBy('id', 'desc') ; 
    //         $response = DB::table('inquiry_registration')
    //         ->select('inquiry_registration.*', 'student_registration.scholar_no')
    //         ->join('student_registration', 'student_registration.form_number', '=', 'inquiry_registration.form_number');
    //         // ->get();
        
    //         $records1 = $response;
        
            
    //     }
    //     if(!empty($form_number)){
    //         $records1->where("inquiry_registration.form_number","=", $form_number);
    //     }
    //     if(!empty($class_name)){
    //         $records1->where("inquiry_registration.class_name","=", $class_name);
    //     }
    //     if(!empty($studentname)){
    //         $records1->where("inquiry_registration.student_name",'LIKE','%'.$studentname.'%');
    //     }

    //     $all_inquiry = $records1->get();
    //     //$all_inquiry = $records1->get();

    //     // $all_inquiry2 = DB::connection('dynamic')->table('inquiry_registration')->get();

    //     $response2 = DB::table('inquiry_registration')
    //     ->select('inquiry_registration.*', 'student_registration.scholar_no')
    //     ->join('student_registration', 'student_registration.form_number', '=', 'inquiry_registration.form_number')
    //     ->get();
    
    //     $all_inquiry2 = $response2;



    //     return view('backend.enquiryfilter',compact('all_inquiry','jsonArr','studentclasses','EnqSecdata','all_inquiry2'));
    // }


    public function filter_allenquiry(Request $request){
        $session_name = $request->post('session_name');
        $form_number = $request->post('form_number');
        $class_name = $request->post('classname');
        $studentname = $request->post('student_name');
        
        $studentclasses = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
    
        $EnqSecdata[] = [
            'session_name' => $session_name,
            'class_name' => $class_name,
            'form_number' => $form_number,
            'student_name' => $studentname
        ];
    
        $jsonArr = [
            'session_name' => $request->post('session_name'),
            'student_name' => $request->post('student_name'),
            'formno' => $request->post('form_number'),
            'class' => $request->post('class'),
        ];
    
        $response = DB::table('inquiry_registration')
            ->select('inquiry_registration.*');
           
        // Apply filters based on user inputs
        if (!empty($session_name)) {
            $response->where('inquiry_registration.session_name', $session_name);
        }
    
        if (!empty($form_number)) {
            $response->where('inquiry_registration.form_number', $form_number);
        }
    
        if (!empty($class_name)) {
            $response->where('inquiry_registration.class_name', $class_name);
        }
    
        if (!empty($studentname)) {
            $response->where('inquiry_registration.student_name', 'LIKE', '%' . $studentname . '%');
        }
    
        // Fetch filtered data
		//echo $response->toSql(); die;
        $all_inquiry = $response->orderByDesc('created_at')->orderByDesc('id')->get();
    
        // Fetch all data (for comparison or alternative view)
        $response2 = DB::connection('dynamic')->table('inquiry_registration')
            ->select('inquiry_registration.*')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    
        $all_inquiry2 = $response2;
    
        return view('backend.enquiryfilter', compact('all_inquiry', 'jsonArr', 'studentclasses', 'EnqSecdata', 'all_inquiry2'));
    }
    
    
    public function saveenquiry(Request $request)
    {  //print_r($request->all()); die();
         $result = [];
            $demoIndex = 1;
            if(!empty($request->coupon_question)){
            if(!empty($request->siblings_namesecond && $request->sibling_class_second && $request->siblings_section_second && $request->siblings_bod_second)){
            for ($i = 0; $i < count($request->siblings_namesecond); $i++) {
                $demoData = [
                    "siblings_namesecond" => $request->siblings_namesecond[$i],
                    "sibling_class_second" => $request->sibling_class_second[$i],
                     "siblings_section_second" => $request->siblings_section_second[$i],
                     "siblings_bod_second" => $request->siblings_bod_second[$i],
                    // "term" => $decode_json["term"][$i]
                ];
                $result["Demo" . $demoIndex] = $demoData;
                $demoIndex++;
            }

            $data2['siblings_details'] = $result;
        }}else{
            $data2['siblings_details'] = null;
            //print_r($data2);
        } 
        if(!empty($request->caste)){
            
            $cast = $request->caste;
        }
        $castename = DB::connection('dynamic')->table('caste_name')->where('caste_name',$request->caste)->get();//CommanModel::fetchDataWhere('caste_name',['caste_name'=>$request->caste]);
           if(empty($castename)){
                $insertArr2 = [  
                'caste_name'=> $request->caste,           
                ];
        
            DB::connection('dynamic')->table('caste_name')->insert($insertArr2);

           }

           $statename = DB::connection('dynamic')->table('states')->where('id',$request->state)->get();//CommanModel::fetchDataWhere('states',['id'=>$request->state]);
            foreach($statename as $state){
                $statename = $state->name;
            } 
        
        $jsonArr = [
            'enquiryno'=>$request->enquiryno2,
            'formno'=>$request->formno,
            'enquirydate'=>$request->enquirydate,
            'studentname_prefix'=>$request->studentname_prefix,
            'student_name'=>$request->studentname,
            'student_dob'=>$request->student_dob,
            'fathername_prefix'=>$request->fathername_prefix,
            'fathername'=>$request->fathername,
            'fathermobile'=>$request->fathermobile,
            'fatheroccupation'=>$request->fatheroccupation,
            'mothername_prefix'=>$request->mothername_prefix,
            'mothername'=>$request->mothername,
            'mothermobile'=>$request->mothermobile,
            'motheroccupation'=>$request->motheroccupation,
            'gender'=>$request->gender,
            'admission_type'=>$request->admission_type,
            'email'=>$request->email,
            'remarks'=>$request->remark,
            'remarkstatus'=>$request->remarkstatus,
            'address'=>$request->address,
            'city'=>$request->city,
            'pincode'=>$request->pincode,
            'state'=>$statename,
            'religion'=>$request->religion,
            'caste'=>$request->caste,
            'category'=>$request->category,
            'searchfather'=>$request->searchfather,
            'reference_number'=>$request->reference_number,
            'received_amount'=>$request->received_amount,
            'presentlyclass'=>$request->presentlyclass,
            'presentlyschool'=>$request->presentlyschool,
            'hear_aboutus'=>$request->hear_aboutus,
            'follow_up_date'=>$request->follow_up_date,
            'inter_dt'=>'',
            'Adm'=>'',
            'folloupdate_status'=>'Pending', 
            'visited'=>'0',
            'followup_Cancel'=>'0',
            'Follows'=>'',
            'Response'=>'',
            'Counseller'=>'',
            'Priority'=>'',
            'followup_remark'=>'',
            'assign_calling'=>'',
            'enquiry_through'=>$request->enquiry_through,
            'siblings_details'=>$data2,
           ];
             $jsonStr = json_encode($jsonArr);

        $insertArr = [
            'application_for'=>$request->admission_type,
            'form_number'=>$request->formno,
            'date_of_birth'=>$request->student_dob,
            'class_name'=>$request->class,
            'student_name'=>$request->studentname,
            'session_name'=>$request->session_name,
            'json_str'=> $jsonStr,
             //'phone_number'=>$request->phone_number,
            'mobile_number'=>$request->fathermobile,
            'inq_mode'=>'on',
            'status'=>'i',
            'next_year' => (empty($request->next_year) ? 0 : 1 ),
        ];
//print_r($insertArr);

        // $inquiry_data = CommanModel::updateData('inquiry_registration',['id'=>$request->iid],$insertArr);
        // print_r($inquiry_data);
        DB::connection('dynamic')->table('inquiry_registration')->where('id',$request->idd)->update($insertArr);
         
        return redirect()->route('adminenquirylist')->with('success','Enquiry has been Updated successfully.');
    }

    
    public function adminpreenquiryform(){
        // $all_inquiry = CommanModel::fetchDataWhere('inquiry_registration',['status'=>'p']);
        $all_inquiry = DB::connection('dynamic')->table('inquiry_registration')
            ->where('status', 'p')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
        $studentclasses = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        $classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();

        
    //     $response = DB::table('inquiry_registration')
    // ->select('inquiry_registration.*', 'student_registration.scholar_no')
    // ->join('student_registration', 'student_registration.form_number', '=', 'inquiry_registration.form_number')
    // ->get();

    // $all_inquiry = $response;

        //return view('backend.student_registrations.index',compact('all_inquiry'));
        // return view('backend.adminpreinquirylist',compact('all_inquiry'));
        return view('backend.adminpreinquirylist', compact('all_inquiry','studentclasses','classlist'));        
    }

    public function filter_preenquiry(Request $request){
        $session_name = $request->post('session_name');
        $class_name = $request->post('classname');
        $studentname = $request->post('student_name');
        $fromdate = $request->post('fromdate');
        $todate = $request->post('todate');
        // die();
        // $Userid = $data['Userid'];
        // $states = $data['states'];
        // $classlist2 = DB::table('class_name')->select('class_name')->distinct()->get();
        $jsonArr = [
            'session_name'=>$request->post('session_name'),
             'student_name'=>$request->post('student_name'),
            'fromdate'=> $request->post('fromdate'),
            'todate'=> $request->post('todate'),
            'class'=>$request->post('class'),
            
        ];

    
        $studentclasses = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        $classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();


        $EnqSecdata[] = [
            'session_name' => $session_name,
            'class_name' => $class_name,
            'studentname' => $studentname,
            'fromdate' => $fromdate,
            'todate' => $todate
        ];
        // $records1 = DB::connection('dynamic')->table('inquiry_registration')->where(['status' => 'p']);

        // return $records1;
        
        $response = DB::connection('dynamic')->table('inquiry_registration')
    ->select('inquiry_registration.*', 'student_registration.scholar_no')
    ->join('student_registration', 'student_registration.form_number', '=', 'inquiry_registration.form_number')
    ->where('inquiry_registration.status', 'p');
    // ->get();

    $records1 = $response;


        if (!empty($session_name)) {
            $records1->where('inquiry_registration.session_name', $session_name);
        }
        
        if (!empty($class_name)) {
            $records1->where('inquiry_registration.class_name', $class_name);
        }
        
        if (!empty($studentname)) {
            $records1->where('inquiry_registration.student_name', 'LIKE', '%' . $studentname . '%');
        }
        
        if (!empty($fromdate)) {
            $records1->whereBetween('inquiry_registration.created_at', [$fromdate, $todate]);
        }
        
        $all_inquiry = $records1
            ->orderByDesc('inquiry_registration.created_at')
            ->orderByDesc('inquiry_registration.id')
            ->get();
        $EnqSecdata = $all_inquiry;

    // $all_inquiry = $records1;


        return view('backend.preenquiryfilter',compact('all_inquiry','jsonArr','studentclasses','EnqSecdata', 'classlist'));

        // return view('backend.preenquiryfilter', compact('all_inquiry','jsonArr','classlist2'));

    }
    
    /**
     * Get adjacent session databases (previous and next) relative to the active database.
     */
    private function getAdjacentSessionDatabases(): array
    {
        $currentDb = Config::get('database.connections.dynamic.database')
            ?: ($_COOKIE['selectedYear'] ?? session('db_names') ?? env('DB_DATABASE'));

        $prevDb = null;
        $nextDb = null;

        if (is_string($currentDb) && preg_match('/^(\d{4})_(\d{4})$/', $currentDb, $matches)) {
            $startYear = (int)$matches[1];
            $endYear = (int)$matches[2];
            $prevDbCand = ($startYear - 1) . '_' . ($endYear - 1);
            $nextDbCand = ($startYear + 1) . '_' . ($endYear + 1);

            try {
                $checkPrev = DB::connection('dynamic')->select("SHOW TABLES FROM `{$prevDbCand}` LIKE 'inquiry_registration'");
                if (!empty($checkPrev)) {
                    $prevDb = $prevDbCand;
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $prevDb = null;
            }

            try {
                $checkNext = DB::connection('dynamic')->select("SHOW TABLES FROM `{$nextDbCand}` LIKE 'inquiry_registration'");
                if (!empty($checkNext)) {
                    $nextDb = $nextDbCand;
                }
            } catch (\Illuminate\Database\QueryException $e) {
                $nextDb = null;
            }
        }

        return [
            'current' => $currentDb,
            'prev'    => $prevDb,
            'next'    => $nextDb
        ];
    }

    private function recentAcademicSessionDatabases(): array
    {
        $currentDb = Config::get('database.connections.dynamic.database')
            ?: ($_COOKIE['selectedYear'] ?? session('db_names') ?? env('DB_DATABASE'));

        if (!is_string($currentDb) || !preg_match('/^(\d{4})_(\d{4})$/', $currentDb, $matches)) {
            return [];
        }

        $startYear = (int) $matches[1];

        return array_map(function ($yearsAgo) use ($startYear) {
            $sessionStart = $startYear - $yearsAgo;

            return sprintf('%04d_%04d', $sessionStart, $sessionStart + 1);
        }, range(0, 2));
    }

    private function resolvePreEnquirySession(Request $request): string
    {
        $dynamicConfig = Config::get('database.connections.dynamic', []);
        $sessionName = session('db_names')
            ?? ($_COOKIE['selectedYear'] ?? null)
            ?? $request->input('session')
            ?? $request->input('session_name')
            ?? ($dynamicConfig['database'] ?? env('DB_DATABASE'));

        if (!$sessionName) {
            abort(503, 'The active academic session is not configured.');
        }

        if (($dynamicConfig['driver'] ?? null) !== 'sqlite'
            && $sessionName !== ($dynamicConfig['database'] ?? null)) {
            $dynamicConfig['database'] = $sessionName;
            Config::set('database.connections.dynamic', $dynamicConfig);
            DB::purge('dynamic');
        }

        if (($dynamicConfig['driver'] ?? null) !== 'sqlite'
            && !Schema::connection('dynamic')->hasTable('inquiry_registration')) {
            abort(503, "The academic session database {$sessionName} is missing the inquiry_registration table.");
        }

        return $sessionName;
    }

    private function nextYearEnquiriesAreOpen(): bool
    {
        $today = \Carbon\Carbon::now();

        return $today->month >= 11 || $today->month <= 3;
    }

    /**
     * Synchronize next-year enquiries from previous session into current session if missing.
     */
    private function syncPreviousNextYearInquiries(): void
    {
        $sessions = $this->getAdjacentSessionDatabases();
        $currentDb = $sessions['current'];
        $prevDb = $sessions['prev'];

        if (!$prevDb) {
            return;
        }

        try {
            $prevRecords = DB::connection('dynamic')->table("{$prevDb}.inquiry_registration")
                ->where(function($q) {
                    $q->where('next_year', 1)->orWhere('next_year', '1');
                })
                ->where(function($q) {
                    $q->where('is_delete', 0)->orWhereNull('is_delete');
                })
                ->get();

            foreach ($prevRecords as $record) {
                $exists = DB::connection('dynamic')->table('inquiry_registration')
                    ->where('form_number', $record->form_number)
                    ->where(function($q) use ($record) {
                        $q->where('student_name', $record->student_name)
                          ->orWhere('mobile_number', $record->mobile_number);
                    })
                    ->exists();

                if (!$exists) {
                    $data = (array)$record;
                    unset($data['id']);
                    $data['session_name'] = $currentDb;
                    $data['next_year'] = 0;
                    DB::connection('dynamic')->table('inquiry_registration')->insert($data);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error syncing previous next-year inquiries: ' . $e->getMessage());
        }
    }

    /**
     * Calculate current and next-year sequential enquiry numbers.
     */
    private function calculateEnquiryNumbers(): array
    {
        $sessions = $this->getAdjacentSessionDatabases();
        $prevDb = $sessions['prev'];
        $nextDb = $sessions['next'];

        // Enquiry numbers are stored in json_str; form_number is a separate form number.
        $currentEnquiryMax = $this->maxEnquiryNumber(
            DB::connection('dynamic')->table('inquiry_registration')
            ->where(function($q) {
                $q->whereNull('next_year')
                  ->orWhere('next_year', 0)
                  ->orWhere('next_year', '0')
                  ->orWhere('next_year', '');
            })
            ->where(function($q) {
                $q->where('is_delete', 0)->orWhereNull('is_delete');
            })
        );

        $previousEnquiryMax = 0;
        if ($prevDb) {
            try {
                $previousEnquiryMax = $this->maxEnquiryNumber(
                    DB::connection('dynamic')->table("{$prevDb}.inquiry_registration")
                    ->where(function($q) {
                        $q->where('next_year', 1)->orWhere('next_year', '1');
                    })
                    ->where(function($q) {
                        $q->where('is_delete', 0)->orWhereNull('is_delete');
                    })
                );
            } catch (\Illuminate\Database\QueryException $e) {
                $previousEnquiryMax = 0;
            }
        }

        $currentEnquiryNo = max($currentEnquiryMax, $previousEnquiryMax) + 1;

        $currentNextYearEnquiryMax = $this->maxEnquiryNumber(
            DB::connection('dynamic')->table('inquiry_registration')
            ->where(function($q) {
                $q->where('next_year', 1)->orWhere('next_year', '1');
            })
            ->where(function($q) {
                $q->where('is_delete', 0)->orWhereNull('is_delete');
            })
        );

        $nextYearEnquiryNo = $currentNextYearEnquiryMax + 1;

        // Keep the existing form-number sequence independent from enquiry numbering.
        $currentFormMax = DB::connection('dynamic')->table('inquiry_registration')
            ->where(function($q) {
                $q->whereNull('next_year')
                  ->orWhere('next_year', 0)
                  ->orWhere('next_year', '0')
                  ->orWhere('next_year', '');
            })
            ->where(function($q) {
                $q->where('is_delete', 0)->orWhereNull('is_delete');
            })
            ->selectRaw('MAX(CAST(form_number AS UNSIGNED)) as max_num')
            ->value('max_num') ?: 0;

        $previousFormMax = 0;
        if ($prevDb) {
            try {
                $previousFormMax = DB::connection('dynamic')->table("{$prevDb}.inquiry_registration")
                    ->where(function($q) {
                        $q->where('next_year', 1)->orWhere('next_year', '1');
                    })
                    ->where(function($q) {
                        $q->where('is_delete', 0)->orWhereNull('is_delete');
                    })
                    ->selectRaw('MAX(CAST(form_number AS UNSIGNED)) as max_num')
                    ->value('max_num') ?: 0;
            } catch (\Illuminate\Database\QueryException $e) {
                $previousFormMax = 0;
            }
        }

        $currentNextYearFormMax = DB::connection('dynamic')->table('inquiry_registration')
            ->where(function($q) {
                $q->where('next_year', 1)->orWhere('next_year', '1');
            })
            ->where(function($q) {
                $q->where('is_delete', 0)->orWhereNull('is_delete');
            })
            ->selectRaw('MAX(CAST(form_number AS UNSIGNED)) as max_num')
            ->value('max_num') ?: 0;

        $nextSessionFormMax = 0;
        if ($nextDb) {
            try {
                $nextSessionFormMax = DB::connection('dynamic')->table("{$nextDb}.inquiry_registration")
                    ->where(function($q) {
                        $q->where('is_delete', 0)->orWhereNull('is_delete');
                    })
                    ->where(function($q) {
                        $q->whereNull('next_year')
                          ->orWhere('next_year', 0)
                          ->orWhere('next_year', '0')
                          ->orWhere('next_year', '');
                    })
                    ->where(function($q) use ($nextDb) {
                        $q->where('session_name', $nextDb)
                          ->orWhereNull('session_name')
                          ->orWhere('session_name', '');
                    })
                    ->selectRaw('MAX(CAST(form_number AS UNSIGNED)) as max_num')
                    ->value('max_num') ?: 0;
            } catch (\Illuminate\Database\QueryException $e) {
                $nextSessionFormMax = 0;
            }
        }

        return [
            'currentEnquiryNo' => $currentEnquiryNo,
            'nextYearEnquiryNo' => $nextYearEnquiryNo,
            'currentFormNo' => max((int) $currentFormMax, (int) $previousFormMax) + 1,
            'nextYearFormNo' => max((int) $currentNextYearFormMax, (int) $nextSessionFormMax) + 1,
        ];
    }

    private function maxEnquiryNumber(\Illuminate\Database\Query\Builder $query): int
    {
        $max = 0;

        foreach ($query->select('json_str')->get() as $record) {
            $data = json_decode($record->json_str ?? '', true);
            $number = $data['enquiryno'] ?? null;

            if ((is_int($number) || is_string($number))
                && preg_match('/^\d+$/', trim((string) $number))) {
                $max = max($max, (int) $number);
            }
        }

        return $max;
    }

    public function admin_preenquiryform()
    {   
            $currentSession = $this->resolvePreEnquirySession(request());
            $this->syncPreviousNextYearInquiries();
        $numbers = $this->calculateEnquiryNumbers();
        $currentEnquiryNo = $numbers['currentEnquiryNo'];
        $nextYearEnquiryNo = $numbers['nextYearEnquiryNo'];
        $currentFormNo = $numbers['currentFormNo'];
        $nextYearFormNo = $numbers['nextYearFormNo'];

        $data['states'] = DB::connection('dynamic')->table('states')->select(['name','id'])->get();
        $Userid = $currentEnquiryNo;
        $data['Userid'] = $Userid;
        $data['currentFormNo'] = $currentFormNo;
        $data['currentEnquiryNo'] = $currentEnquiryNo;
        $data['nextYearEnquiryNo'] = $nextYearEnquiryNo;
        $data['currentSession'] = $currentSession;
        $canEnquireNextYear = $this->nextYearEnquiriesAreOpen();
        $states = $data['states'];
        $classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        
        return view('backend.preinquiryregistration', compact('classlist','data','Userid', 'states', 'currentEnquiryNo', 'nextYearEnquiryNo', 'currentFormNo', 'nextYearFormNo', 'canEnquireNextYear'));
	}

		public function get_next_enquiry_number(Request $request)
    {
		    $this->resolvePreEnquirySession($request);
		    $this->syncPreviousNextYearInquiries();
		    $isNextYear = $this->nextYearEnquiriesAreOpen()
		        && ($request->input('next_year') == 1 || $request->input('next_year') === 'true' || $request->input('next_year') === '1');

        $numbers = $this->calculateEnquiryNumbers();
        $enquiryNo = $isNextYear ? $numbers['nextYearEnquiryNo'] : $numbers['currentEnquiryNo'];

        return response()->json([
            'status' => true,
            'enquiry_no' => $enquiryNo,
            'next_year' => $isNextYear ? 1 : 0
        ]);
    }
    
    public function save_student_preinquiryentry(Request $request)
{   
    $sessionName = $this->resolvePreEnquirySession($request);
    $statename = DB::connection('dynamic')->table('states')->where('id', $request->state)->first()->name ?? '';

    // Format names properly
    $studentName = $this->formatFullName($request->studentname);
    $fatherName = $this->formatFullName($request->fathername);
    $motherName = $this->formatFullName($request->mothername);
	
	$isNextYear = $this->nextYearEnquiriesAreOpen() && !empty($request->next_year) ? 1 : 0;

        $this->syncPreviousNextYearInquiries();
        $numbers = $this->calculateEnquiryNumbers();
        $enquiryNumber = $isNextYear ? $numbers['nextYearEnquiryNo'] : $numbers['currentEnquiryNo'];
        $formNumber = $request->input('formno') ?: $numbers['currentFormNo'];
	
    $jsonArr = [
        'enquiryno'         => $enquiryNumber,
        'formno'            => $formNumber,
        'enquirydate'       => $request->enquirydate,
        'date_of_birth'     => $request->date_of_birth,
        'studentname_prefix'=> $request->studentname_prefix,
        'class_name'        => $request->classname,
        'student_name'      => $studentName,
        'student_dob'       => $request->date_of_birth,
        'fathername_prefix' => $request->fathername_prefix,
        'fathername'        => $fatherName,
        'fathermobile'      => $request->fathermobile,
        'mothername_prefix' => $request->mothername_prefix,
        'mothername'        => $motherName,
        'mothermobile'      => $request->mothermobile,
        'gender'            => $request->gender,
        'admission_type'    => $request->admission_type,
        'address'           => $request->address,
        'city'              => $request->city,
        'pincode'           => $request->pincode,
        'state'             => $statename,
    ];

    $jsonStr = json_encode($jsonArr);

    $insertArr = [
        'application_for'   => $request->admission_type,
        'form_number'       => $formNumber,
        'date_of_birth'     => $request->date_of_birth,
        'class_name'        => $request->classname,
        'student_name'      => $studentName,
        'session_name'      => $sessionName,
        'json_str'          => $jsonStr,
        'gender'            => $request->gender,
        'mobile_number'     => $request->fathermobile,
        'inq_mode'          => 'on',
        'next_year'         => $isNextYear,
        'status'            => 'p'
    ];

    DB::connection('dynamic')->table('inquiry_registration')->insert($insertArr);

    return redirect()->route('admin-preenquiryform', ['session' => $sessionName])
            ->with('success', 'Record inserted successfully');
}


    public function preenquiryviewlist($id)
    {
        $data['states'] =  DB::connection('dynamic')->table('states')->select(['name', 'id'])->get();//State::get();
        $data['all_inquiry'] = DB::connection('dynamic')->table('inquiry_registration')->where('id',$id)->get();//CommanModel::fetchDataWhere('inquiry_registration',['id'=> $id]);
        $data['classlist'] = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        //return view('backend.student_registrations.index',compact('all_inquiry'));
        return view('backend.preenquiryview',$data); 
    }

    public function registrationviewlist($id)
    {
        $data['id']=$id;
        $dynamicConnectionName = 'dynamic';
        $dynamicConfig = Config::get("database.connections.{$dynamicConnectionName}");
        
        $data['course_fees_head_master'] = DB::connection('dynamic')->table('course_fees_head_master')->where('is_delete',0)->get();//Course_fees_head_master::where('is_delete',0)->get();
        $data['class_names'] = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        $data['holds_structure_row'] = DB::connection('dynamic')->table('holds_structure_row')->get(); //Holds_structure_row::all();
        $data['fees_types'] = DB::connection('dynamic')->table('fees_types_master')->select('fees_type')->where('is_delete',0)->get();
        $data['terms'] = DB::connection('dynamic')->table('terms')->where('is_delete',0)->get();
        $data['states'] = DB::connection('dynamic')->table('states');//State::get(["name", "id"]);
        $data['all_inquiry'] = DB::connection('dynamic')->table('student_registration')->where('id',$id)->get();//CommanModel::fetchDataWhere('student_registration',['id'=> $id]);
        $data['Vehicallist'] = DB::connection('dynamic')->table('generate_duechartstatus')->select('*')->where('student_id',$id)->get();
        $data['late_fees_master'] = DB::connection('dynamic')->table('late_fees_master')->where('id',1)->first(); //Late_fees_master::where('id',1)->first();
        $data['drivername'] = DB::connection('dynamic')->table('busstaff')->where('role', 'Driver')->get();
        $data['checkBus'] = DB::connection('dynamic')->table('student_registration')->select('json_str')->where('id',$id)->get();

        // print_r($data['checkBus']);exit;
        //return view('backend.student_registrations.index',compact('all_inquiry'));
        return view('backend.student_registrations.registrationview',$data); 
    }
    public function feesmasterviewlist($id)
    {
        $data['id']=$id;
        $data['course_fees_head_master'] = DB::connection('dynamic')->table('course_fees_head_master')->where('is_delete',0)->get();
        //Course_fees_head_master::where('is_delete',0)->get();
        $data['class_names'] = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        $data['holds_structure_row'] = DB::connection('dynamic')->table('holds_structure_row')->get(); //Holds_structure_row::all();
        $data['fees_types'] = DB::connection('dynamic')->table('fees_types_master')->select('fees_type')->where('is_delete',0)->get();
        $data['terms'] = DB::connection('dynamic')->table('terms')->where('is_delete',0)->get();
        $data['states'] = DB::connection('dynamic')->table('states')->get();//State::get(["name", "id"]);
        $data['all_inquiry'] = DB::connection('dynamic')->table('student_registration')->where('id',$id)->get();
		$data['student_fees_exempted'] = [];
		$data['next_year_fee_receipts'] = [];
		$data['next_year_fee_rows'] = [];

		foreach ($data['all_inquiry'] as $each_inq) {
			$scholar_no = $each_inq->scholar_no;

			$exempted = DB::connection('dynamic')
				->table('student_fees_exempted')
				->where('scholar_no', $scholar_no)
				->first();

			$data['student_fees_exempted'][$scholar_no] = $exempted;

			$data['next_year_fee_receipts'][$scholar_no] = DB::connection('dynamic')
				->table('totalnextyear')
				->where('scholar_no', $scholar_no)
				->whereNotNull('receipt_number')
				->pluck('receipt_number')
				->unique()
				->values();

			if (!\Illuminate\Support\Facades\Schema::connection('dynamic')->hasColumn('totalnextyear', 'payment_date')) {
				\Illuminate\Support\Facades\Schema::connection('dynamic')->table('totalnextyear', function ($table) {
					$table->string('payment_date', 155)->nullable()->after('due_date');
				});
			}

			$data['next_year_fee_rows'][$scholar_no] = DB::connection('dynamic')
				->table('totalnextyear')
				->where('scholar_no', $scholar_no)
				->orderBy('id')
				->get([
					'fees_date',
					'due_date',
					'payment_date',
					'account_name',
					'fees',
					'totalnextyear',
					'received_type',
					'reference_number',
					'receipt_number',
				]);
		}
		//CommanModel::fetchDataWhere('student_registration',['id'=> $id]);
        $data['generateDueChartStatus'] = DB::connection('dynamic')->table('generate_duechartstatus')->select('*')->where('student_id',$id)->get();
        $data['late_fees_master'] = DB::connection('dynamic')->table('late_fees_master')->where('id',1)->first(); //Late_fees_master::where('id',1)->first();
        $data['drivername'] = HrmsEmployee::query()
            ->selectRaw("id, first_name, last_name, TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) as ename")
            ->whereHas('position', function ($query) {
                $query->whereRaw('LOWER(position_name) = ?', ['driver']);
            })
            ->where('employee_status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
        $data['schedulemasters'] = DB::connection('dynamic')->table('schedulemaster')->where('is_delete', 0)->get();
        $data['busfeesamount'] = DB::connection('dynamic')->table('busfees')->select('amount')->where('is_delete',0)->get();
        // print_r($data['busfeesamount']);exit;
        // $schedulemasters = Schedulemaster::where('is_delete', 0)->get();
        $stor_data = [];
        $datas = DB::connection('dynamic')->table('student_registration')->select('student_name', 'id', 'form_number', 'json_str')->where('id',$id)->get();
        // $datas = Student_registration::select('student_name', 'id', 'form_number', 'json_str')->where('id',$id)->get();
        // echo"<pre>";print_r($datas);exit;
        foreach ($datas as $data1) {
            $add_stu = json_decode($data1->json_str);
            // $stu_add = $data->json_unquote(json_extract(`json_str`, '$."present_address"'));
            //    echo"<pre>"; print_r($add_stu->present_address);exit;
            // $try_stu_add = json_decode($stu_add);
            $arr = Scholarbusassign::select('*')->where('student_id_select_p', $data1->id)->first();
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

            $stor_data[] = [
                'id' => $data1->id,
                'student_name' => $data1->student_name,
                'student_check_value' => $add_stu->present_address,
                'stu_driver' =>  empty($add_stu->driver_name) ? '-' : $add_stu->driver_name,
                'student_add' => empty($arr->studentaddcheck) ? '-' : $arr->studentaddcheck,
                'form_number' => $data1->form_number,
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

        $data['stor_dat'] = $stor_data[0];
        // echo"<pre>";print_r($data['stor_dat']);
        $data['checkBus'] = DB::connection('dynamic')->table('student_registration')->select('json_str')->where('id',$id)->get();
// exit;
        // echo"<pre>"; print_r($data);exit;
        //return view('backend.student_registrations.index',compact('all_inquiry'));
        return view('backend.student_registrations.feesmasterview',$data); 
    }
    
    public function registrationeditlist($id)
    {        
        $data['inqArr'] = DB::connection('dynamic')->table('inquiry_registration')->where('save_status','Form Selected')->get(); 
        $data['stutdentsArr'] = DB::connection('dynamic')->table('student_registration')->get(); 
        $data['all_inquiry'] = DB::connection('dynamic')->table('student_registration')->where('id',$id)->get(); 
		if ($data['all_inquiry']->isEmpty()) {
            foreach ($this->recentAcademicSessionDatabases() as $candidateDb) {
                try {
                    $dynamicConfig = Config::get('database.connections.dynamic');
                    $dynamicConfig['database'] = $candidateDb;
                    Config::set('database.connections.dynamic', $dynamicConfig);
                    DB::reconnect('dynamic');
                    $check = DB::connection('dynamic')->table('student_registration')->where('id', $id)->get();
                    if ($check->isNotEmpty()) {
                        $data['all_inquiry'] = $check;
                        break;
                    }
                } catch (\Exception $e) {}
            }
        }
        if (Schema::connection('dynamic')->hasTable('hrms_employees')) {
            $data['drivername'] = HrmsEmployee::query()
                ->selectRaw("id, first_name, last_name, TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) as ename, contact_number")
                ->whereHas('position', function ($query) {
                    $query->whereRaw('LOWER(position_name) = ?', ['driver']);
                })
                ->where('employee_status', 'active')
                ->orderByRaw("LOWER(TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')))) ASC")
                ->get();

            $data['staffList'] = HrmsEmployee::query()
                ->selectRaw("id, first_name, last_name, TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) as full_name, contact_number")
                ->where('employee_status', 'active')
                ->orderByRaw("LOWER(TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')))) ASC")
                ->get();
        } else {
            $data['drivername'] = DB::connection('dynamic')->table('busstaff')->where('role', 'Driver')->where('is_delete', 0)->get();
            $data['staffList'] = collect([]);
        }
        $data['caste'] = DB::connection('dynamic')->table('caste_name')->orderByRaw('LOWER(TRIM(caste_name)) ASC')->get();
		$data['classlist'] = DB::connection('dynamic')->table('class_name')
            ->whereNotNull('class_name')
            ->distinct()
            ->orderBy('id')
            ->get();
        if ($data['classlist']->isEmpty() && Schema::connection('dynamic')->hasTable('classes')) {
            $data['classlist'] = DB::connection('dynamic')->table('classes')
                ->whereNotNull('class_name')
                ->select('class_name')
                ->distinct()
                ->orderBy('class_name')
                ->get();
        }

        $assignedCombinationId = null;
        $assignedStreamId = null;
        if (Schema::connection('dynamic')->hasTable('subject_assign_student')) {
            $existingAssignment = DB::connection('dynamic')->table('subject_assign_student')
                ->where('students_details', $id)
                ->where('is_delete', 0)
                ->first();
            if ($existingAssignment && !empty($existingAssignment->assign_this_combtoall)) {
                $assignedCombinationId = $existingAssignment->assign_this_combtoall;
            }
        }
        if (!$assignedCombinationId && !empty($data['all_inquiry'][0]->json_str)) {
            $parsedJson = json_decode($data['all_inquiry'][0]->json_str, true);
            if (is_array($parsedJson) && !empty($parsedJson['combination_id'])) {
                $assignedCombinationId = $parsedJson['combination_id'];
            }
        }
        if ($assignedCombinationId && Schema::connection('dynamic')->hasTable('subject_combinations')) {
            $comb = DB::connection('dynamic')->table('subject_combinations')->where('id', $assignedCombinationId)->first();
            if ($comb && !empty($comb->streams)) {
                $assignedStreamId = $comb->streams;
            }
        }
        if (!$assignedStreamId && !empty($data['all_inquiry'][0]->json_str)) {
            $parsedJson = json_decode($data['all_inquiry'][0]->json_str, true);
            if (is_array($parsedJson) && !empty($parsedJson['stream_id'])) {
                $assignedStreamId = $parsedJson['stream_id'];
            }
        }
        $data['assignedCombinationId'] = $assignedCombinationId;
        $data['assignedStreamId'] = $assignedStreamId;

        return view('backend.student_registrations.edit_registration',$data); 
    }

public function perstudueamount($id){
    // print_r($id);exit;
    $all_inquiry = DB::connection('dynamic')
    ->table('totalnextyear')
    ->join('student_registration', 'totalnextyear.scholar_no', '=', 'student_registration.scholar_no')
    ->select('totalnextyear.scholar_no','totalnextyear.receipt_number', 'totalnextyear.fees_date','totalnextyear.account_name', 'totalnextyear.due_date','totalnextyear.fees', 'totalnextyear.totalnextyear', 'student_registration.student_name','student_registration.class_name')
    ->where('totalnextyear.scholar_no', $id)  // Specify the table alias for 'scholar_no'
    ->get();
    // echo"<pre>";print_r($all_inquiry);exit;
    return view('backend.perstudueamount', compact('all_inquiry'));

}
    
   public function saveeditregistration(Request $request){ 
	$dbName = session('db_names') ?? request()->cookie('db_names') ?? request()->input('db_names');
        if (!empty($dbName)) {
            Config::set('database.connections.dynamic.database', $dbName);
            DB::purge('dynamic');
        }

        $existingStudent = DB::connection('dynamic')->table('student_registration')->where('id', $request->id)->first();
        if (!$existingStudent) {
            foreach ($this->recentAcademicSessionDatabases() as $candidateDb) {
                try {
                    Config::set('database.connections.dynamic.database', $candidateDb);
                    DB::purge('dynamic');
                    $existingStudent = DB::connection('dynamic')->table('student_registration')->where('id', $request->id)->first();
                    if ($existingStudent) {
                        session(['db_names' => $candidateDb]);
                        break;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        $existingJson = (!empty($existingStudent) && !empty($existingStudent->json_str)) ? json_decode($existingStudent->json_str, true) : [];
        if (!is_array($existingJson)) {
            $existingJson = [];
        }	
   $postData_html = $request->all();
   $files = [];
        
        // echo '<pre>';
        // echo"<pre>";print_r($postData_html);die();
 // Birthcirtificate
 if ($request->hasFile('BirthCertificate')) {
    $BirthCertificate = 'BirthCertificate' . time() . '.' . $request->file('BirthCertificate')->extension();
    
    $request->file('BirthCertificate')->move(public_path('uploads/admin/BirthCertificate'), $BirthCertificate);

    $files['BirthCertificate'] = $BirthCertificate;
} else {
    // If no file is selected, set $BirthCertificate to null or an empty string
    $BirthCertificate = null;
    // or $BirthCertificate = '';
}

  // aadharProff
  if ($request->hasFile('aadharProff')) {
    $aadharProff = 'aadharProff' . time() . '.' . $request->file('aadharProff')->extension();
    $request->file('aadharProff')->move(public_path('uploads/admin/aadharProff'), $aadharProff);
    $files['aadharProff'] = $aadharProff;
} else {
    // If no file is selected, set $aadharProff to null or an empty string
    $aadharProff = null;
    // or $aadharProff = '';
}
            // bankbProff

            if ($request->hasFile('bankbProff')) {
                $bankbProff = 'bankbProff' . time() . '.' . $request->file('bankbProff')->extension();
                $request->file('bankbProff')->move(public_path('uploads/admin/bankbProff'), $bankbProff);
                $files['bankbProff'] = $bankbProff;
            } else {
                // If no file is selected, set $bankbProff to null or an empty string
                $bankbProff = null;
                // or $bankbProff = '';
            }
              // StuProf
              if ($request->hasFile('StuProf')) {
                $StuProf = 'StuProf' . time() . '.' . $request->file('StuProf')->extension();
                $request->file('StuProf')->move(public_path('uploads/admin/StuProf'), $StuProf);
                $files['StuProf'] = $StuProf;
            } else {
                // If no file is selected, set $StuProf to null or an empty string
                $StuProf = null;
                // or $StuProf = '';
            }
            // // sssmprof
            if ($request->hasFile('sssmprof')) {
                $sssmprof = 'sssmprof' . time() . '.' . $request->file('sssmprof')->extension();
                $request->file('sssmprof')->move(public_path('uploads/admin/sssmprof'), $sssmprof);
                $files['sssmprof'] = $sssmprof;
            } else {
                // If no file is selected, set $sssmprof to null or an empty string
                $sssmprof = null;
                // or $sssmprof = '';
            }
            // salaryprof
            if ($request->hasFile('salaryprof')) {
                $salaryprof = 'salaryprof' . time() . '.' . $request->file('salaryprof')->extension();
                $request->file('salaryprof')->move(public_path('uploads/admin/salaryprof'), $salaryprof);
                $files['salaryprof'] = $salaryprof;
            } else {
                // If no file is selected, set $salaryprof to null or an empty string
                $salaryprof = null;
                // or $salaryprof = '';
            }
             // CastProff
             if ($request->hasFile('CastProff')) {
                $CastProff = 'CastProff' . time() . '.' . $request->file('CastProff')->extension();
                $request->file('CastProff')->move(public_path('uploads/admin/CastProff'), $CastProff);
                $files['CastProff'] = $CastProff;
            } else {
                // If no file is selected, set $CastProff to null or an empty string
                $CastProff = null;
                // or $CastProff = '';
            }
                    // transfer_certificate
                    if ($request->hasFile('TransferCertificate')) {
                        $TransferCertificate = 'TransferCertificate' . time() . '.' . $request->file('TransferCertificate')->extension();
                        $request->file('TransferCertificate')->move(public_path('uploads/admin/TransferCertificate'), $TransferCertificate);
                        $files['TransferCertificate'] = $TransferCertificate;
                    } else {
                        // If no file is selected, set $TransferCertificate to null or an empty string
                        $TransferCertificate = null;
                        // or $TransferCertificate = '';
                    }
            // address_proof
            if ($request->hasFile('AddressProff')) {
                $AddressProff = 'AddressProff' . time() . '.' . $request->file('AddressProff')->extension();
                $request->file('AddressProff')->move(public_path('uploads/admin/AddressProff'), $AddressProff);
                $files['AddressProff'] = $AddressProff;
            } else {
                // If no file is selected, set $AddressProff to null or an empty string
                $AddressProff = null;
                // or $AddressProff = '';
            }
            // last_report_card
            if ($request->hasFile('LastReportCard')) {
                $LastReportCard = 'LastReportCard' . time() . '.' . $request->file('LastReportCard')->extension();
                $request->file('LastReportCard')->move(public_path('uploads/admin/LastReportCard'), $LastReportCard);
                $files['LastReportCard'] = $LastReportCard;
            } else {
                // If no file is selected, set $LastReportCard to null or an empty string
                $LastReportCard = null;
                // or $LastReportCard = '';
            }

		$studentDobVal = !empty($request->student_dob) ? $request->student_dob : (!empty($existingStudent->date_of_birth) ? $existingStudent->date_of_birth : ($existingJson['student_dob'] ?? ''));
        $nationalityVal = !empty($postData_html['nationality']) ? $postData_html['nationality'] : ($existingJson['nationality'] ?? 'Indian');
        $categoryVal = !empty($request->category) ? $request->category : ($existingJson['category'] ?? '');
        $classNameVal = !empty($request->classname) ? $request->classname : (!empty($existingStudent->class_name) ? $existingStudent->class_name : ($existingJson['classname'] ?? ''));
        $sessionNameVal = !empty($request->session_name) ? $request->session_name : (!empty($existingStudent->session_name) ? $existingStudent->session_name : ($existingJson['session_name'] ?? ''));
        $batchNameVal = (!empty($postData_html['batch']) && $postData_html['batch'] !== 'select') ? $postData_html['batch'] : ($existingJson['batch'] ?? '');
        $casteVal = (!empty($request->student_caste) && $request->student_caste !== '0' && $request->student_caste !== 'select') ? $request->student_caste : (!empty($request->caste) && $request->caste !== '0' ? $request->caste : ($existingJson['student_caste'] ?? ($existingJson['caste'] ?? '')));
        $sectionNameVal = (!empty($postData_html['section_name'])) ? $postData_html['section_name'] : (!empty($existingStudent->section_name) ? $existingStudent->section_name : ($existingJson['section_name'] ?? ''));
        $studentNameVal = !empty($request->studentname) ? $request->studentname : (!empty($request->student_name) ? $request->student_name : (!empty($existingStudent->student_name) ? $existingStudent->student_name : ($existingJson['studentname'] ?? '')));

        $postData = [
        
            "scholar_no"    => $postData_html['scholar_no'] ?? ($existingStudent->scholar_no ?? ($existingJson['scholar_no'] ?? '')),
            "application_for"   => $postData_html['application_for'] ?? ($existingStudent->application_for ?? ($existingJson['application_for'] ?? '')),
            "form_number"    => $postData_html['form_number'] ?? ($existingStudent->form_number ?? ($existingJson['form_number'] ?? '')),
            "studentname_prefix" => $postData_html['studentname_prefix'] ?? ($existingJson['studentname_prefix'] ?? ''),
            "studentname"   => $studentNameVal,
            "received_amount"   => $postData_html['received_amount'] ?? ($existingJson['received_amount'] ?? ''),
            "amount"   => $postData_html['amount'] ?? ($existingJson['amount'] ?? ''),
            "gender" => $postData_html['gender'] ?? ($existingJson['gender'] ?? ''),
            "student_dob"   => $studentDobVal,
            "nationality" => $nationalityVal,
            "session_name"   => $sessionNameVal,
            "present_address"   => $postData_html['present_address'] ?? ($existingJson['present_address'] ?? ''),
            "permanent_address"   => $postData_html['permanent_address'] ?? ($existingJson['permanent_address'] ?? ''),
            "phone_number"   => $postData_html['phone_number'] ?? ($existingJson['phone_number'] ?? ''),
            "mobile_number"   => $postData_html['mobile_number'] ?? ($existingJson['mobile_number'] ?? ''),
            "mother_tongue"   => $postData_html['mother_tongue'] ?? ($existingJson['mother_tongue'] ?? ''),
            "remarks"   => $postData_html['remarks'] ?? ($existingJson['remarks'] ?? ''),
            "vaccaination"   => $postData_html['vaccaination'] ?? ($existingJson['vaccaination'] ?? ''),
            "SSSMID"   => $postData_html['SSSMID'] ?? ($existingJson['SSSMID'] ?? ''),
            "family_SSSMID"   => $postData_html['family_SSSMID'] ?? ($existingJson['family_SSSMID'] ?? ''),

            "AadharNo"   => $postData_html['AadharNo'] ?? ($existingJson['AadharNo'] ?? ''),
            "apaar_id"   => $postData_html['apaar_id'] ?? ($existingJson['apaar_id'] ?? ''),
            "pen_no"     => $postData_html['pen_no'] ?? ($existingJson['pen_no'] ?? ''),
            "student_medical_conserns"   => $postData_html['student_medical_conserns'] ?? ($existingJson['student_medical_conserns'] ?? ''),
            "present_school_name"   => $postData_html['present_school_name'] ?? ($existingJson['present_school_name'] ?? ''),
            "is_sibling_applied_for_admission"   => $postData_html['is_sibling_applied_for_admission'] ?? ($existingJson['is_sibling_applied_for_admission'] ?? ''),
            "searchfather"   => $postData_html['searchfather'] ?? ($existingJson['searchfather'] ?? ''),
            "siblings_namesecond"   => $postData_html['siblings_namesecond'] ?? ($existingJson['siblings_namesecond'] ?? ''),
            "siblings_section_second"    => $postData_html['siblings_section_second'] ?? ($existingJson['siblings_section_second'] ?? ''),
            "sibling_class_second"    => $postData_html['sibling_class_second'] ?? ($existingJson['sibling_class_second'] ?? ''),
            
            "siblings_bod_second"    => $postData_html['siblings_bod_second'] ?? ($existingJson['siblings_bod_second'] ?? ''),
            "driver_name"    => $postData_html['driver_name'] ?? ($existingJson['driver_name'] ?? ''),
            "bus_facility_start_date"    => $postData_html['bus_facility_start_date'] ?? ($existingJson['bus_facility_start_date'] ?? ''),
            "is_staff_applied_for_admission" => $postData_html['is_staff_applied_for_admission'] ?? ($existingJson['is_staff_applied_for_admission'] ?? ''),
            "staff_name"    => $postData_html['staff_name'] ?? ($existingJson['staff_name'] ?? ''),
            "fathername_prefix" => $postData_html['fathername_prefix'] ?? ($existingJson['fathername_prefix'] ?? ''),
            "fathername"    => $postData_html['fathername'] ?? ($existingJson['fathername'] ?? ''),
            "father_education"    => $postData_html['father_education'] ?? ($existingJson['father_education'] ?? ''),
            "father_organization"    => $postData_html['father_organization'] ?? ($existingJson['father_organization'] ?? ''),
            "father_designation"    => $postData_html['father_designation'] ?? ($existingJson['father_designation'] ?? ''),
            "father_office_telephone"    => $postData_html['father_office_telephone'] ?? ($existingJson['father_office_telephone'] ?? ''),
            "father_email_id"   => $postData_html['father_email_id'] ?? ($existingJson['father_email_id'] ?? ''),
            "father_mobile"   => $postData_html['father_mobile'] ?? ($existingJson['father_mobile'] ?? ''),
            "fatherSSSMID"   => $postData_html['fatherSSSMID'] ?? ($existingJson['fatherSSSMID'] ?? ''),
            "fatherAadharNo"   => $postData_html['fatherAadharNo'] ?? ($existingJson['fatherAadharNo'] ?? ''),
            "father_res_address"   => $postData_html['father_res_address'] ?? ($existingJson['father_res_address'] ?? ''),           
            
            "father_emergency_contact"   => $postData_html['father_emergency_contact'] ?? ($existingJson['father_emergency_contact'] ?? ''),
            "mothername_prefix" => $postData_html['mothername_prefix'] ?? ($existingJson['mothername_prefix'] ?? ''),
            "mothername"   => $postData_html['mothername'] ?? ($existingJson['mothername'] ?? ''),
            "mother_education"   => $postData_html['mother_education'] ?? ($existingJson['mother_education'] ?? ''),
            "mother_organization"   => $postData_html['mother_organization'] ?? ($existingJson['mother_organization'] ?? ''),
            "mother_office_telephone"   => $postData_html['mother_office_telephone'] ?? ($existingJson['mother_office_telephone'] ?? ''),
            "mother_email"   => $postData_html['mother_email'] ?? ($existingJson['mother_email'] ?? ''),
            "mother_mobile"   => $postData_html['mother_mobile'] ?? ($existingJson['mother_mobile'] ?? ''),
            "motherSSSMID"   => $postData_html['motherSSSMID'] ?? ($existingJson['motherSSSMID'] ?? ''),
            "motherAadharNo"   => $postData_html['motherAadharNo'] ?? ($existingJson['motherAadharNo'] ?? ''),
            "mother_res_address" => $postData_html['mother_res_address'] ?? ($existingJson['mother_res_address'] ?? ''),
            "mother_emergency_contact"   => $postData_html['mother_emergency_contact'] ?? ($existingJson['mother_emergency_contact'] ?? ''),
            "guardian_name"   => $postData_html['guardian_name'] ?? ($existingJson['guardian_name'] ?? ''),
            
            "guardian_office_telephone"   => $postData_html['guardian_office_telephone'] ?? ($existingJson['guardian_office_telephone'] ?? ''),
            "guardian_email_id"   => $postData_html['guardian_email_id'] ?? ($existingJson['guardian_email_id'] ?? ''),
            "guardian_mobile"   => $postData_html['guardian_mobile'] ?? ($existingJson['guardian_mobile'] ?? ''),
            "guardian_permanent_address"   => $postData_html['guardian_permanent_address'] ?? ($existingJson['guardian_permanent_address'] ?? ''),
            "guardian_emergency_contact"   => $postData_html['guardian_emergency_contact'] ?? ($existingJson['guardian_emergency_contact'] ?? ''),
            "guardian_relation"   => $postData_html['guardian_relation'] ?? ($existingJson['guardian_relation'] ?? ''),
            'amount' => $postData_html['amount'] ?? ($existingJson['amount'] ?? ''),
            "bankName"   => $postData_html['bankName'] ?? ($existingJson['bankName'] ?? ''),
            "branchName"   => $postData_html['branchName'] ?? ($existingJson['branchName'] ?? ''),
            "account_number" => $postData_html['account_number'] ?? ($existingJson['account_number'] ?? ''),
            "ifsc_code"   => $postData_html['ifsc_code'] ?? ($existingJson['ifsc_code'] ?? ''),
            'reference_number' => $postData_html['reference_number'] ?? ($existingJson['reference_number'] ?? ''),
            "iid"   => $postData_html['iid'] ?? ($existingJson['iid'] ?? ''),
            "submit"   => "submit",

            "bus_facility_end_date" => $postData_html['bus_facility_end_date'] ?? ($existingJson['bus_facility_end_date'] ?? ''),
            "id" => $request->id,
            "classname" => $classNameVal,
            "batch" => $batchNameVal,
            "section_name" => $sectionNameVal,
            "father_dob" => $postData_html['father_dob'] ?? ($existingJson['father_dob'] ?? ''),
            "mother_dob" => $postData_html['mother_dob'] ?? ($existingJson['mother_dob'] ?? ''),
            "mother_ocupation" => $postData_html['mother_ocupation'] ?? ($existingJson['mother_ocupation'] ?? ''),
            "local_guardian" => $postData_html['local_guardian'] ?? ($existingJson['local_guardian'] ?? ''),
            "local_address" => $postData_html['local_address'] ?? ($existingJson['local_address'] ?? ''),
            "guradian_phone"  => $postData_html['guradian_phone'] ?? ($existingJson['guradian_phone'] ?? ''),
            "guradian_mobile" => $postData_html['guradian_mobile'] ?? ($existingJson['guradian_mobile'] ?? ''),
            "guradian_parent_category" => $postData_html['guradian_parent_category'] ?? ($existingJson['guradian_parent_category'] ?? ''),
            "guradian_new_category" => $postData_html['guradian_new_category'] ?? ($existingJson['guradian_new_category'] ?? ''),
            "guradian_new_house" => $postData_html['guradian_new_house'] ?? ($existingJson['guradian_new_house'] ?? ''),
            // ----------------------------------------------------------

            "category" => $categoryVal,
            "religion" => !empty($request->religion) ? $request->religion : ($existingJson['religion'] ?? ""),
            "student_caste" => $casteVal,
            "required_school_transport" => $request->required_school_transport ?? ($existingJson['required_school_transport'] ?? ''),
            "birth_certificate_chk" => $request->birth_certificate_chk ?? ($existingJson['birth_certificate_chk'] ?? ''),
            "transfer_certificate_chk" => $request->transfer_certificate_chk ?? ($existingJson['transfer_certificate_chk'] ?? ''),
            "address_proof_chk" => $request->address_proof_chk ?? ($existingJson['address_proof_chk'] ?? ''),
            "cast_chk" => $request->cast_chk ?? ($existingJson['cast_chk'] ?? ''),
            "aadhar_chk" => $request->aadhar_chk ?? ($existingJson['aadhar_chk'] ?? ''),
            "bankb_chk" => $request->bankb_chk ?? ($existingJson['bankb_chk'] ?? ''),
            "stuprof_chk" => $request->stuprof_chk ?? ($existingJson['stuprof_chk'] ?? ''),
            "sssmprof_chk" => $request->sssmprof_chk ?? ($existingJson['sssmprof_chk'] ?? ''),
            "salaryprof_chk" => $request->salaryprof_chk ?? ($existingJson['salaryprof_chk'] ?? ''),
            "last_report_card_chk" => $request->last_report_card_chk ?? ($existingJson['last_report_card_chk'] ?? ''),
            "BirthCertificate" => (!empty($postData_html['BirthCertificate'])) ? $BirthCertificate : (!empty($postData_html['BirthCertificate1']) ? $postData_html['BirthCertificate1'] : ($existingJson['BirthCertificate'] ?? null)),
            "TransferCertificate" => (!empty($postData_html['TransferCertificate'])) ? $TransferCertificate : (!empty($postData_html['TransferCertificate1']) ? $postData_html['TransferCertificate1'] : ($existingJson['TransferCertificate'] ?? null)),
            "AddressProff" => (!empty($postData_html['AddressProff'])) ? $AddressProff : (!empty($postData_html['AddressProff1']) ? $postData_html['AddressProff1'] : ($existingJson['AddressProff'] ?? null)),
            "CastProff" => (!empty($postData_html['CastProff'])) ? $CastProff : (!empty($postData_html['CastProff1']) ? $postData_html['CastProff1'] : ($existingJson['CastProff'] ?? null)),
            "aadharProff" => (!empty($postData_html['aadharProff'])) ? $aadharProff : (!empty($postData_html['aadharProff1']) ? $postData_html['aadharProff1'] : ($existingJson['aadharProff'] ?? null)),
            "bankbProff" => (!empty($postData_html['bankbProff'])) ? $bankbProff : (!empty($postData_html['bankbProff1']) ? $postData_html['bankbProff1'] : ($existingJson['bankbProff'] ?? null)),
            "StuProf" => (!empty($postData_html['StuProf'])) ? $StuProf : (!empty($postData_html['StuProf1']) ? $postData_html['StuProf1'] : ($existingJson['StuProf'] ?? null)),
            "sssmprof" => (!empty($postData_html['sssmprof'])) ? $sssmprof : (!empty($postData_html['sssmprof1']) ? $postData_html['sssmprof1'] : ($existingJson['sssmprof'] ?? null)),
            "salaryprof" => (!empty($postData_html['salaryprof'])) ? $salaryprof : (!empty($postData_html['salaryprof1']) ? $postData_html['salaryprof1'] : ($existingJson['salaryprof'] ?? null)),
            "LastReportCard" => (!empty($postData_html['LastReportCard'])) ? $LastReportCard : (!empty($postData_html['LastReportCard1']) ? $postData_html['LastReportCard1'] : ($existingJson['LastReportCard'] ?? null))

    ];
        // print_r($postData);die();
        // unset($postData['application_for']);
        // unset($postData['form_number']);
        // unset($postData['date_of_birth']);
        // unset($postData['class_name']);


        unset($postData['_token']);
		
		// Merge existing json_str so unrelated fields are not lost
        $mergedPostData = array_merge($existingJson, array_filter($postData, function($val) {
            return $val !== null && $val !== '';
        }));

        $studentDobVal = !empty($request->student_dob) ? $request->student_dob : (!empty($existingStudent->date_of_birth) ? $existingStudent->date_of_birth : ($existingJson['student_dob'] ?? ''));
        $nationalityVal = !empty($postData_html['nationality']) ? $postData_html['nationality'] : ($existingJson['nationality'] ?? 'Indian');
        $categoryVal = !empty($request->category) ? $request->category : ($existingJson['category'] ?? '');
        $classNameVal = !empty($request->classname) ? $request->classname : (!empty($existingStudent->class_name) ? $existingStudent->class_name : ($existingJson['classname'] ?? ''));
        $sessionNameVal = !empty($request->session_name) ? $request->session_name : (!empty($existingStudent->session_name) ? $existingStudent->session_name : ($existingJson['session_name'] ?? ''));
        $batchNameVal = (!empty($postData_html['batch']) && $postData_html['batch'] !== 'select') ? $postData_html['batch'] : ($existingJson['batch'] ?? '');
        $casteVal = (!empty($request->student_caste) && $request->student_caste !== '0' && $request->student_caste !== 'select') ? $request->student_caste : (!empty($request->caste) && $request->caste !== '0' ? $request->caste : ($existingJson['student_caste'] ?? ($existingJson['caste'] ?? '')));
        $sectionNameVal = (!empty($postData_html['section_name'])) ? $postData_html['section_name'] : (!empty($existingStudent->section_name) ? $existingStudent->section_name : ($existingJson['section_name'] ?? ''));
        $studentNameVal = !empty($request->studentname) ? $request->studentname : (!empty($request->student_name) ? $request->student_name : (!empty($existingStudent->student_name) ? $existingStudent->student_name : ($existingJson['studentname'] ?? '')));

        $fatherNameVal = !empty($postData_html['fathername']) ? $postData_html['fathername'] : (!empty($existingJson['fathername']) ? $existingJson['fathername'] : ($existingJson['student_father_name'] ?? ''));
        $motherNameVal = !empty($postData_html['mothername']) ? $postData_html['mothername'] : (!empty($existingJson['mothername']) ? $existingJson['mothername'] : ($existingJson['mother_name'] ?? ''));
        $mobileVal = !empty($postData_html['mobile_number']) ? $postData_html['mobile_number'] : (!empty($existingStudent->mobile_number) ? $existingStudent->mobile_number : ($existingJson['mobile_number'] ?? ($existingJson['father_mobile'] ?? '')));
        $phoneVal = !empty($postData_html['phone_number']) ? $postData_html['phone_number'] : (!empty($existingStudent->phone_number) ? $existingStudent->phone_number : ($existingJson['phone_number'] ?? ''));

        $mergedPostData['student_dob'] = $studentDobVal;
        $mergedPostData['nationality'] = $nationalityVal;
        $mergedPostData['category'] = $categoryVal;
        $mergedPostData['classname'] = $classNameVal;
        $mergedPostData['session_name'] = $sessionNameVal;
        $mergedPostData['batch'] = $batchNameVal;
        $mergedPostData['student_caste'] = $casteVal;
        $mergedPostData['caste'] = $casteVal;
        $mergedPostData['fathername'] = $fatherNameVal;
        $mergedPostData['student_father_name'] = $fatherNameVal;
        $mergedPostData['mothername'] = $motherNameVal;
        $mergedPostData['mother_name'] = $motherNameVal;
        $mergedPostData['mobile_number'] = $mobileVal;
        $mergedPostData['phone_number'] = $phoneVal;
        if (!empty($studentNameVal)) {
            $mergedPostData['studentname'] = $studentNameVal;
        }

        $isNextYearVal = 0;
        if (isset($postData_html['next_year'])) {
            $isNextYearVal = (!empty($postData_html['next_year']) && ($postData_html['next_year'] == 1 || $postData_html['next_year'] === '1')) ? 1 : 0;
        } elseif (isset($existingStudent->next_year)) {
            $isNextYearVal = (int)$existingStudent->next_year;
        } elseif (isset($existingJson['next_year'])) {
            $isNextYearVal = (!empty($existingJson['next_year']) && ($existingJson['next_year'] == 1 || $existingJson['next_year'] === '1')) ? 1 : 0;
        }
        $mergedPostData['next_year'] = $isNextYearVal;

        if (isset($postData_html['admission_session'])) {
            $mergedPostData['admission_session'] = $postData_html['admission_session'];
        } elseif (isset($existingJson['admission_session'])) {
            $mergedPostData['admission_session'] = $existingJson['admission_session'];
        }
        if (isset($postData_html['intended_session'])) {
            $mergedPostData['intended_session'] = $postData_html['intended_session'];
        } elseif (isset($existingJson['intended_session'])) {
            $mergedPostData['intended_session'] = $existingJson['intended_session'];
        }

        $isSeniorClass = in_array(trim((string)$classNameVal), ['11', '12'], true);
        if ($isSeniorClass) {
            $streamId = $request->stream_id ?? ($mergedPostData['stream_id'] ?? null);
            if (!empty($streamId)) {
                $mergedPostData['stream_id'] = $streamId;
                $streamRecord = DB::connection('dynamic')->table('streams')->where('id', $streamId)->first();
                $streamName = $streamRecord ? strtoupper(trim($streamRecord->streams)) : '';
                if ($streamName === 'SCIENCE' || (int)$streamId === 3) {
                    $sectionNameVal = 'Kautilya';
                } elseif (in_array($streamName, ['COMMERCE', 'HUMANITIES'], true) || in_array((int)$streamId, [1, 2], true)) {
                    $sectionNameVal = 'Ramanujan';
                }
            }
            if (!empty($request->combination_id)) {
                $mergedPostData['combination_id'] = $request->combination_id;
            }
        }
        $mergedPostData['section_name'] = $sectionNameVal;

        $nextYearSectionVal = null;
        $nextYearStreamVal = null;
        $nextYearCombVal = null;
        if ($isNextYearVal === 1) {
            $nextYearSectionVal = $request->next_year_section ?? ($existingStudent->next_year_section ?? ($sectionNameVal ?: 'D'));
            $nextYearStreamVal = $isSeniorClass ? ($request->next_year_stream ?? ($request->stream_id ?? ($existingStudent->next_year_stream ?? ($mergedPostData['stream_id'] ?? null)))) : null;
            $nextYearCombVal = $isSeniorClass ? ($request->next_year_subject_combination ?? ($request->combination_id ?? ($existingStudent->next_year_subject_combination ?? ($mergedPostData['combination_id'] ?? null)))) : null;
        }

        $mergedPostData['next_year_section'] = $nextYearSectionVal;
        $mergedPostData['next_year_stream'] = $nextYearStreamVal;
        $mergedPostData['next_year_subject_combination'] = $nextYearCombVal;
        
        $insertArr  = [
            'application_for'=> !empty($request->application_for) ? $request->application_for : ($existingStudent->application_for ?? ''),
            'form_number'=> !empty($request->form_number) ? $request->form_number : ($existingStudent->form_number ?? ''),
            'scholar_no'=> !empty($request->scholar_no) ? $request->scholar_no : ($existingStudent->scholar_no ?? ''),
            'date_of_birth'=> $studentDobVal,
            'class_name'=> $classNameVal,
            'section_name'=> $sectionNameVal,
            'mobile_number'=> $mobileVal,
            'phone_number'=> $phoneVal,
            'staff_name'=> (!empty($request->staff_name) && is_array($request->staff_name)) ? ($request->staff_name[0] ?? '') : (!empty($request->staff_name) ? $request->staff_name : ($existingStudent->staff_name ?? '')),
            'driver'=> $request->driver_name ?? ($existingStudent->driver ?? ''),
            'student_name'=> $studentNameVal,
            'session_name'=> $sessionNameVal,
            'inq_mode'=>'off',
            'status'=>'r',
            'next_year' => $isNextYearVal,
            'next_year_section' => $nextYearSectionVal,
            'next_year_stream' => $nextYearStreamVal,
            'next_year_subject_combination' => $nextYearCombVal,
            'json_str'=>json_encode($mergedPostData),
            'registration_date'=> !empty($request->registration_date) ? $request->registration_date : (!empty($existingStudent->registration_date) ? $existingStudent->registration_date : date('Y-m-d')),
        ];
		if (!empty($classNameVal)) {
            $classQuery = DB::connection('dynamic')->table('classes')->where('class_name', $classNameVal);
            if (!empty($sectionNameVal)) {
                $matchedClass = (clone $classQuery)->where('section_name', $sectionNameVal)->first();
                if ($matchedClass) {
                    $insertArr['class_id'] = $matchedClass->id;
                }
            }
            if (empty($insertArr['class_id'])) {
                $firstClass = $classQuery->first();
                if ($firstClass) {
                    $insertArr['class_id'] = $firstClass->id;
                }
            }
        }
        if (empty($insertArr['class_id']) && !empty($existingStudent->class_id)) {
            $insertArr['class_id'] = $existingStudent->class_id;
        }

        $inquiry_data = DB::connection('dynamic')->table('student_registration')->where('id', $request->id)->update($insertArr);

        if ($isSeniorClass && !empty($request->combination_id)) {
            DB::connection('dynamic')->table('subject_assign_student')->updateOrInsert(
                [
                    'students_details' => $request->id,
                ],
                [
                    'class_name' => $classNameVal,
                    'section_name' => $sectionNameVal,
                    'assign_this_combtoall' => $request->combination_id,
                    'is_delete' => 0,
                    'updated_at' => now(),
                ]
            );
        }
        
        $sibling = 0;
        $staff = 0;
        $percentage_total = 0;
        if(!empty($postData_html['is_sibling_applied_for_admission']) && $postData_html['is_sibling_applied_for_admission']==1){
            $sibling = 1;
            $percentage_total = $percentage_total+10;
        }
        
        if(!empty($postData_html['is_staff_applied_for_admission']) && $postData_html['is_staff_applied_for_admission']==1){
            $staff = 1;
            $percentage_total = $percentage_total+50;
        }
        DB::connection('dynamic')->table('student_fees_exempted')->updateOrInsert(
            [
                'scholar_no' => !empty($request->scholar_no) ? $request->scholar_no : ($existingStudent->scholar_no ?? ''),
                'head_name'  => 'TUITION FEES'
            ],
            [
                'sibling'    => $sibling,
                'staff'      => $staff,
                'exempt_per' => $percentage_total
            ]
        );
        return redirect()->route('student-registrations')->with('success', 'Student registration updated successfully.');  
    }
	
    public function getcastename(){

          $inquiry_data = CommanModel::fetchDataArr('caste_name');

        $passArr = ['inq_data'=>$inquiry_data];
 
        return $passArr;
    }

    /*getDataByFormNumber*/
    public function getsiblingbyfathersname(Request $request)
    {           
        $form_number = $request->form_number;
         
        $inquiry_data = DB::connection('dynamic')->table('student_registration')
        ->select('*')
        ->where(function ($query) use ($form_number) {
            $query->where('form_number', $form_number)
                  ->orWhere('student_name', $form_number)
                  ->orWhereJsonContains('json_str->fathername', $form_number);
        })
        ->first();
        // print_r($form_number);exit;
        $objsection ="";
        if(isset($inquiry_data->section)){
            $objsection = $inquiry_data->section;
        }
        // $inquiry_data->section = "";

//    echo"<pre>"; print_r($inquiry_data->student_name);exit;
        // $passArr = ['inq_data'=>$inquiry_data,'inq_str_data'=>json_decode($inquiry_data->json_str)];
        $passArr2 ='<table id="itemTable" class="table"><tbody class="field_wrapper answer">
                    <tr class="item">
                        <td><label for="siblings_name">Name</label>
                            <input class="form-control" id="siblings_namesecond" type="text" placeholder="Enter name"  name="siblings_namesecond" value="'.$inquiry_data->student_name.'">                       
                        </td>
                        <td><label for="sibling_class_second">Class</label>
                        <input class="form-control" id="sibling_class_second" type="text" placeholder="Enter name"  name="sibling_class_second" value="'.$inquiry_data->class_name.'">  
                        
                        </td>
                        <td><label for="siblings_school_second">Section</label>
                        <input class="form-control" id="siblings_section_second" type="text" placeholder="Enter name"  name="siblings_section_second" value="'.$objsection.'">  
                        </td>
                        <td><label for="siblings_school_second">Date Of Birth</label>
                            <input class="form-control"   id="picker2-" type="text" placeholder="dd-mm-yyyy"  name="siblings_bod_second[]" max="9999-12-31" value="'. date('d-m-Y',strtotime($inquiry_data->date_of_birth)).'">                
                        </td></tr><tbody><table>';
//  print_r($passArr2);exit;
        return $passArr2;
    }



    public function resetpasswordadmin(Request $request){
        
        $str = Hash::make("LVN@123");
        DB::table('student_registration')->where('id',$request->id)->update(['password' => $str]);
        return json_encode('success');
    }

    public function update_preenquiryview(Request $request){
        $statename = CommanModel::fetchDataWhere('states',['id'=>$request->state]);
            foreach($statename as $state){
                $statename = $state->name;
            } 
		$studentName = $this->formatFullName($request->studentname);
        $fatherName = $this->formatFullName($request->fathername);
        $motherName = $this->formatFullName($request->mothername);
        $jsonArr = [
            'enquiryno'=>$request->formno,
            'formno'=>$request->formno,
            'enquirydate'=>$request->enquirydate,
            'date_of_birth'=>$request->student_dob,
            'studentname_prefix'=>$request->studentname_prefix,
            'student_name'=>$studentName,
            'student_dob'=>$request->student_dob,
            'fathername_prefix'=>$request->fathername_prefix,
            'fathername'=>$fatherName,
            'fathermobile'=>$request->fathermobile,
            'mothername_prefix'=>$request->mothername_prefix,
            'mothername'=>$motherName,
            'mothermobile'=>$request->mothermobile,
            'gender'=>$request->gender,
            'admission_type'=>$request->admission_type,
            'address'=>$request->address,
            'city'=>$request->city,
            'pincode'=>$request->pincode,
            'state'=>$statename,            
            //'presentlyschool'=>$request->presentlyschool,
            
           ];
             $jsonStr = json_encode($jsonArr);

        $insertArr = [
            'application_for'=>$request->admission_type,
            'form_number'=>$request->formno,
            'date_of_birth'=>$request->student_dob,
            'class_name'=>$request->classname,
            'student_name'=>$studentName,
            'session_name'=>$request->session,
            'json_str'=> $jsonStr,
            'gender'=>$request->gender,
            'mobile_number'=>$request->fathermobile,
            'inq_mode'=>'on',
            'status'=>'p'
        ];
        
        // $request->update_id
        DB::table('inquiry_registration')->where('id',$request->update_id)->update($insertArr);
        echo "Record inserted successfully.<br/>";

        //$comman_model->insertData('inquiry_registration',$insertArr);
          return redirect('preenquiryviewlist/'.$request->update_id)
                        ->with('success','Record Update successfully');

        //return redirect('admin-enquiryform');
    }
	
	public function updateDiscount(Request $request)
    {
        $validated = $request->validate([
            'scholar_no' => 'required|string',
            'lumpsum_fees' => 'nullable|boolean',
            'siblings' => 'nullable|boolean',
            'staff_ward' => 'nullable|boolean',
            'lvn' => 'nullable|boolean',
        ]);

        // Define discount percentages
        $discounts = [
            'lum' => $request->has('lumpsum_fees') ? 10 : 0,
            'sibling' => $request->has('siblings') ? 10 : 0,
            'lssm' => $request->has('lvn') ? 10 : 0,
            'staff' => $request->has('staff_ward') ? 50 : 0,
        ];

        // Calculate total exempt percentage
        $exempt_per = array_sum($discounts);

        // Check if the record exists (Unique key issue)
        $existing = DB::table('student_fees_exempted')
            ->where('scholar_no', $validated['scholar_no'])
            ->first();

        $data = [
            'scholar_no' => $validated['scholar_no'],
            'head_name' => 'TUITION FEES', // Apply discount only on Tuition Fees
            'lum' => $discounts['lum'] > 0 ? 1 : 0,
            'sibling' => $discounts['sibling'] > 0 ? 1 : 0,
            'staff' => $discounts['staff'] > 0 ? 1 : 0,
            'lssm' => $discounts['lssm'] > 0 ? 1 : 0,
            'exempt_per' => $exempt_per, // Total discount percentage
        ];

        if ($existing) {
            // Update the existing record
            DB::table('student_fees_exempted')
                ->where('scholar_no', $validated['scholar_no'])
                ->update($data);
        } else {
            // Insert new record
            DB::table('student_fees_exempted')->insert($data);
        }

        // Fetch the updated/new record
        $updatedStudent = DB::table('student_fees_exempted')
            ->where('scholar_no', $validated['scholar_no'])
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Tuition Fees discount updated successfully!',
            'data' => $updatedStudent
        ]);
    }
	
	public function exportPreEnquiryFeesCsv(Request $request)
	{
		$filename = 'pre_enquiry_fees_list_' . date('Ymd_His') . '.csv';

		$records = DB::connection('dynamic')->table('inquiry_registration')
			->where('status', 'i')
			->get();

		$headers = [
			'Content-Type' => 'text/csv',
			'Content-Disposition' => "attachment; filename=\"$filename\"",
		];

		$columns = [
			'S.No',
			'Enquiry No.',
			'Class Name',
			'Student Name',
			'Father Name',
			'Session Name',
			'Mobile Number',
			'Amount',
			'Enquiry Date',
			'Year',
			'Status',
		];

		$callback = function () use ($records, $columns) {
			$file = fopen('php://output', 'w');
			fputcsv($file, $columns);

			$i = 1;
			foreach ($records as $record) {
				$json = json_decode($record->json_str, true);

				$studentName = (!empty($json['studentname_prefix']) ? $json['studentname_prefix'] . ' ' : '') . $record->student_name;
				$fatherName = (!empty($json['fathername_prefix']) ? $json['fathername_prefix'] . ' ' : '') . ($json['fathername'] ?? '');

				$year = '';
				if (!empty($record->next_year)) {
					$years = explode("_", $record->session_name);
					if (count($years) == 2) {
						$year = ($years[0] + 1) . '_' . ($years[1] + 1);
					}
				} elseif (!empty($_COOKIE['selectedYear'])) {
					$year = $_COOKIE['selectedYear'];
				}

				fputcsv($file, [
					$i++,
					$record->form_number,
					$record->class_name,
					$studentName,
					$fatherName,
					$record->session_name,
					$record->mobile_number,
					'500',
					date('d-m-Y', strtotime($record->created_at)),
					$year,
					'Inquiry',
				]);
			}

			fclose($file);
		};

		return Response::stream($callback, 200, $headers);
	}
	private function formatFullName($name)
	{
		$trimmed = trim(preg_replace('/\s+/', ' ', (string) $name));
		if ($trimmed === '') {
			return '';
		}

		return preg_replace_callback('/(^|[\s.\'-])([a-zA-Z\x{00C0}-\x{017F}])/u', function ($matches) {
			return $matches[1] . mb_strtoupper($matches[2], 'UTF-8');
		}, mb_strtolower($trimmed, 'UTF-8'));
	}

	
}
