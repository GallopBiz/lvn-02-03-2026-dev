<?php

namespace App\Http\Controllers\backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use DB;
use Illuminate\Support\Facades\Config;

class AdminController extends Controller
{
    /**
     * redirect admin after login
     *
     * @return \Illuminate\View\View
     */
    public $db_name = '';
    public function __construct(Request $request){
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
                $db_name = "2025_2026";
            }
            
        }
        // $db_name = "2023_2024";

        $dynamicConnectionName = 'dynamic';
        $dynamicConfig = Config::get("database.connections.{$dynamicConnectionName}");
        $dynamicConfig['database'] = $db_name;
        Config::set('database.connections.dynamic', $dynamicConfig);
        DB::reconnect('dynamic');
    // print_r($db_name);die();
    }
    public function dashboard()
    {   
       $currentSchoolYear = '2025_2026'; // Or $this->db_name if you're using dynamic session
			$results = DB::connection('dynamic')->select("
				SELECT status, COUNT(*) AS total 
				FROM inquiry_registration 
				WHERE session_name = ? 
				AND status IN ('p', 'i', 'r') 
				GROUP BY status
			", [$currentSchoolYear]);

			// Convert results to associative array: ['p' => 15, 'i' => 25, 'r' => 10]
			$counts = collect($results)->pluck('total', 'status')->all();

			$preenquirecount   = $counts['p'] ?? 0;
			$enquirecount      = $counts['i'] ?? 0;
			$registrationcount = $counts['r'] ?? 0;
			
		$records = DB::table('feesreceiptchallan')
            ->select([
                DB::raw("DATE(JSON_UNQUOTE(JSON_EXTRACT(str_json, '$.payment_date'))) as payment_day"),
                DB::raw("SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(str_json, '$.sub_total_received')) AS DECIMAL(10,2))) as total_received")
            ])
            ->groupBy(DB::raw("DATE(JSON_UNQUOTE(JSON_EXTRACT(str_json, '$.payment_date')))"))
            ->orderByDesc(DB::raw("DATE(JSON_UNQUOTE(JSON_EXTRACT(str_json, '$.payment_date')))"))
            ->limit(10)
            ->get();
			
		$totalSubTotalReceived = DB::table('feesreceiptchallan')
			->selectRaw("SUM(JSON_UNQUOTE(JSON_EXTRACT(str_json, '$.sub_total_received')) + 0) as total")
			->value('total');
			
		 $totalRegistrations = DB::connection('dynamic')
			->table('student_registration')
            ->where('is_archived', 0)
			->where('status', 'r')
			->where('session_name', $currentSchoolYear)
			->count();	
			
        $totalBuses = DB::table('vehicel')->distinct('vehicelno')->count('vehicle_no');
		
		// Get the count of unique scholar_no
		$uniqueScholarCount = DB::table('totalnextyear')
			->where('is_delete', 0)
			->distinct('scholar_no')
			->count('scholar_no');

        // print_r($registrationcount);exit;
        // $comman_model->insertData('inquiry_registration',$insertArr);
        return view('backend.index',compact('preenquirecount','enquirecount','registrationcount', 'records', 'totalSubTotalReceived', 'totalRegistrations', 'totalBuses', 'uniqueScholarCount'));
    }
	
	

}
