<?php

namespace App\Http\Controllers;
use App\Models\AddVehial;
use App\Http\Controllers\BusStaff;
use Illuminate\Http\Request;
use App\Models\BusStaf;
use Illuminate\Support\Facades\Config;
use App\Models\CommanModel;
use App\Models\Schedulemasterall;
use DB;

class BusStaff extends Controller
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
    public function index(){
        $busStafs = DB::connection('dynamic')->table('busstaff')->where('is_delete',0)->get();//BusStaf::where('is_delete',0)->get();

        $callno = DB::connection('dynamic')->table('vehicel')->select('callno')->distinct()->get();//AddVehial::select('callno')->distinct()->get(); 
        return view('backend.Transport.BusStaff', compact('busStafs','callno'));
    }

    public function create(Request $request){
        BusStaf::create($request->post());
        return redirect()->route('driver-conductor-master')->with('success',' has been created successfully.');
    }

    public function view($id){
        $bus_stafs = BusStaf::whereId($id)->get();
        $callno = AddVehial::select('callno')->distinct()->get(); 
        $busStafs = BusStaf::orderBy('id','desc')->get();
        $schedulemasteralls = Schedulemasterall::all();
        $currentDateTime = now(); // Get the current date and time
        
        foreach ($bus_stafs as $bus_staf) {
            $ename = $bus_staf->ename;
        
            foreach ($schedulemasteralls as $schedulemasterall) {
                $schedule_check_one = json_decode($schedulemasterall->schedule_check_one);
                $schedule_time_from = $schedulemasterall->schedule_time_from;
        
                // Compare the current time with the schedule_time_from
                if ($currentDateTime->format('H:i') === $schedule_time_from) {
                    foreach ($schedule_check_one as $item) {
                        if ($item->driver_name === $ename || $item->conductor_name === $ename) {
                            // The ename matches the driver_name and current time matches schedule_time_from
                            // echo "Match found: $ename\n";
                            return redirect()->route('driver-conductor-master')->with('error',' Driver is on the route try after some time');
                        }
                    }
                }
            }
        }
        return view('backend.Transport.BusStaff',compact('bus_stafs','busStafs','callno'));
    
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
            'call_no' => $request->callno,
            'offical_mobile_no' => $request->offical_mobile_no,
			'emergency_contact_no' => $request->emergency_contact_no,
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
    public function busstaff_delete($id)
    {
        // echo $id;
        $a = explode('-',$id);
        $b = $a[1];        
        $c = $a[0];
        $delete_resp = CommanModel::soft_delete($c,['id'=>$b]);
        if($delete_resp=='TRUE'){
            return redirect()->back()->with('success', 'Record successfully removed');
        }elseif($delete_resp=='FALSE'){
            return redirect()->back()->with('error', 'Record not removed');
        }
    }
	
		public function import(Request $request)
	{
		// Validate file
		$request->validate([
			'file' => 'required|mimes:csv,txt|max:2048',
		]);

		$file = $request->file('file');
		$path = $file->getRealPath();

		// Check if the file exists and is readable
		if (!file_exists($path) || !is_readable($path)) {
			return redirect()->back()->with('error', 'Uploaded file is not readable.');
		}

		// Open the file
		$handle = fopen($path, "r");
		if ($handle === FALSE) {
			return redirect()->back()->with('error', 'Unable to open CSV file.');
		}

		// Read the header row
		$header = fgetcsv($handle);
		if (!$header) {
			fclose($handle);
			return redirect()->back()->with('error', 'Invalid CSV file format.');
		}

		// Process rows
		$insertedRows = 0;
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			BusStaf::create([
				'role'               => trim($data[0]) ?? null,
				'ename'              => trim($data[1]) ?? null,
				'mobile_number'      => trim($data[2]) ?? null,
				'aadhar_number'      => trim($data[3]) ?? null,
				'sssmid'             => trim($data[4]) ?? null,
				'current_address'    => trim($data[5]) ?? null,
				'parmanent_address'  => trim($data[6]) ?? null,
				'license_no'         => trim($data[7]) ?? null,
				'license_expire'     => trim($data[8]) ?? null,
				'license_lssue'      => trim($data[9]) ?? null,
				'voter_id_no'        => trim($data[10]) ?? null,
				'joining_date'       => trim($data[11]) ?? null,
				'leaving_date'       => trim($data[12]) ?? null,
				'leaving_date1'      => trim($data[13]) ?? null,
				'call_no'            => trim($data[14]) ?? null,
				'offical_mobile_no'  => trim($data[15]) ?? null,
				'emergency_contact_no' => trim($data[18]) ?? null, // <--- NEW
				'remarks'            => trim($data[16]) ?? null,
				'healthstatus'       => trim($data[17]) ?? null,
				'is_delete'          => 0 // Default active
			]);

			$insertedRows++;
		}
		
		fclose($handle);

		if ($insertedRows > 0) {
			return redirect()->back()->with('success', "$insertedRows bus staff records imported successfully!");
		} else {
			return redirect()->back()->with('error', 'No valid records were imported.');
		}
	}

}
