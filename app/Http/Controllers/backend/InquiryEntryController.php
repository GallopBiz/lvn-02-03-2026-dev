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
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;

// use Illuminate\Support\Facades\DB
//use Request;
use DataTables;

class InquiryEntryController extends Controller
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
                $db_name = "2024_2025";
            }
            
        }
        // $db_name = "2023_2024";
        $dynamicConnectionName = 'dynamic';
        $dynamicConfig = Config::get("database.connections.{$dynamicConnectionName}");
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
		
		$currentSchoolYear = session('db_names');
	
        $enquirecount = DB::connection('dynamic')->table('inquiry_registration')->where('status','i')->where('session_name', $currentSchoolYear)->count();
        $all_inquiry = DB::connection('dynamic')->table('inquiry_registration')->where('status','i')->get(); 
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
         $all_inquiry = DB::connection('dynamic')->table('inquiry_registration')->where('status','i')->get(); //CommanModel::fetchDataWhere('inquiry_registration',['status'=>'i']);
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
        $data['caste'] = DB::connection('dynamic')->table('caste_name')->get();
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
        $data['caste'] = DB::connection('dynamic')->table('caste_name')->get();
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
        $all_inquiry = $response->get();
    
        // Fetch all data (for comparison or alternative view)
        $response2 = DB::table('inquiry_registration')
            ->select('inquiry_regist	ration.*')
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
        $all_inquiry = DB::connection('dynamic')->table('inquiry_registration')->where('status','p')->get();
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
        
        $response = DB::table('inquiry_registration')
    ->select('inquiry_registration.*', 'student_registration.scholar_no')
    ->join('student_registration', 'student_registration.form_number', '=', 'inquiry_registration.form_number');
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
        
        $EnqSecdata = $records1->get();
        
        
       $all_inquiry = $records1->get();

    // $all_inquiry = $records1;


        return view('backend.preenquiryfilter',compact('all_inquiry','jsonArr','studentclasses','EnqSecdata', 'classlist'));

        // return view('backend.preenquiryfilter', compact('all_inquiry','jsonArr','classlist2'));

    }
    
    public function admin_preenquiryform()
    {   
        $data['states'] = DB::connection('dynamic')->table('states')->select(['name','id'])->get();//State::get(["name", "id"]);
        $record = $record = DB::connection('dynamic')
			->table('inquiry_registration')
			->orderBy('form_number', 'asc')
			->get()
			->last();
        if(!empty($record)){
            $data['Userid'] = $record->form_number + 1;
        } else {
            $data['Userid'] = 1;
        }
        // $data['Userid'] = $uid + 1;
        $Userid = $data['Userid'];
        $states = $data['states'];
        $classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        
        return view('backend.preinquiryregistration', compact('classlist','data','Userid', 'states'));

    
    }
    
    public function save_student_preinquiryentry(Request $request)
{   
    $statename = DB::connection('dynamic')->table('states')->where('id', $request->state)->first()->name ?? '';

    // Format names properly
    $studentName = $this->formatFullName($request->studentname);
    $fatherName = $this->formatFullName($request->fathername);
    $motherName = $this->formatFullName($request->mothername);

    $jsonArr = [
        'enquiryno'         => $request->formno,
        'formno'            => $request->formno,
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
        'form_number'       => $request->formno,
        'date_of_birth'     => $request->date_of_birth,
        'class_name'        => $request->classname,
        'student_name'      => $studentName,
        'session_name'      => $request->session,
        'json_str'          => $jsonStr,
        'gender'            => $request->gender,
        'mobile_number'     => $request->fathermobile,
        'inq_mode'          => 'on',
        'next_year'         => (empty($request->next_year) ? 0 : 1),
        'status'            => 'p'
    ];

    DB::connection('dynamic')->table('inquiry_registration')->insert($insertArr);

    return redirect('admin-preenquiryform')->with('success', 'Record inserted successfully');
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

		foreach ($data['all_inquiry'] as $each_inq) {
			$scholar_no = $each_inq->scholar_no;

			$exempted = DB::connection('dynamic')
				->table('student_fees_exempted')
				->where('scholar_no', $scholar_no)
				->first();

			$data['student_fees_exempted'][$scholar_no] = $exempted;
		}
		print_r($id);
		//CommanModel::fetchDataWhere('student_registration',['id'=> $id]);
        $data['generateDueChartStatus'] = DB::connection('dynamic')->table('generate_duechartstatus')->select('*')->where('student_id',$id)->get();
        $data['late_fees_master'] = DB::connection('dynamic')->table('late_fees_master')->where('id',1)->first(); //Late_fees_master::where('id',1)->first();
        $data['drivername'] = DB::connection('dynamic')->table('busstaff')->where('role', 'Driver')->get();
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
        $data['all_inquiry'] = DB::connection('dynamic')->table('student_registration')->where('id',$id)->get(); $data['drivername'] = DB::connection('dynamic')->table('busstaff')->where('role', 'Driver')->get();
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
    
   public function saveeditregistration(Request $request){   //   thisone....
        $postData_html = $request->all();
        
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



        $postData = [
        
            "scholar_no"    => $postData_html['scholar_no'] ,
            "application_for"   => $postData_html['application_for'] ,
            "form_number"    => $postData_html['form_number'] ,
            "studentname_prefix" => $postData_html['studentname_prefix'] ,
            "studentname"   => $postData_html['studentname'],
            "received_amount"   => $postData_html['received_amount'],
            "amount"   => $postData_html['amount'],
            "gender" => (!empty($postData_html['gender'])) ? $postData_html['gender'] : "",
            "student_dob"   => $postData_html['student_dob'],
            "nationality" => $postData_html['nationality'],
            "session_name"   => $postData_html['session_name'],
            "present_address"   => $postData_html['present_address'],
            "permanent_address"   => $postData_html['permanent_address'],
            "phone_number"   => $postData_html['phone_number'],
            "mobile_number"   => $postData_html['mobile_number'],
            "mother_tongue"   => $postData_html['mother_tongue'],
            "remarks"   => $postData_html['remarks'],
            "vaccaination"   => $postData_html['vaccaination'],
            "SSSMID"   => $postData_html['SSSMID'],
            "family_SSSMID"   => $postData_html['family_SSSMID'],

            "AadharNo"   => $postData_html['AadharNo'],
            "student_medical_conserns"   => $postData_html['student_medical_conserns'],
            "present_school_name"   => $postData_html['present_school_name'],
            "is_sibling_applied_for_admission"   => (!empty($postData_html['is_sibling_applied_for_admission'])) ? $postData_html['is_sibling_applied_for_admission'] : "",
            "searchfather"   => $postData_html['searchfather'],
            "siblings_namesecond"   => $postData_html['siblings_namesecond'],
            "siblings_section_second"    => (!empty($postData_html['siblings_section_second'])) ? $postData_html['siblings_section_second'] : "" ,
            "sibling_class_second"    => (!empty($postData_html['sibling_class_second'])) ? $postData_html['sibling_class_second'] : "" ,
            
            "siblings_bod_second"    => $postData_html['siblings_bod_second'],
            "driver_name"    => $postData_html['driver_name'],
            "bus_facility_start_date"    => $postData_html['bus_facility_start_date'],
            "is_staff_applied_for_admission" => (!empty($postData_html['is_staff_applied_for_admission'])) ? $postData_html['is_staff_applied_for_admission'] : "",
            "staff_name"    => (!empty($postData_html['staff_name'])) ? $postData_html['staff_name'] : "",
            "fathername_prefix" =>$postData_html['fathername_prefix'] ,
            "fathername"    => $postData_html['fathername'],
            "father_education"    => $postData_html['father_education'],
            "father_organization"    => $postData_html['father_organization'],
            "father_designation"    => $postData_html['father_designation'],
            "father_office_telephone"    => $postData_html['father_office_telephone'],
            "father_email_id"   =>$postData_html['father_email_id'],
            "father_mobile"   =>$postData_html['father_mobile'],
            "fatherSSSMID"   => (!empty($postData_html['fatherSSSMID'])) ? $postData_html['fatherSSSMID'] : "" ,
            "fatherAadharNo"   =>(!empty($postData_html['fatherAadharNo'])) ? $postData_html['fatherAadharNo'] : "",
            "father_res_address"   =>(!empty($postData_html['father_res_address'])) ? $postData_html['father_res_address'] : "",           
            
            "father_emergency_contact"   =>$postData_html['father_emergency_contact'],
            "mothername_prefix" => $postData_html['mothername_prefix'],
            "mothername"   =>$postData_html['mothername'],
            "mother_education"   =>$postData_html['mother_education'],
            "mother_organization"   =>$postData_html['mother_organization'],
            "mother_office_telephone"   =>$postData_html['mother_office_telephone'],
            "mother_email"   =>$postData_html['mother_email'],
            "mother_mobile"   =>$postData_html['mother_mobile'],
            "motherSSSMID"   =>(!empty($postData_html['motherSSSMID'])) ? $postData_html['motherSSSMID'] : "",
            "motherAadharNo"   =>(!empty($postData_html['motherAadharNo'])) ? $postData_html['motherAadharNo'] : "",
            "mother_res_address"=>(!empty($postData_html['mother_res_address'])) ? $postData_html['mother_res_address'] : "",
            "mother_emergency_contact"   =>$postData_html['mother_emergency_contact'],
            "guardian_name"   =>$postData_html['guardian_name'],
            
            "guardian_office_telephone"   =>$postData_html['guardian_office_telephone'],
            "guardian_email_id"   =>$postData_html['guardian_email_id'],
            "guardian_mobile"   =>$postData_html['guardian_mobile'],
            "guardian_permanent_address"   =>$postData_html['guardian_permanent_address'],
            "guardian_emergency_contact"   =>$postData_html['guardian_emergency_contact'],
            "guardian_relation"   =>$postData_html['guardian_relation'],
            'amount' =>(!empty($postData_html['amount'])) ? $postData_html['amount'] : "",
            "bankName"   =>(!empty($postData_html['bankName'])) ? $postData_html['bankName'] : "",
            "branchName"   =>(!empty($postData_html['branchName'])) ? $postData_html['branchName'] : "",
            "account_number" =>(!empty($postData_html['account_number'])) ? $postData_html['account_number'] : "",
            "ifsc_code"   =>(!empty($postData_html['ifsc_code'])) ? $postData_html['ifsc_code'] : "",
            'reference_number'=>(!empty($postData_html['reference_number'])) ? $postData_html['reference_number'] : "",
            'reference_number'=>(!empty($postData_html['ifsc_code'])) ? $postData_html['ifsc_code'] : "",
            "iid"   =>(!empty($postData_html['iid'])) ? $postData_html['iid'] : "",
            "submit"   =>"submit",     //add

            "bus_facility_end_date" => "",  // editonly
            "id" => (!empty($postData_html['id'])) ? $postData_html['id'] : "",          // editonly
            "classname" => (!empty($postData_html['classname'])) ? $postData_html['classname'] : "",    // editonly
            "batch" => (!empty($postData_html['batch'])) ? $postData_html['batch'] : "" ,  // editonly
            "section_name" => (!empty($postData_html['section_name'])) ? $postData_html['section_name'] : "",  // editonly
            "father_dob" => "",    // editonly
            "mother_dob" => "",    // editonly
            "mother_ocupation" => $postData_html['mother_ocupation'],   //editonly     
            "local_guardian" => "",  //editonly
            "local_address" => "", ////editonly
            "guradian_phone"  => "", ////editonly
            "guradian_mobile" => "", ////editonly
            "guradian_parent_category" => "", //editonly
            "guradian_new_category" => "", //editonly
            "guradian_new_house" => "", //editonly
            // ----------------------------------------------------------

                
            "category" => $request->category,
            "religion" => $request->religion,
            "student_caste" => $request->student_caste,
            "classname" => $request->classname,
            "required_school_transport"    =>$request->required_school_transport,
            "section_name" => "",  // editonly
            "birth_certificate_chk" => $request->birth_certificate_chk,
            "transfer_certificate_chk" => $request->transfer_certificate_chk,
            "address_proof_chk" => $request->address_proof_chk,
            "cast_chk"=>$request->cast_chk,
            "aadhar_chk"=>$request->aadhar_chk,
            "bankb_chk"=>$request->bankb_chk,
            "stuprof_chk"=>$request->stuprof_chk,
            "sssmprof_chk"=>$request->sssmprof_chk,
            "salaryprof_chk"=>$request->salaryprof_chk,
            "last_report_card_chk" => $request->last_report_card_chk,
            "BirthCertificate" =>(!empty($postData_html['BirthCertificate'])) ? $BirthCertificate : $postData_html['BirthCertificate1'],
            "TransferCertificate" =>(!empty($postData_html['TransferCertificate'])) ? $TransferCertificate : $postData_html['TransferCertificate1'],
            "AddressProff" => (!empty($postData_html['AddressProff'])) ? $AddressProff : $postData_html['AddressProff1'],
            "CastProff"=>(!empty($postData_html['CastProff'])) ? $CastProff : $postData_html['CastProff1'],
            "aadharProff"=>(!empty($postData_html['aadharProff'])) ? $aadharProff : $postData_html['aadharProff1'],
            "bankbProff"=>(!empty($postData_html['bankbProff'])) ? $bankbProff : $postData_html['bankbProff1'],
            "StuProf"=>(!empty($postData_html['StuProf'])) ? $StuProf : $postData_html['StuProf1'],
            "sssmprof"=>(!empty($postData_html['sssmprof'])) ? $sssmprof : $postData_html['sssmprof1'],
            "salaryprof"=>(!empty($postData_html['salaryprof'])) ? $salaryprof : $postData_html['salaryprof1'],
            "LastReportCard" =>(!empty($postData_html['LastReportCard'])) ? $LastReportCard : $postData_html['LastReportCard1']

    ];

    if (!empty($postData['scholar_no'])) {
        echo '<input type="hidden" name="scholar_no" value="' . $postData['scholar_no'] . '">';
    }

    if (!empty($postData['SSSMID'])) {
        echo '<input type="hidden" name="SSSMID" value="' . $postData['SSSMID'] . '">';
    }
    if (!empty($postData['AadharNo'])) {
        echo '<input type="hidden" name="AadharNo" value="' . $postData['AadharNo'] . '">';
    }
    if (!empty($postData['siblings_section_second'])) {
        echo '<input type="hidden" name="siblings_section_second" value="' . $postData['siblings_section_second'] . '">';
    }
    // if (!empty($postData['staff_name'])) {
    //     echo '<input type="hidden" name="staff_name" value="' . $postData['staff_name'] . '">';
    // }
    if (!empty($postData['father_office_telephone'])) {
        echo '<input type="hidden" name="father_office_telephone" value="' . $postData['father_office_telephone'] . '">';
    }
    if (!empty($postData['fatherSSSMID'])) {
        echo '<input type="hidden" name="fatherSSSMID" value="' . $postData['fatherSSSMID'] . '">';
    }
    if (!empty($postData['fatherAadharNo'])) {
        echo '<input type="hidden" name="fatherAadharNo" value="' . $postData['fatherAadharNo'] . '">';
    }
    if (!empty($postData['motherSSSMID'])) {
        echo '<input type="hidden" name="motherSSSMID" value="' . $postData['motherSSSMID'] . '">';
    }
    if (!empty($postData['motherAadharNo'])) {
        echo '<input type="hidden" name="motherAadharNo" value="' . $postData['motherAadharNo'] . '">';
    }
    if (!empty($postData['iid'])) {
        echo '<input type="hidden" name="iid" value="' . $postData['iid'] . '">';
    }
    if (!empty($postData['submit'])) {
        echo '<input type="hidden" name="submit" value="' . $postData['submit'] . '">';
    }





            // addonly.....start....
            // "scholar_no"    => "" ,  // addonly
            // "SSSMID"   => "",     // addonly
            // "AadharNo"   => "",     // addonly
            // "siblings_section_second"    => "", // addonly
            // "staff_name"    => "",    // addonly
            // "father_office_telephone"    => "" ,  // addonly
            // "fatherSSSMID"   =>"",    // add
            // "fatherAadharNo"   =>"",  //add
            // "motherSSSMID"   =>"",    //add
            // "motherAadharNo"   =>"",  //add
            // "iid"   =>"",               //add
            // "submit"   =>"submit",     //add



       




     



        // print_r($postData);die();
        // unset($postData['application_for']);
        // unset($postData['form_number']);
        // unset($postData['date_of_birth']);
        // unset($postData['class_name']);


        unset($postData['_token']);

        $files = [];
        $postData['files'] = $files;

        $insertArr  = [
            'application_for'=>$request->application_for,
            'form_number'=>$request->form_number,
            'date_of_birth'=>$request->student_dob,
            'class_name'=>$request->classname,
            'student_name'=> $request->studentname,
            'session_name'=>$request->session_name,
            // 'student_name'=>$request->student_name,
            'inq_mode'=>'off',
            'status'=>'r',
            'json_str'=>json_encode($postData),
			'registration_date'=>$request->registration_date,
        ];
// echo"<pre>";print_r($insertArr);exit;
        
        $inquiry_data = DB::table('student_registration')->where('id',$request->id)->update($insertArr);
        
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
                'scholar_no' => $request->scholar_no,
                'head_name'  => 'TUITION FEES'
            ],
            [
                'sibling'    => $sibling,
                'staff'      => $staff,
                'exempt_per' => $percentage_total
            ]
        );
        return redirect()->back()->with('success', 'Student Registred successfully Update');  
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
        $jsonArr = [
            'enquiryno'=>$request->formno,
            'formno'=>$request->formno,
            'enquirydate'=>$request->enquirydate,
            'date_of_birth'=>$request->student_dob,
            'studentname_prefix'=>$request->studentname_prefix,
            'student_name'=>$request->studentname,
            'student_dob'=>$request->student_dob,
            'fathername_prefix'=>$request->fathername_prefix,
            'fathername'=>$request->fathername,
            'fathermobile'=>$request->fathermobile,
            'mothername_prefix'=>$request->mothername_prefix,
            'mothername'=>$request->mothername,
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
            'student_name'=>$request->studentname,
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
		return collect(explode(' ', trim(preg_replace('/\s+/', ' ', $name))))
			->map(function ($part) {
				return ucfirst(strtolower($part));
			})
			->implode(' ');
	}

	
}










