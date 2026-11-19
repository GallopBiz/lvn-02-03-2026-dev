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
use App\Models\HrmsEmployee;

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
                $db_name = session('db_names') ?? session('selectedYear') ?? env('DB_DATABASE', '2026_2027');
            }
            
        }

        $dynamicConnectionName = 'dynamic';
        $dynamicConfig = Config::get("database.connections.{$dynamicConnectionName}");
        if (($dynamicConfig['database'] ?? null) !== $db_name) {
            $dynamicConfig['database'] = $db_name;
            Config::set('database.connections.dynamic', $dynamicConfig);
            DB::purge('dynamic');
            DB::reconnect('dynamic');
        }

    }

    private function activeStudentRegistrationQuery()
    {
        $query = DB::connection('dynamic')
            ->table('student_registration')
            ->where(function ($q) {
                $q->where('is_archived', 0)
                    ->orWhereNull('is_archived');
            })
            ->where(function ($q) {
                $q->whereNull('status')
                    ->orWhere('status', '!=', 't');
            });

        if (Schema::connection('dynamic')->hasColumn('student_registration', 'next_year')) {
            $query->where(function ($q) {
                $q->where('next_year', 0)
                    ->orWhereNull('next_year');
            });
        }

        if (Schema::connection('dynamic')->hasColumn('student_registration', 'transfer_status')) {
            $query->where(function ($q) {
                $q->whereNull('transfer_status')
                    ->orWhere('transfer_status', '!=', 'transferred');
            });
        }

        return $query;
    }

    private function nextYearStudentRegistrationQuery()
    {
        $query = DB::connection('dynamic')
            ->table('student_registration')
            ->where(function ($q) {
                $q->where('is_archived', 0)
                    ->orWhereNull('is_archived');
            })
            ->where(function ($q) {
                $q->whereNull('status')
                    ->orWhere('status', '!=', 't');
            })
            ->where('next_year', 1);

        if (Schema::connection('dynamic')->hasColumn('student_registration', 'transfer_status')) {
            $query->where(function ($q) {
                $q->whereNull('transfer_status')
                    ->orWhere('transfer_status', '!=', 'transferred');
            });
        }

        return $query;
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
    public function student_registrations(Request $request = null)
    {   
        $activeTab = ($request && $request->filled('tab')) ? $request->get('tab') : request('tab', 'current');
        if ($activeTab === 'next_year') {
            $all_inquiry = $this->nextYearStudentRegistrationQuery()->where('status', '=', 'r')->orderBy('created_at', 'desc')->orderBy('id', 'desc')->get();
        } else {
            $all_inquiry = $this->activeStudentRegistrationQuery()->where('status','=','r')->orderBy('created_at', 'desc')->orderBy('id', 'desc')->get();
        }

        $classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        $archivedCount = DB::connection('dynamic')->table('student_registration')->where('is_archived', 1)->count();
        $currentCount = $this->activeStudentRegistrationQuery()->where('status', '=', 'r')->count();
        $nextYearCount = $this->nextYearStudentRegistrationQuery()->where('status', '=', 'r')->count();

        return view('backend.student_registrations.index', compact('all_inquiry','classlist', 'archivedCount', 'activeTab', 'currentCount', 'nextYearCount'));
    }

    public function next_year_student_registrations(Request $request)
    {
        $request->merge(['tab' => 'next_year']);
        return $this->student_registrations($request);
    } 
    
    public function curren_year_student_registrations(){
        $currentSchoolYear = session('db_names');
		// print_r($currentSchoolYear);die;
        $all_inquiry = $this->activeStudentRegistrationQuery()->where('status','=','r')->where('session_name', $currentSchoolYear)->get(); 
        $archivedCount = DB::connection('dynamic')->table('student_registration')->where('is_archived', 1)->where('session_name', $currentSchoolYear)->count();
        return view('backend.student_registrations.currentsessionregistration', compact('all_inquiry', 'archivedCount'));

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
			"registration_date" => (!empty($postData_html['registration_date'])) ? $postData_html['registration_date'] : date('Y-m-d'),
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
            "apaar_id"   => (!empty($postData_html['apaar_id'])) ? $postData_html['apaar_id'] : "" ,
            "pen_no"     => (!empty($postData_html['pen_no'])) ? $postData_html['pen_no'] : "" ,
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

        $selectedClass = $request->classname;
        if (strcasecmp($selectedClass, 'kg1') === 0) $selectedClass = 'KG1';
        if (strcasecmp($selectedClass, 'kg2') === 0) $selectedClass = 'KG2';

        $isNextYear = !empty($request->next_year) && ($request->next_year == 1 || $request->next_year === '1');
        $inquiryRecord = null;
        if (!empty($request->iid) || !empty($request->inquiry_id)) {
            $inqLookupId = !empty($request->iid) ? $request->iid : $request->inquiry_id;
            $inquiryRecord = DB::connection('dynamic')->table('inquiry_registration')->where('id', $inqLookupId)->first();
        }
        if (!$isNextYear && $inquiryRecord && (!empty($inquiryRecord->next_year) && ($inquiryRecord->next_year == 1 || $inquiryRecord->next_year === '1'))) {
            $isNextYear = true;
        }

        $selectedSection = $request->section_name ?? $request->section ?? null;
        $applicableClasses = [
            'nursery', 'kg1', 'kg2',
            '01', '02', '03', '04', '05', '06', '07', '08', '09', '10',
            '1', '2', '3', '4', '5', '6', '7', '8', '9'
        ];
        $isApplicable = in_array(strtolower(trim($selectedClass ?? '')), $applicableClasses, true);

        if ($isApplicable) {
            if ($isNextYear) {
                // Next Year new admissions are temporarily held in Section D until session transition
                if (empty($selectedSection)) {
                    $selectedSection = 'D';
                }
            } else {
                // New Admissions for current session: Apply Round Robin based on current section strength
                // if section is not explicitly chosen or is Section D
                if (empty($selectedSection) || $selectedSection === 'D') {
                    $regularSections = ['Aryabhatta', 'Kautilya', 'Ramanujan'];
                    $counts = [
                        'Aryabhatta' => 0,
                        'Kautilya'   => 0,
                        'Ramanujan'  => 0,
                    ];

                    $existingStudents = DB::connection('dynamic')->table('student_registration')
                        ->where('class_name', $selectedClass)
                        ->where(function ($q) {
                            $q->where('is_archived', 0)
                                ->orWhereNull('is_archived');
                        })
                        ->where(function ($q) {
                            $q->whereNull('status')->orWhereNotIn('status', ['t', 'archived']);
                        })
                        ->where(function ($q) {
                            $q->where('next_year', 0)
                                ->orWhereNull('next_year');
                        })
                        ->get();

                    foreach ($existingStudents as $es) {
                        $sName = trim((string)$es->section_name);
                        foreach ($regularSections as $rSec) {
                            if (strcasecmp($sName, $rSec) === 0) {
                                $counts[$rSec]++;
                                break;
                            }
                        }
                    }

                    $bestSection = 'Aryabhatta';
                    $minCount = $counts['Aryabhatta'];
                    foreach ($regularSections as $candidate) {
                        if ($counts[$candidate] < $minCount) {
                            $minCount = $counts[$candidate];
                            $bestSection = $candidate;
                        }
                    }

                    $selectedSection = $bestSection;
                }
            }
        }

        $isSeniorClass = in_array(trim((string)$selectedClass), ['11', '12'], true);
        if ($isSeniorClass) {
            $streamId = $request->stream_id ?? null;
            if (!empty($streamId) && (empty($selectedSection) || $selectedSection === 'D')) {
                $streamRecord = DB::connection('dynamic')->table('streams')->where('id', $streamId)->first();
                $streamName = $streamRecord ? strtoupper(trim($streamRecord->streams)) : '';
                if ($streamName === 'SCIENCE' || (int)$streamId === 3) {
                    $selectedSection = 'Kautilya';
                } elseif (in_array($streamName, ['COMMERCE', 'HUMANITIES'], true) || in_array((int)$streamId, [1, 2], true)) {
                    $selectedSection = 'Ramanujan';
                }
            }
        }

        $classId = null;
        if (!empty($selectedClass)) {
            $classQuery = DB::connection('dynamic')->table('classes')->where('class_name', $selectedClass)->where('is_delete', 0);
            if (!empty($selectedSection)) {
                $matchedClass = (clone $classQuery)->where('section_name', $selectedSection)->first();
                if ($matchedClass) {
                    $classId = $matchedClass->id;
                }
            }
            if (empty($classId)) {
                $firstClass = (clone $classQuery)->first();
                if ($firstClass) {
                    $classId = $firstClass->id;
                }
            }
        }

        $calcNextSession = function($sess) {
            $parts = preg_split('/[_-]/', trim((string)$sess));
            if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                return ((int)$parts[0] + 1) . '_' . ((int)$parts[1] + 1);
            }
            return $sess;
        };

        $currentSession = $request->session_name ?: (session('db_names') ?: ($_COOKIE['selectedYear'] ?? '2025_2026'));
        $admissionSession = $isNextYear ? $calcNextSession($currentSession) : $currentSession;

        $nextYearSection = $isNextYear ? ($selectedSection ?: 'D') : null;
        $nextYearStream = ($isNextYear && $isSeniorClass) ? ($request->stream_id ?? null) : null;
        $nextYearComb = ($isNextYear && $isSeniorClass) ? ($request->combination_id ?? null) : null;

        $postData['classname'] = $selectedClass;
        $postData['section_name'] = $selectedSection;
        $postData['class_id'] = $classId;
        $postData['next_year'] = $isNextYear ? 1 : 0;
        $postData['next_year_section'] = $nextYearSection;
        $postData['next_year_stream'] = $nextYearStream;
        $postData['next_year_subject_combination'] = $nextYearComb;
        $postData['admission_session'] = $admissionSession;
        $postData['intended_session'] = $admissionSession;
        if (empty($postData['batch']) || ($isNextYear && $postData['batch'] === $currentSession)) {
            $postData['batch'] = $admissionSession;
        }

        $isSeniorClass = in_array(trim((string)$selectedClass), ['11', '12'], true);
        if ($isSeniorClass) {
            $postData['stream_id'] = $request->stream_id ?? null;
            $postData['combination_id'] = $request->combination_id ?? null;
        }

        $insertArr  = [
            'application_for'=>$request->application_for,
            'form_number'=>$request->form_number,
            'date_of_birth'=>$request->student_dob,
            'class_name'=>$selectedClass,
            'section_name'=>$selectedSection,
            'class_id'=>$classId,
            'student_name'=>$request->studentname,
            'driver'=>$request->driver_name ?? '',
            'staff_name' => (!empty($request->staff_name) && is_array($request->staff_name)) ? ($request->staff_name[0] ?? '') : (!empty($request->staff_name) ? $request->staff_name : ''),
            'mobile_number' => $request->mobile_number ?? '',
            'phone_number' => $request->phone_number ?? '',
    	    'scholar_no'=>$request->scholar_no,
            'session_name'=>$request->session_name,
            'password'=>Hash::make("LVN@123"),
           // 'student_name'=>$request->student_name,
            'inq_mode'=>'on',
            'status'=>'r',
            'type'=>'s',
            'next_year' => $isNextYear ? 1 : 0,
            'next_year_section' => $nextYearSection,
            'next_year_stream' => $nextYearStream,
            'next_year_subject_combination' => $nextYearComb,
            // 'json_str' => $jsonStr 
            'json_str'=>json_encode($postData),
			'registration_date'=> !empty($request->registration_date) ? $request->registration_date : date('Y-m-d'),
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

        $inqDeleteId = !empty($request->iid) ? $request->iid : (!empty($request->inquiry_id) ? $request->inquiry_id : null);
        if (!empty($inqDeleteId)) {
            DB::connection('dynamic')->table("inquiry_registration")->where('id', $inqDeleteId)->update(['is_delete' => 1]);
        }
        $uid = DB::connection('dynamic')->table('student_registration')->get()->last()->id;
        DB::connection('dynamic')->table('model_has_roles')->insert(['role_id' => 2,'model_id' => $uid]);

        if ($isSeniorClass && !empty($request->combination_id)) {
            DB::connection('dynamic')->table('subject_assign_student')->updateOrInsert(
                [
                    'students_details' => $uid,
                ],
                [
                    'class_name' => $selectedClass,
                    'section_name' => $selectedSection,
                    'assign_this_combtoall' => $request->combination_id,
                    'is_delete' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

       return redirect()->route('student-registrations')->with('success', 'Student Successfully Registred With Scholar Number - ' .$request->scholar_no );
    
    }


    /*getDataByFormNumber*/
    public function getDataByFormNumber(Request $request)
    {           
        $form_number = $request->form_number;
       
        $preEnquiries = DB::connection('dynamic')->table('inquiry_registration')
            ->where('status', 'p');
        $inquiry_data = (clone $preEnquiries)
            ->where(function ($query) use ($form_number) {
                $query->where('form_number', $form_number)
                    ->orWhere('student_name', $form_number);
            })
            ->first();

        if (!$inquiry_data) {
            $inquiry_data = (clone $preEnquiries)
                ->where(function ($query) use ($form_number) {
                    $query->whereJsonContains('json_str->formno', $form_number)
                        ->orWhereJsonContains('json_str->fathername', $form_number)
                        ->orWhereJsonContains('json_str->fathermobile', $form_number)
                        ->orWhereJsonContains('json_str->mothermobile', $form_number)
                        ->orWhereJsonContains('json_str->mothername', $form_number);
                })
                ->first();
        }

        if (!$inquiry_data) {
            return ['inq_data' => null, 'inq_str_data' => null, 'statevalue' => null];
        }

        $inquiryJson = json_decode($inquiry_data->json_str);
        $statevalue = DB::connection('dynamic')
            ->table('states')
            ->where('name', $inquiryJson->state ?? null)
            ->value('id');
         
        $passArr = [
            'inq_data' => $inquiry_data,
            'inq_str_data' => $inquiryJson,
            'statevalue' => $statevalue,
        ];
 
        return $passArr;
    }

    public function getDataByFormNumberstudent_registration(Request $request)
    {       
        $form_number = $request->form_number;
        $inquiry_id = $request->inquiry_id ?? $request->id;

        $inquiryQuery = DB::connection('dynamic')
            ->table('inquiry_registration')
            ->select('*')
            ->where('status', 'i')
            ->where(function ($q) {
                $q->where('is_delete', 0)
                  ->orWhereNull('is_delete');
            });

        if (!empty($inquiry_id)) {
            $inquiry_data = (clone $inquiryQuery)->where('id', $inquiry_id)->first();
        } else {
            $inquiry_data = (clone $inquiryQuery)
                ->where(function($query) use ($form_number) {
                    $query->where('form_number', $form_number)
                          ->orWhere('student_name', $form_number)
                          ->orWhereJsonContains('json_str->fathername', $form_number)
                          ->orWhereJsonContains('json_str->fathermobile', $form_number)
                          ->orWhereJsonContains('json_str->mothermobile', $form_number)
                          ->orWhereJsonContains('json_str->mothername', $form_number);
                })
                ->orderBy('id', 'desc')
                ->first();
        }

        // Fallback to latest inquiry matching id or form_number only if no active non-deleted record exists
        if (!$inquiry_data) {
            $inquiry_data = DB::connection('dynamic')
                ->table('inquiry_registration')
                ->select('*')
                ->where('status', 'i')
                ->where(function($query) use ($form_number, $inquiry_id) {
                    if (!empty($inquiry_id)) {
                        $query->where('id', $inquiry_id);
                    } else {
                        $query->where('form_number', $form_number)
                              ->orWhere('student_name', $form_number)
                              ->orWhereJsonContains('json_str->fathername', $form_number)
                              ->orWhereJsonContains('json_str->fathermobile', $form_number)
                              ->orWhereJsonContains('json_str->mothermobile', $form_number)
                              ->orWhereJsonContains('json_str->mothername', $form_number);
                    }
                })
                ->orderBy('id', 'desc')
                ->first();
        }

        if (!$inquiry_data) {
            return ['inq_data' => null, 'inq_str_data' => null, 'statevalue' => null, 'next_year' => 0];
        }

        $da = !empty($inquiry_data->json_str) ? json_decode($inquiry_data->json_str) : null;
        $statename = $da->state ?? null;
        $statevalue = null;
        if (!empty($statename)) {
            $stateRow = DB::connection('dynamic')
                ->table('states')
                ->where('name', $statename)
                ->first();
            $statevalue = $stateRow ? $stateRow->id : null;
        }

        if (!empty($inquiry_data->class_name)) {
            if (strcasecmp($inquiry_data->class_name, 'kg1') === 0) $inquiry_data->class_name = 'KG1';
            if (strcasecmp($inquiry_data->class_name, 'kg2') === 0) $inquiry_data->class_name = 'KG2';
        }

        $nextYear = (!empty($inquiry_data->next_year) && ($inquiry_data->next_year == 1 || $inquiry_data->next_year === '1')) ? 1 : 0;
        if (!$nextYear && !empty($da->next_year) && ($da->next_year == 1 || $da->next_year === '1')) {
            $nextYear = 1;
        }
         
        $passArr = [
            'inq_data' => $inquiry_data,
            'inq_str_data' => $da,
            'statevalue' => $statevalue,
            'next_year' => $nextYear,
        ];
 
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
        $inqArr = DB::connection('dynamic')->table('inquiry_registration')->where('save_status','=','Form Selected')->where('status','=','i')->where('is_delete','=','0')->orderBy('id', 'desc')->get();

        $stutdentsArr = $this->activeStudentRegistrationQuery()->get(); 
        $preOrder = ['Nursery', 'KG1', 'KG2', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        $classlist = DB::connection('dynamic')->table('classes')
            ->whereNotNull('class_name')
            ->where('is_delete', 0)
            ->select('class_name')
            ->distinct()
            ->get()
            ->sort(function ($a, $b) use ($preOrder) {
                $posA = array_search($a->class_name, $preOrder);
                $posB = array_search($b->class_name, $preOrder);
                if ($posA !== false && $posB !== false) return $posA <=> $posB;
                if ($posA !== false) return -1;
                if ($posB !== false) return 1;
                return strnatcasecmp($a->class_name, $b->class_name);
            })
            ->values();
        if (Schema::connection('dynamic')->hasTable('hrms_employees')) {
            $drivername = HrmsEmployee::query()
                ->selectRaw("id, first_name, last_name, TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) as ename, contact_number")
                ->whereHas('position', function ($query) {
                    $query->whereRaw('LOWER(position_name) = ?', ['driver']);
                })
                ->where('employee_status', 'active')
                ->orderByRaw("LOWER(TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')))) ASC")
                ->get();

            $staffList = HrmsEmployee::query()
                ->selectRaw("id, first_name, last_name, TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) as full_name, contact_number")
                ->where('employee_status', 'active')
                ->orderByRaw("LOWER(TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')))) ASC")
                ->get();
        } else {
            $drivername = DB::connection('dynamic')->table('busstaff')->where('role', 'Driver')->where('is_delete', 0)->get();
            $staffList = collect([]);
        }
        $caste = DB::connection('dynamic')->table('caste_name')->orderByRaw('LOWER(TRIM(caste_name)) ASC')->get();
        $uid = DB::connection('dynamic')
        ->table('student_registration')
        ->where(function ($query) {
            $query->where('is_archived', 0)
                ->orWhereNull('is_archived');
        })
        ->whereRaw('scholar_no REGEXP "^[0-9]+$"')
        ->max('scholar_no');
    
        $uid = $uid +1;
        return view('backend.student_registrations.add', compact('caste','inqArr','drivername','stutdentsArr','classlist','uid','staffList'));
    }

    public function getClassSectionStrength(Request $request)
    {
        $className = trim($request->input('class_name', ''));
        if (empty($className)) {
            return response()->json([
                'success' => false,
                'message' => 'Class name is required',
            ], 400);
        }

        if (strcasecmp($className, 'kg1') === 0) $className = 'KG1';
        if (strcasecmp($className, 'kg2') === 0) $className = 'KG2';

        $applicableClasses = [
            'nursery', 'kg1', 'kg2',
            '01', '02', '03', '04', '05', '06', '07', '08', '09', '10',
            '1', '2', '3', '4', '5', '6', '7', '8', '9'
        ];
        $isApplicable = in_array(strtolower($className), $applicableClasses, true);

        $students = DB::connection('dynamic')->table('student_registration')
            ->where(function ($query) {
                $query->where('is_archived', 0)->orWhereNull('is_archived');
            })
            ->where(function ($query) {
                $query->where('next_year', 0)->orWhereNull('next_year');
            })
            ->where(function ($query) {
                $query->whereNull('status')->orWhere('status', '!=', 't');
            })
            ->where(function ($query) use ($className) {
                $query->where('class_name', $className);
                if (in_array(strtoupper($className), ['KG1', 'KG2'])) {
                    $query->orWhere('class_name', ucfirst(strtolower($className)));
                }
            })
            ->get(['id', 'section_name', 'class_id']);

        $counts = [
            'Aryabhatta' => 0,
            'Kautilya' => 0,
            'Ramanujan' => 0,
            'D' => 0,
        ];

        foreach ($students as $s) {
            $sec = trim($s->section_name ?? '');
            if (strcasecmp($sec, 'Aryabhatta') === 0) {
                $counts['Aryabhatta']++;
            } elseif (strcasecmp($sec, 'Kautilya') === 0) {
                $counts['Kautilya']++;
            } elseif (strcasecmp($sec, 'Ramanujan') === 0) {
                $counts['Ramanujan']++;
            } else {
                $counts['D']++;
            }
        }

        return response()->json([
            'success' => true,
            'class_name' => $className,
            'is_applicable' => $isApplicable,
            'sections' => [
                ['section' => 'Aryabhatta', 'strength' => $counts['Aryabhatta']],
                ['section' => 'Kautilya', 'strength' => $counts['Kautilya']],
                ['section' => 'Ramanujan', 'strength' => $counts['Ramanujan']],
                ['section' => 'D', 'strength' => $counts['D'], 'label' => 'D (Temporary)'],
            ],
            'total' => array_sum($counts),
        ]);
    }

    public function getClassStreams(Request $request)
    {
        $dbName = session('db_names') ?? request()->cookie('db_names') ?? request()->input('db_names');
        if (!empty($dbName)) {
            Config::set('database.connections.dynamic.database', $dbName);
            DB::purge('dynamic');
        }

        $className = trim((string) $request->input('class_name', ''));
        if (!in_array($className, ['11', '12'], true)) {
            return response()->json([
                'success' => true,
                'is_senior' => false,
                'class_name' => $className,
                'streams' => [],
            ]);
        }

        $classRecord = DB::connection('dynamic')->table('class_name')->where('class_name', $className)->first();
        $classIds = array_values(array_filter([$classRecord ? $classRecord->id : null, $className]));

        $streamIds = DB::connection('dynamic')->table('subject_combinations')
            ->where('is_delete', 0)
            ->whereIn('class_id', $classIds)
            ->pluck('streams')
            ->filter()
            ->unique()
            ->toArray();

        $streamsQuery = DB::connection('dynamic')->table('streams')->where('is_delete', 0);
        if (!empty($streamIds) && !$request->boolean('all')) {
            $streamsQuery->whereIn('id', $streamIds);
        }
        $streams = $streamsQuery->orderBy('streams', 'asc')->get(['id', 'streams as stream_name']);

        return response()->json([
            'success' => true,
            'is_senior' => true,
            'class_name' => $className,
            'streams' => $streams,
        ]);
    }

    public function getStreamCombinations(Request $request)
    {
        $dbName = session('db_names') ?? request()->cookie('db_names') ?? request()->input('db_names');
        if (!empty($dbName)) {
            Config::set('database.connections.dynamic.database', $dbName);
            DB::purge('dynamic');
        }

        $className = trim((string) $request->input('class_name', ''));
        $streamId = $request->input('stream_id');

        if (!in_array($className, ['11', '12'], true) || empty($streamId)) {
            return response()->json([
                'success' => true,
                'is_senior' => in_array($className, ['11', '12'], true),
                'class_name' => $className,
                'stream_id' => $streamId,
                'combinations' => [],
            ]);
        }

        $classRecord = DB::connection('dynamic')->table('class_name')->where('class_name', $className)->first();
        $classIds = array_values(array_filter([$classRecord ? $classRecord->id : null, $className]));

        $combinations = DB::connection('dynamic')->table('subject_combinations')
            ->where('is_delete', 0)
            ->whereIn('class_id', $classIds)
            ->where('streams', $streamId)
            ->orderBy('combination_name', 'asc')
            ->get(['id', 'combination_name', 'alise_name']);

        return response()->json([
            'success' => true,
            'is_senior' => true,
            'class_name' => $className,
            'stream_id' => $streamId,
            'combinations' => $combinations,
        ]);
    }

    public function selection_process(){
         $all_inquiry = DB::connection('dynamic')->table('inquiry_registration')->orderBy('id', 'desc')->where("status","=", "i")->get();
         $totalPending = DB::connection('dynamic')->table('inquiry_registration')->select('*')->where('status', '=', 'i')->whereJsonContains('json_str->folloupdate_status', 'Pending')->get();
         $totalCancel = DB::connection('dynamic')->table('inquiry_registration')->select('*')->where('status', '=', 'i')->whereJsonContains('json_str->folloupdate_status', 'Cancel')->get();
         $totaladmission = DB::connection('dynamic')->table('student_registration')->where("status","=", "r")->get();
         $datas = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();

         $analytics = [
             'total' => $all_inquiry->count(),
             'form_selected' => 0,
             'pending' => 0,
             'form_submitted' => 0,
             'admission_on_hold' => 0,
             'rejected' => 0,
             'registered' => 0,
         ];
         foreach ($all_inquiry as $item) {
             $st = trim($item->save_status ?? '');
             if (empty($st) || strcasecmp($st, 'Pending') === 0) {
                 $analytics['pending']++;
             } elseif (strcasecmp($st, 'Form Selected') === 0) {
                 $analytics['form_selected']++;
             } elseif (strcasecmp($st, 'Form Submitted') === 0) {
                 $analytics['form_submitted']++;
             } elseif (strcasecmp($st, 'Admission On Hold') === 0) {
                 $analytics['admission_on_hold']++;
             } elseif (strcasecmp($st, 'Rejected (Interview)') === 0 || strcasecmp($st, 'Rejected') === 0) {
                 $analytics['rejected']++;
             } elseif (strcasecmp($st, 'Registered') === 0) {
                 $analytics['registered']++;
             }
         }

         return view('backend.student_registrations.selection_process',compact('all_inquiry','totalPending','totalCancel','totaladmission', 'datas', 'analytics'));
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

        // Base query without save_status so that the analytics breakdown displays all statuses for the active search
        $baseQuery = DB::connection('dynamic')->table('inquiry_registration')
            ->where("status", "=", "i");

        if(!empty($session_name)){
            $baseQuery->where("session_name","=", $session_name);
        }
        if(!empty($form_number)){
            $baseQuery->where("form_number","=", $form_number);
        }
        if(!empty($class_name)){
            $baseQuery->where("class_name","=", $class_name);
        }
        if(!empty($gender)){
            $baseQuery->where("gender","=", $gender);
        }
        if(!empty($studentname)){
            $baseQuery->where("student_name","like", '%' . $studentname . '%');
        }

        $analyticsItems = (clone $baseQuery)->select('save_status')->get();
        $analytics = [
            'total' => $analyticsItems->count(),
            'form_selected' => 0,
            'pending' => 0,
            'form_submitted' => 0,
            'admission_on_hold' => 0,
            'rejected' => 0,
            'registered' => 0,
        ];
        foreach ($analyticsItems as $item) {
            $st = trim($item->save_status ?? '');
            if (empty($st) || strcasecmp($st, 'Pending') === 0) {
                $analytics['pending']++;
            } elseif (strcasecmp($st, 'Form Selected') === 0) {
                $analytics['form_selected']++;
            } elseif (strcasecmp($st, 'Form Submitted') === 0) {
                $analytics['form_submitted']++;
            } elseif (strcasecmp($st, 'Admission On Hold') === 0) {
                $analytics['admission_on_hold']++;
            } elseif (strcasecmp($st, 'Rejected (Interview)') === 0 || strcasecmp($st, 'Rejected') === 0) {
                $analytics['rejected']++;
            } elseif (strcasecmp($st, 'Registered') === 0) {
                $analytics['registered']++;
            }
        }

        $records1 = clone $baseQuery;
        if(!empty($save_status)){
            if ($save_status === 'Pending') {
                $records1->where(function($q) {
                    $q->whereNull('save_status')->orWhere('save_status', '')->orWhere('save_status', 'Pending');
                });
            } else {
                $records1->where("save_status","=", $save_status);
            }
        }
        $records1->orderBy('id', 'desc');

        $all_inquiry = $records1->get();
        $classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        $datas = $classlist;
        $classname = $class_name;
        $totalPending = DB::connection('dynamic')->table('inquiry_registration')->select('*')->where('status', '=', 'i')->whereJsonContains('json_str->folloupdate_status', 'Pending')->get();
        $totalCancel = DB::connection('dynamic')->table('inquiry_registration')->select('*')->where('status', '=', 'i')->whereJsonContains('json_str->folloupdate_status', 'Cancel')->get();
        $totaladmission = DB::connection('dynamic')->table('student_registration')->where("status","=", "r")->get();
        return view('backend.student_registrations.selection_process',compact('all_inquiry','jsonArr', 'classlist', 'datas', 'classname', 'totalPending', 'totalCancel', 'totaladmission', 'analytics'));
    }

    
	public function student_registration_filer(Request $request)
	{
		$activeTab = $request->input('tab', 'current');
		$session_name = $request->input('session_name');
		$form_number = $request->input('form_number');
		$serial_number = $request->input('serial_number');
		$class_name = $request->input('classname');
		$studentname = $request->input('student_name');
		$application_for = $request->input('application_for');
		$scholar_no = $request->input('scholar_no');
		$parent_name = $request->input('parent_name');
		$mobile_number = $request->input('mobile_number');
		$quick_search = $request->input('quick_search') ?? $request->input('search');

		$jsonArr = [
			'tab' => $activeTab,
			'session_name' => $session_name,
			'student_name' => $studentname,
			'serial_number' => $serial_number,
			'class_name' => $class_name,
			'form_number' => $form_number,
			'application_for' => $application_for,
			'scholar_no' => $scholar_no,
			'parent_name' => $parent_name,
			'mobile_number' => $mobile_number,
			'quick_search' => $quick_search,
		];

		// Start query builder with active student registrations (status = 'r')
		if ($activeTab === 'next_year') {
			$records1 = $this->nextYearStudentRegistrationQuery()
				->where('status', '=', 'r')
				->orderBy('created_at', 'desc')
				->orderBy('id', 'desc');
		} else {
			$records1 = $this->activeStudentRegistrationQuery()
				->where('status', '=', 'r')
				->orderBy('created_at', 'desc')
				->orderBy('id', 'desc');
		}

		if (!empty($session_name)) {
			$sessionVariants = array_unique([
				trim($session_name),
				str_replace('-', '_', trim($session_name)),
				str_replace('_', '-', trim($session_name))
			]);
			$records1->where(function($q) use ($sessionVariants) {
				$q->whereIn('session_name', $sessionVariants);
				foreach ($sessionVariants as $sv) {
					$q->orWhereJsonContains('json_str->admission_session', $sv)
					  ->orWhereJsonContains('json_str->intended_session', $sv)
					  ->orWhereJsonContains('json_str->batch', $sv);
				}
			});
		}

		if (!empty($form_number)) {
			$records1->where('form_number', 'like', '%' . trim($form_number) . '%');
		}

		if (!empty($serial_number)) {
			$trimmedSerial = trim($serial_number);
			$records1->where(function ($q) use ($trimmedSerial) {
				$q->where('scholar_no', 'like', '%' . $trimmedSerial . '%')
				  ->orWhere('form_number', 'like', '%' . $trimmedSerial . '%')
				  ->orWhere('id', $trimmedSerial);
			});
		}

		if (!empty($class_name)) {
			$records1->where(function ($q) use ($class_name) {
				$q->where('class_name', trim($class_name))
				  ->orWhere('class_id', trim($class_name));
			});
		}

		if (!empty($studentname)) {
			$records1->where('student_name', 'like', '%' . trim($studentname) . '%');
		}

		if (!empty($scholar_no)) {
			$records1->where('scholar_no', 'like', '%' . trim($scholar_no) . '%');
		}

		if (!empty($parent_name)) {
			$pterm = trim($parent_name);
			$records1->where(function ($q) use ($pterm) {
				$q->where('json_str->fathername', 'like', '%' . $pterm . '%')
				  ->orWhere('json_str->student_father_name', 'like', '%' . $pterm . '%')
				  ->orWhere('json_str->mothername', 'like', '%' . $pterm . '%')
				  ->orWhere('json_str->student_mother_name', 'like', '%' . $pterm . '%');
			});
		}

		if (!empty($mobile_number)) {
			$mterm = trim($mobile_number);
			$records1->where(function ($q) use ($mterm) {
				$q->where('mobile_number', 'like', '%' . $mterm . '%')
				  ->orWhere('json_str->father_mobile', 'like', '%' . $mterm . '%')
				  ->orWhere('json_str->mother_mobile', 'like', '%' . $mterm . '%')
				  ->orWhere('json_str->phone_number', 'like', '%' . $mterm . '%');
			});
		}

		if (!empty($quick_search)) {
			$qterm = trim($quick_search);
			$records1->where(function ($q) use ($qterm) {
				$q->where('student_name', 'like', '%' . $qterm . '%')
				  ->orWhere('scholar_no', 'like', '%' . $qterm . '%')
				  ->orWhere('mobile_number', 'like', '%' . $qterm . '%')
				  ->orWhere('form_number', 'like', '%' . $qterm . '%')
				  ->orWhere('json_str->fathername', 'like', '%' . $qterm . '%')
				  ->orWhere('json_str->student_father_name', 'like', '%' . $qterm . '%')
				  ->orWhere('json_str->mothername', 'like', '%' . $qterm . '%')
				  ->orWhere('json_str->student_mother_name', 'like', '%' . $qterm . '%')
				  ->orWhere('json_str->father_mobile', 'like', '%' . $qterm . '%')
				  ->orWhere('json_str->mother_mobile', 'like', '%' . $qterm . '%');
			});
		}

		if (!empty($application_for)) {
			$records1->where('application_for', trim($application_for));
		}

		$all_inquiry = $records1->get();
		$classlist = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
		$archivedCount = DB::connection('dynamic')->table('student_registration')->where('is_archived', 1)->count();
		$currentCount = $this->activeStudentRegistrationQuery()->where('status', '=', 'r')->count();
		$nextYearCount = $this->nextYearStudentRegistrationQuery()->where('status', '=', 'r')->count();
		return view('backend.student_registrations.index', compact('all_inquiry', 'jsonArr', 'classlist', 'archivedCount', 'activeTab', 'currentCount', 'nextYearCount'));
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
