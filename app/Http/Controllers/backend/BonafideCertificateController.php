<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class BonafideCertificateController extends Controller
{
    protected $db_name;

    public function __construct(Request $request)
    {
        if (!empty($request->session)) {
            $this->db_name = $request->session;
        } elseif (!empty($request->session_name)) {
            $this->db_name = $request->session_name;
        } elseif (isset($_COOKIE['selectedYear'])) {
            $this->db_name = $_COOKIE['selectedYear'];
        } else {
            $this->db_name = "2023_2024";
        }

        $dynamicConfig = Config::get("database.connections.dynamic");
        $dynamicConfig['database'] = $this->db_name;
        Config::set('database.connections.dynamic', $dynamicConfig);
        DB::reconnect('dynamic');
    }

    /**
     * Show the certificate form
     */
    public function showForm()
    {
        $data_student_name = DB::connection('dynamic')->table('student_registration')->select('student_name','id','scholar_no')->where('status','=','r')->get();

        return view('backend.bonafide',compact('data_student_name'));
    }

    /**
     * Generate the certificate based on student data
     */
    // public function generateCertificate(Request $request)
    // {
    //     $request->validate([
    //         'session' => 'required|string',
    //         'scholar_no' => 'required|string',
    //         'remarks' => 'required|string',  // Fix: make remarks field required
    //     ]);

    //     $student = DB::connection('dynamic')->table('student_registration')
    //         ->where('session_name', $request->session)
    //         ->where('scholar_no', $request->scholar_no)
    //         ->first();

    //     if (!$student) {
    //         return back()->with('error', 'Student not found.');
    //     }

    //     $json = json_decode($student->json_str, true);

    //     $placeholders = [
    //         '{{student_name}}' => $json['studentname'] ?? '',
    //         '{{father_name}}' => $json['fathername'] ?? '',
    //         '{{session}}' => $json['batch'] ?? '',
    //         '{{class}}' => $json['classname'] ?? '',
    //         '{{section}}' => $json['section_name'] ?? '',
    //         '{{scholar_no}}' => $student->scholar_no ?? '',
    //         '{{dob}}' => $json['student_dob'] ?? '',
    //         '{{current_address}}' => $json['present_address'] ?? '',
    //         '{{permanent_address}}' => $json['permanent_address'] ?? '',
    //     ];

    //     $output = str_replace(array_keys($placeholders), array_values($placeholders), $request->remarks);

    //     return view('backend.bonafide.generated', ['content' => $output]);
    // }
    public function generateCertificate(Request $request)
{
    $request->validate([
        'scholar_no' => 'required|string',
        'remarks' => 'required|string',
    ]);

    $student = DB::connection('dynamic')->table('student_registration')
        ->where('scholar_no', $request->scholar_no)
        ->first();

    if (!$student) {
        return back()->with('error', 'Student not found.');
    }

    $json = json_decode($student->json_str, true);
	
	$gender = strtolower($json['gender'] ?? '');

		if ($gender === 'female') {
			$gender_word = 'her';
			$relation = 'D/O'; // daughter of
			$he_she = 'she';
			$his_her = 'her';
		} else {
			$gender_word = 'his';
			$relation = 'S/O'; // son of
			$he_she = 'he';
			$his_her = 'his';
		}

    $merged_data = [
        'student_name'        => $json['studentname'] ?? '',
        'father_name'         => $json['fathername'] ?? '',
        'session'             => $json['batch'] ?? '',
        'class'               => $json['classname'] ?? '',
        'section'             => $json['section_name'] ?? '',
        'scholar_no'          => $student->scholar_no ?? '',
        'dob'                 => $json['student_dob'] ?? '',
        'permanent_address'   => $json['permanent_address'] ?? '',
        'local_address'       => $json['present_address'] ?? '',
        'fees_paid'           => $json['fees_paid'] ?? '0',
		'gender_word'         => $gender_word,
		'relation'            => $relation,
		'he_she'              => $he_she,
		'his_her'             => $his_her
		];
    $remarks = $request->remarks;
    $current_date = now()->format('d-m-Y');

    return view('backend.bonafide.generated', compact('merged_data', 'current_date','remarks'));
}

}
