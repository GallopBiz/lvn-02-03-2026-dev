<?php

namespace App\Http\Controllers\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use DB;
use App\Models\State;
use Illuminate\Support\Facades\Config;
use App\Models\Remark;

class RegistrationController extends Controller
{
    /**     
     * redirect admin after login
     *
     * @return \Illuminate\View\View
     */

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

    public function enquiryform()
    {   
        $data['states'] = State::get(["name", "id"]);
        $data['caste'] = DB::connection('dynamic')->table('caste_name')->get();
        $data['presentlyschool'] = DB::connection('dynamic')->table('presentlyschool')->get();
        $uid = 0; // Initialize $uid with a default value

		$lastInquiry = DB::connection('dynamic')
			->table('inquiry_registration')
			->orderBy('id', 'desc') // Order by ID in descending order to get the last record
			->first(); // Fetch only the last record directly

		if ($lastInquiry !== null) {
			$uid = $lastInquiry->id; // Assign the 'id' of the last record to $uid
		}
        $data['all_inquiry'] = DB::connection('dynamic')->table('inquiry_registration')->where('status','p')->get();
        $data['Userid'] = $uid + 1;
        $data['classlist'] = DB::connection('dynamic')->table('classes')->select('class_name')->distinct()->get();
        // $data['Remarks'] = Remark::get(["not_show", "No"]);
        $data['Remarks'] = DB::table('remarksmaster')->where('not_show','No')->get();
        return view('backend.inquiryregistration',$data);
    }

    public function schalars_all(){
        return view('backend.student_registrations.schalars_view');
    }
    public function Academics_all(){
        return view('backend.AcademicsModules.Academics_view');
    }

    public function hrms_all(){
        return view('backend.HRMS.hrms_view');
    }

    public function Transport_all(){
        return view('backend.Transport.Transport_view');
    }
    public function Fees_all(){
        return view('backend.Fees-module.fees_view');
    }
    public function dashboard(){
        return view('backend.Fees-module.fees_view');
    }

}
