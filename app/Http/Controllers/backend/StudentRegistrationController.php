<?php
namespace App\Http\Controllers\backend;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use DB;
use Hash;
use DataTables;
use App\Http\Requests\StoreFileRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class StudentRegistrationController extends Controller
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

    private function activeStudentRegistrationQuery()
    {
        return DB::connection('dynamic')
            ->table('student_registration')
            ->where(function ($query) {
                $query->where('is_archived', 0)
                    ->orWhereNull('is_archived');
            });
    }

    public function index(Request $request)
    {
        
        if ($request->ajax()) {
  
            $data = DB::connection('dynamic')
            ->table('inquiry_registration')
            ->latest()
            ->get();
  
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
         $classlist = DB::table('classes')->select('class_name')->distinct()->get();
        
        // return view('backend.inquiry');
        return view('backend.inquiry', compact('classlist',));
    }

    public function view(Request $request)
    {
        $mobile_number = $request->post('mobile_number');

        if ($request->ajax()) {
  
            $data = DB::connection('dynamic')
            ->table('call_details') 
            ->select('call_note')
            ->where('mobile_number', $mobile_number)
            ->latest()
            ->get();
  
            return $data;
            
        }
        
        return view('backend.inquiry');
    }

    public function store(Request $request)
    {
        DB::connection('dynamic')->table('call_details')->updateOrInsert(
            ['id' => $request->product_id],
            [
                'mobile_number' => $request->mobile_number,
                'call_tag' => $request->call_tag,
                'call_note' => $request->call_note
            ]
        );       
     
        return response()->json(['success'=>'Call saved successfully.']);
    }

    public function edit($id)
    {
        $product = DB::connection('dynamic')
        ->table('inquiry_registration')
        ->where('id', $id)
        ->first();
        return response()->json($product);
    }
public function downloadcertificate($id)
{
    
    $a = explode('-', $id);
    $b = $a[1];
    $c = $a[0];
    // print_r($a);exit;
    if($c === "aadharProff"){
        $filePath = public_path('uploads/admin/aadharProff/'.$b); 
        return response()->download($filePath, $b );
        // echo $filePath;
    }elseif($c === "BirthCertificate"){
        $filePath = public_path('uploads/admin/BirthCertificate/'.$b); 
        return response()->download($filePath, $b );
    }elseif($c === "TransferCertificate"){
        $filePath = public_path('uploads/admin/TransferCertificate/'.$b); 
        return response()->download($filePath, $b );
    }elseif($c === "AddressProff"){
        $filePath = public_path('uploads/admin/AddressProff/'.$b); 
        return response()->download($filePath, $b );
    }elseif($c === "CastProff"){
        $filePath = public_path('uploads/admin/CastProff/'.$b); 
        return response()->download($filePath, $b );
    }elseif($c === "sssmprof"){
        $filePath = public_path('uploads/admin/sssmprof/'.$b); 
        return response()->download($filePath, $b );
    }elseif($c === "LastReportCard"){
        $filePath = public_path('uploads/admin/LastReportCard/'.$b); 
        return response()->download($filePath, $b );
    }
    // print_r($a); // Debugging output
    exit;
    // Rest of your code
}

    /*List All Inquiry*/
    public function student_registrations()
    {   
        $all_inquiry = $this->activeStudentRegistrationQuery()->where('status','=','r')->orderBy('created_at', 'desc')->orderBy('id', 'desc')->get() ; 
        // return view('backend.student_registrations.index',compact('all_inquiry'));

        $classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();

        return view('backend.student_registrations.index', compact('all_inquiry','classlist'));

    } 
    
    public function curren_year_student_registrations(){
        $currentSchoolYear = session('db_names');
		// print_r($currentSchoolYear);die;
        $all_inquiry = $this->activeStudentRegistrationQuery()->where('status','=','r')->where('session_name', $currentSchoolYear)->get(); 
        return view('backend.student_registrations.currentsessionregistration', compact('all_inquiry'));

    } 

    public function archived_students()
    {
        $all_inquiry = DB::connection('dynamic')
            ->table('student_registration')
            ->where('is_archived', 1)
            ->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();
        $classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();

        return view('backend.student_registrations.archived', compact('all_inquiry', 'classlist'));
    }

    public function archive_student($id)
    {
        DB::connection('dynamic')
            ->table('student_registration')
            ->where('id', $id)
            ->update(['is_archived' => 1, 'updated_at' => now()]);

        return redirect()->route('student-registrations')->with('success', 'Student archived successfully.');
    }

    public function restore_student($id)
    {
        DB::connection('dynamic')
            ->table('student_registration')
            ->where('id', $id)
            ->update(['is_archived' => 0, 'updated_at' => now()]);

        return redirect()->route('archived-students')->with('success', 'Student restored successfully.');
    }

    /*List All Inquiry*/
    public function save_student_registration(Request $request){   //thisone....
        $postData_html = $request->all();

        // echo"<pre>";print_r($request->all());die();

        // if (!empty($postData['bus_facility_start_date'])) {
            
            // echo '<input type="hidden" name="bus_facility_start_date" value="">';
        // }

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
        
            "scholar_no"    => (!empty($postData_html['scholar_no'])) ? $postData_html['scholar_no'] : "" ,
			"registration_date" => (!empty($postData_html['registration_date'])) ? $postData_html['registration_date'] : "",
            "application_for"   => (!empty($postData_html['application_for'])) ? $postData_html['application_for'] : "" ,
            // "application_for"   => "" ,
            "form_number"    => (!empty($postData_html['form_number'])) ? $postData_html['form_number'] : "" ,
            "studentname_prefix" => (!empty($postData_html['studentname_prefix'])) ? $postData_html['studentname_prefix'] : "" ,
            "studentname"   => (!empty($postData_html['studentname'])) ? $postData_html['studentname'] : "" ,
            "received_amount"   => (!empty($postData_html['received_amount'])) ? $postData_html['received_amount'] : "" ,
            "amount"   => (!empty($postData_html['amount'])) ? $postData_html['amount'] : "" ,
            "gender"   => (!empty($postData_html['gender'])) ? $postData_html['gender'] : "" ,
            "student_dob"   => (!empty($postData_html['student_dob'])) ? $postData_html['student_dob'] : "" ,
            "nationality" => "INDIA"  ,
            "session_name"   => (!empty($postData_html['session_name'])) ? $postData_html['session_name'] : "" ,
            "present_address"   => (!empty($postData_html['present_address'])) ? $postData_html['present_address'] : "" ,
            "permanent_address"   => (!empty($postData_html['permanent_address'])) ? $postData_html['permanent_address'] : "" ,
            "phone_number"   => (!empty($postData_html['phone_number'])) ? $postData_html['phone_number'] : "" ,
            "mobile_number"   =>  (!empty($postData_html['mobile_number'])) ? $postData_html['mobile_number'] : "" ,
            "mother_tongue"   => (!empty($postData_html['mother_tongue'])) ? $postData_html['mother_tongue'] : "" ,
            "remarks"   => (!empty($postData_html['remarks'])) ? $postData_html['remarks'] : "" ,
            "vaccaination"   =>  (!empty($postData_html['vaccaination'])) ? $postData_html['vaccaination'] : "" ,
            "SSSMID"   =>  (!empty($postData_html['SSSMID'])) ? $postData_html['SSSMID'] : "" ,
            "family_SSSMID"   =>  (!empty($postData_html['family_SSSMID'])) ? $postData_html['family_SSSMID'] : "" ,
            "is_staff_applied_for_admission"=>(!empty($postData_html['is_staff_applied_for_admission'])) ? $postData_html['is_staff_applied_for_admission'] : "",
            "AadharNo"   => (!empty($postData_html['AadharNo'])) ? $postData_html['AadharNo'] : "" ,
            "student_medical_conserns"   =>  (!empty($postData_html['student_medical_conserns'])) ? $postData_html['student_medical_conserns'] : "" ,
            "present_school_name"   => (!empty($postData_html['present_school_name'])) ? $postData_html['present_school_name'] : "" ,
            "searchfather"   => (!empty($postData_html['searchfather'])) ? $postData_html['searchfather'] : "" ,
            "siblings_namesecond"   =>  (!empty($postData_html['siblings_namesecond'])) ? $postData_html['siblings_namesecond'] : "" ,
            "siblings_section_second"    =>  (!empty($postData_html['siblings_section_second'])) ? $postData_html['siblings_section_second'] : "" ,
            "siblings_bod_second"    => (!empty($postData_html['siblings_bod_second'])) ? $postData_html['siblings_bod_second'] : "" ,
            "driver_name"    => (!empty($postData_html['driver_name'])) ? $postData_html['driver_name'] : "" ,
            "bus_facility_start_date"    =>  (!empty($postData_html['bus_facility_start_date'])) ? $postData_html['bus_facility_start_date'] : "" ,
            "staff_name"    => (!empty($postData_html['staff_name'])) ? $postData_html['staff_name'] : "" ,
            "fathername_prefix" => "Mr."  ,
            "fathername"    => (!empty($postData_html['fathername'])) ? $postData_html['fathername'] : "" ,
            "father_education"    =>  (!empty($postData_html['father_education'])) ? $postData_html['father_education'] : "" ,
            "father_organization"    =>  (!empty($postData_html['father_organization'])) ? $postData_html['father_organization'] : "" ,
            "father_designation"    => (!empty($postData_html['father_designation'])) ? $postData_html['father_designation'] : "" ,
            "father_office_telephone"    => (!empty($postData_html['father_office_telephone'])) ? $postData_html['father_office_telephone'] : "" ,
            "father_email_id"   => (!empty($postData_html['father_email_id'])) ? $postData_html['father_email_id'] : "" ,
            "father_mobile"   => (!empty($postData_html['father_mobile'])) ? $postData_html['father_mobile'] : "" ,
            "fatherSSSMID"   => (!empty($postData_html['fatherSSSMID'])) ? $postData_html['fatherSSSMID'] : "" ,
            "fatherAadharNo"   => (!empty($postData_html['fatherAadharNo'])) ? $postData_html['fatherAadharNo'] : "" ,
            // "father_res_address"   =>$postData_html['father_res_address'],
            "father_emergency_contact"   =>(!empty($postData_html['father_emergency_contact'])) ? $postData_html['father_emergency_contact'] : "" ,
            "mothername_prefix" => "Mrs." ,
            "mothername"   =>(!empty($postData_html['mothername'])) ? $postData_html['mothername'] : "" ,
            "mother_education"   =>(!empty($postData_html['mother_education'])) ? $postData_html['mother_education'] : "" ,
            "mother_organization"   =>(!empty($postData_html['mother_organization'])) ? $postData_html['mother_organization'] : "" ,
            "mother_office_telephone"   =>(!empty($postData_html['mother_office_telephone'])) ? $postData_html['mother_office_telephone'] : "" ,
            "mother_email"   =>(!empty($postData_html['mother_email'])) ? $postData_html['mother_email'] : "" ,
            "mother_mobile"   =>(!empty($postData_html['mother_mobile'])) ? $postData_html['mother_mobile'] : "" ,
            "motherSSSMID"   =>(!empty($postData_html['motherSSSMID'])) ? $postData_html['motherSSSMID'] : "" ,
            "motherAadharNo"   =>(!empty($postData_html['motherAadharNo'])) ? $postData_html['motherAadharNo'] : "" ,
            // "mother_res_address"   =>$postData_html['mother_res_address'],
            "mother_emergency_contact"   => (!empty($postData_html['mother_emergency_contact'])) ? $postData_html['mother_emergency_contact'] : "" ,
            "guardian_name"   => (!empty($postData_html['guardian_name'])) ? $postData_html['guardian_name'] : "" ,
            
            "guardian_office_telephone"   =>(!empty($postData_html['guardian_office_telephone'])) ? $postData_html['guardian_office_telephone'] : "" ,
            "guardian_email_id"   =>(!empty($postData_html['guardian_email_id'])) ? $postData_html['guardian_email_id'] : "" ,
            "guardian_mobile"   =>(!empty($postData_html['guardian_mobile'])) ? $postData_html['guardian_mobile'] : "" ,
            "guardian_permanent_address"   =>(!empty($postData_html['guardian_permanent_address'])) ? $postData_html['guardian_permanent_address'] : "" ,
            "guardian_emergency_contact"   =>(!empty($postData_html['guardian_emergency_contact'])) ? $postData_html['guardian_emergency_contact'] : "" ,
            "guardian_relation"   =>(!empty($postData_html['guardian_relation'])) ? $postData_html['guardian_relation'] : "" ,

            "bankName"   =>(!empty($postData_html['bankName'])) ? $postData_html['bankName'] : "" ,
            "branchName"   =>(!empty($postData_html['branchName'])) ? $postData_html['branchName'] : "" ,
            "account_number" =>(!empty($postData_html['account_number'])) ? $postData_html['account_number'] : "" ,
            "ifsc_code"   =>(!empty($postData_html['ifsc_code'])) ? $postData_html['ifsc_code'] : "" ,


            "iid"   =>(!empty($postData_html['iid'])) ? $postData_html['iid'] : "" ,
            "submit"   =>"submit",
            // editonly.. start..........
            "bus_facility_end_date" => "",  // editonly
            "id" => "",          // editonly
            // "classname" => $postData_html['classname'],    // editonly
            "batch" => (!empty($postData_html['batch'])) ? $postData_html['batch'] : "" ,  // editonly
            "father_dob" => (!empty($postData_html['father_dob'])) ? $postData_html['father_dob'] : "" ,    // editonly
            "mother_dob" =>(!empty($postData_html['mother_dob'])) ? $postData_html['mother_dob'] : "" ,    // editonly
            "mother_ocupation" => (!empty($postData_html['mother_ocupation'])) ? $postData_html['mother_ocupation'] : "" ,   //editonly     
            "local_guardian" => (!empty($postData_html['local_guardian'])) ? $postData_html['local_guardian'] : "" ,  //editonly
            "local_address" =>(!empty($postData_html['local_address'])) ? $postData_html['local_address'] : "" , ////editonly
            "guradian_phone"  =>(!empty($postData_html['guradian_phone'])) ? $postData_html['guradian_phone'] : "" , ////editonly
            "guradian_mobile" => (!empty($postData_html['guradian_mobile'])) ? $postData_html['guradian_mobile'] : "" , ////editonly
            "guradian_parent_category" =>(!empty($postData_html['guradian_parent_category'])) ? $postData_html['guradian_parent_category'] : "" ,//editonly
            "guradian_new_category" => (!empty($postData_html['guradian_new_category'])) ? $postData_html['guradian_new_category'] : "" , //editonly
            "guradian_new_house" => (!empty($postData_html['guradian_new_house'])) ? $postData_html['guradian_new_house'] : "" , //editonly
    

            // "category" => $postData_html['category'],
            "category" => $request->category,
            "religion" => $request->religion,
            "student_caste" => $request->student_caste,
            "classname" => $request->classname,
            "required_school_transport"    =>$request->required_school_transport,
            "section_name" => (!empty($postData_html['section_name'])) ? $postData_html['section_name'] : "" ,  // editonly

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
            "BirthCertificate" => $BirthCertificate,
            "TransferCertificate" => $TransferCertificate,
            "AddressProff" => $AddressProff,
            "CastProff"=>$CastProff,
            "aadharProff"=>$aadharProff,
            "bankbProff"=>$bankbProff,
            "StuProf"=>$StuProf,
            "sssmprof"=>$sssmprof,
            "salaryprof"=>$salaryprof,
            "LastReportCard" => $LastReportCard

        ];

        if (!empty($postData['bus_facility_end_date'])) {
            echo '<input type="hidden" name="bus_facility_end_date" value="' . $postData['bus_facility_end_date'] . '">';
        }
        if (!empty($postData['id'])) {
            echo '<input type="hidden" name="id" value="' . $postData['id'] . '">';
        }
        if (!empty($postData['classname'])) {
            echo '<input type="hidden" name="classname" value="' . $postData['classname'] . '">';
        }

        if (!empty($postData['section_name'])) {
            echo '<input type="hidden" name="section_name" value="' . $postData['section_name'] . '">';
        }

        if (!empty($postData['father_dob'])) {
            echo '<input type="hidden" name="father_dob" value="' . $postData['father_dob'] . '">';
        }

        if (!empty($postData['mother_dob'])) {
            echo '<input type="hidden" name="mother_dob" value="' . $postData['mother_dob'] . '">';
        }

        if (!empty($postData['mother_ocupation'])) {
            echo '<input type="hidden" name="mother_ocupation" value="' . $postData['mother_ocupation'] . '">';
        }

        if (!empty($postData['local_guardian'])) {
            echo '<input type="hidden" name="local_guardian" value="' . $postData['local_guardian'] . '">';
        }

        if (!empty($postData['local_address'])) {
            echo '<input type="hidden" name="local_address" value="' . $postData['local_address'] . '">';
        }

        if (!empty($postData['guradian_phone'])) {
            echo '<input type="hidden" name="guradian_phone" value="' . $postData['guradian_phone'] . '">';
        }

        if (!empty($postData['guradian_mobile'])) {
            echo '<input type="hidden" name="guradian_mobile" value="' . $postData['guradian_mobile'] . '">';
        }

        if (!empty($postData['guradian_parent_category'])) {
            echo '<input type="hidden" name="guradian_parent_category" value="' . $postData['guradian_parent_category'] . '">';
        }

        if (!empty($postData['guradian_new_category'])) {
            echo '<input type="hidden" name="guradian_new_category" value="' . $postData['guradian_new_category'] . '">';
        }

        if (!empty($postData['guradian_new_house'])) {
            echo '<input type="hidden" name="guradian_new_house" value="' . $postData['guradian_new_house'] . '">';
        }




        // unset($postData['application_for']);
        // unset($postData['form_number']);
        // unset($postData['date_of_birth']);
        // unset($postData['class_name']);

        // unset($postData['_token']);

        $files = [];        
        $postData['files'] = $files;

        // $postData['staff_name'] = $request->staff_name;
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
            'inq_mode'=>'on',
            'status'=>'r',
            'type'=>'s',
            // 'json_str' => $jsonStr 
            'json_str'=>json_encode($postData),
			'registration_date'=>$request->registration_date,
            'is_archived'=>0,
        ];
        DB::connection('dynamic')->table('student_registration')->insert($insertArr); 
		
		/**DB::connection('dynamic')->table('student_fee_exemption')->insert([
            'scholar_no'     => $request->scholar_no,
            'head_name'      => $exemption->head_name, 
            'exemption_type' => $exemption->sibling, // Correct column from fee_exemption_master
            'percentage'     => $exemption->percentage,
            'remark'         => $request->remark,
        ]);**/

        DB::connection('dynamic')->table("inquiry_registration")->where('id', $request->iid)->update(['is_delete' => 1]);
        $uid = DB::connection('dynamic')->table('student_registration')->get()->last()->id;
        DB::connection('dynamic')->table('model_has_roles')->insert(['role_id' => 2,'model_id' => $uid]);
       return redirect()->route('registrationviewlist',['id'=>$uid])->with('success', 'Student Successfully Registred With Scholar Number - ' .$request->scholar_no );
    
    }


    /*getDataByFormNumber*/
    public function getDataByFormNumber(Request $request)
    {           
        $form_number = $request->form_number;
       
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
         $da=json_decode($inquiry_data->json_str);
         $statename= $da->state;
         $statevalue = DB::connection('dynamic')
         ->table('states')
         ->where('name', $statename)
         ->get();
         foreach($statevalue as $v){ $statevalue= $v->id; }
         
        $passArr = ['inq_data'=>$inquiry_data,'inq_str_data'=>json_decode($inquiry_data->json_str),'statevalue'=>$statevalue];
 
        return $passArr;
    }

    public function getDataByFormNumberstudent_registration(Request $request)
    {       
            
        $form_number = $request->form_number;
        $inquiry_data = DB::connection('dynamic')
         ->table('inquiry_registration')
         ->select('*')
         ->where('status', 'i')
         ->where(function($query) use ($form_number) {
             $query->where('form_number', $form_number)
                   ->orWhere('student_name', $form_number)
                   ->orWhereJsonContains('json_str->fathername', $form_number)
                   ->orWhereJsonContains('json_str->fathermobile', $form_number)
                   ->orWhereJsonContains('json_str->mothermobile', $form_number)
                   ->orWhereJsonContains('json_str->mothername', $form_number);
         })
         ->first();
         $da=json_decode($inquiry_data->json_str);
         $statename= $da->state;
         $statevalue = DB::connection('dynamic')
         ->table('states')
         ->where('name', $statename)
         ->get();
         foreach($statevalue as $v){ $statevalue= $v->id; }
         
        $passArr = ['inq_data'=>$inquiry_data,'inq_str_data'=>json_decode($inquiry_data->json_str),'statevalue'=>$statevalue];
 
        return $passArr;
    }
    

    /*Edit Inquiry*/
    public function inquiry_edit($id)
    {   
        $inquiry_data = $inquiry = DB::connection('dynamic')
        ->table('inquiry_registration')
        ->where('id', $id)
        ->first();
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

        $inquiry_data = DB::connection('dynamic')
        ->table('inquiry_registration')
        ->where('id', $update_id)
        ->update($dataForUpdate);

        return redirect('inquiry-data-show');
    }

    /*Add registration*/
    public function add_student_registrations(){
        /*r status for registration*/
        $inqArr = DB::connection('dynamic')->table('inquiry_registration')->where('save_status','=','Form Selected')->where('status','=','i')->where('is_delete','=','0')->get();

        $stutdentsArr = $this->activeStudentRegistrationQuery()->get(); 
        $classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        $drivername = DB::connection('dynamic')->table('busstaff')->where('role', 'Driver')->get();
        $caste = DB::connection('dynamic')->table('caste_name')->get();
        $uid = DB::connection('dynamic')
        ->table('student_registration')
        ->where(function ($query) {
            $query->where('is_archived', 0)
                ->orWhereNull('is_archived');
        })
        ->whereRaw('scholar_no REGEXP "^[0-9]+$"')
        ->max('scholar_no');
    
        $uid = $uid +1;
        return view('backend.student_registrations.add', compact('caste','inqArr','drivername','stutdentsArr','classlist','uid'));
    }

    public function selection_process(){
         $all_inquiry = DB::connection('dynamic')->table('inquiry_registration')->orderBy('id', 'desc')->where("status","=", "i")->get();
         $totalPending = DB::connection('dynamic')->table('inquiry_registration')->select('*')->where('status', '=', 'i')->whereJsonContains('json_str->folloupdate_status', 'Pending')->get();
         $totalCancel = DB::connection('dynamic')->table('inquiry_registration')->select('*')->where('status', '=', 'i')->whereJsonContains('json_str->folloupdate_status', 'Cancel')->get();
         $totaladmission = DB::connection('dynamic')->table('student_registration')->where("status","=", "r")->get();
        return view('backend.student_registrations.selection_process',compact('all_inquiry','totalPending','totalCancel','totaladmission'));
    }


    
    public function save_selection_process(Request $request){
        $id = $request->id;
        $dataForUpdate = [
            'save_status'=>$request->save_status,
        ];
        $inquiry_data = DB::connection('dynamic')
        ->table('inquiry_registration')
        ->where('id', $id)
        ->update($dataForUpdate);
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
        $classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        return view('backend.student_registrations.selection_process',compact('all_inquiry','jsonArr', 'classlist'));
    }

    
	public function student_registration_filer(Request $request)
	{
		$session_name = $request->post('session_name');
		$form_number = $request->post('form_number');
		$save_status = $request->post('save_status');
		$class_name = $request->post('classname');
		$studentname = $request->post('student_name');
		$application_for = $request->post('application_for'); // <-- new

		$jsonArr = [
			'session_name' => $session_name,
			'student_name' => $studentname,
			'serial_number' => $request->post('serial_number'),
			'class_name' => $request->post('classname'),
			'form_number' => $form_number,
			'application_for' => $application_for, // <-- add to jsonArr
		];

		// Start query builder
		if(!empty($session_name)){
			$records1 = $this->activeStudentRegistrationQuery()
						 ->where("session_name", $session_name)
						 ->orderBy('created_at', 'desc')
                         ->orderBy('id', 'desc');
		} else {
			$records1 = $this->activeStudentRegistrationQuery()->orderBy('created_at', 'desc')->orderBy('id', 'desc');
		}

		if(!empty($form_number)){
			$records1->where("form_number", $form_number);
		}
		if(!empty($save_status)){
			$records1->where("save_status", $save_status);
		}
		if(!empty($class_name)){
			$records1->where("class_name", $class_name);
		}
		if(!empty($studentname)){
			$records1->where("student_name", $studentname);
		}
		if(!empty($application_for)){
			$records1->where("application_for", $application_for);
		}

		$all_inquiry = $records1->get();
		$classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
		return view('backend.student_registrations.index', compact('all_inquiry', 'jsonArr', 'classlist'));
	}

	
		public function showPhotoUploadForm()
	{
		return view('backend.student_registrations.bulk_photo_import');
	}

	public function uploadStudentPhotos(Request $request)
	{
		$request->validate([
			'photos_zip' => 'required|mimes:zip|max:10240', // 10MB max
		]);

		$zip = new ZipArchive;
		$file = $request->file('photos_zip');
		$filename = time() . '_photos.zip';
		$destinationPath = storage_path('app/temp_photos');

		if (!File::exists($destinationPath)) {
			File::makeDirectory($destinationPath, 0755, true);
		}

		$file->move($destinationPath, $filename);
		$zipPath = $destinationPath . '/' . $filename;
		$extractPath = $destinationPath . '/extracted_' . time();

		if ($zip->open($zipPath) === TRUE) {
			$zip->extractTo($extractPath);
			$zip->close();
		} else {
			return back()->with('error', 'Unable to open ZIP file.');
		}

		$files = File::files($extractPath);
		$imported = 0;
		$skipped = [];

		foreach ($files as $photo) {
			$originalName = pathinfo($photo->getFilename(), PATHINFO_FILENAME); // scholar_no
			$extension = $photo->getExtension();
			$newFileName = $originalName . '.' . $extension;

			$student = DB::table('student_registration')->where('scholar_no', $originalName)->first();

			if ($student) {
				$storagePath = 'public/student_photos/' . $newFileName;
				Storage::put($storagePath, File::get($photo));

				DB::table('student_registration')
				  ->where('scholar_no', $originalName)
				  ->update([
					  'student_image_url' => 'storage/student_photos/' . $newFileName,
				  ]);

				$imported++;
			} else {
				$skipped[] = $originalName;
			}
		}

		File::deleteDirectory($extractPath);
		unlink($zipPath);

		return back()
			->with('success', "$imported photo(s) imported successfully.")
			->with('warning', count($skipped) ? 'Skipped Scholar Nos: ' . implode(', ', $skipped) : null);
	}


    
}
