<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Maintenanceheadmaster;
use App\Models\Routemaster;
use App\Models\Areamaster;
use App\Models\Busstop;
use App\Models\CommanModel;
use App\Models\Routeareabusmaster;
use App\Models\Maintenancegroupmaster;

class RouteController extends Controller
{
    public function index(){
        $maintenances = Maintenanceheadmaster::orderBy('id','desc')->get();
        $routemasters = Routemaster::where('is_delete',0)->get();
        $routeareabuss = Routeareabusmaster::where('is_delete',0)->get();
        $select_main = Routemaster::All();
        $select_main_area = Areamaster::All();
        $select_main_bus = Busstop::select('bus_stop_name')->get();
        return view('backend.Transport.route_master', compact('select_main','select_main_bus','select_main_area','routemasters','maintenances','routeareabuss'));
    }

    public function view($id){
        $route_s = Routemaster::whereId($id)->get();
        $maintenance_s = Maintenanceheadmaster::whereId($id)->get();
        $maintenances = Maintenanceheadmaster::orderBy('id','desc')->get();
        $routemasters = Routemaster::orderBy('id','desc')->get();
        $select_main = Routemaster::All();
        $select_main_area = Areamaster::All();
        return view('backend.Transport.route_master',compact('route_s','select_main','select_main_area','routemasters','maintenance_s','maintenances'));
    }

    public function store(Request $request){
        if (!empty($request->id)){
            Routemaster::updateorcreate([
                'id' => $request->id
            ],
            [
                'route_name' => $request->route_name
            ]);
        } else {
            Routemaster::create($request->post());
        }
        return redirect()->action([RouteController::class, 'index'])->with('success','Route Master has been Save successfully.');
    }

    public function delete($id){
        $rtopapers = Maintenanceheadmaster::findOrFail($id);
        $rtopapers->delete();
        return redirect()->route('maintenance-head-master')->with('success','Bus Stop has been Deleted successfully.');
    }

    public function ajax(Request $request){
        $area_name = $request->area_name;
        $areas = Busstop::select('bus_stop_name')->where('area_name','=',$area_name)->get();
        return json_encode($areas,1);
    }

    public function create(Request $request){
        if (!empty($request->id)){
            Routeareabusmaster::updateorcreate([
                'id' => $request->id
            ],
            [
                'route_name' => $request->route_name,
                'area_name' => $request->area_name,
                'bus_stop_name' => $request->bus_stop_name,
            ]);
        } else {
            Routeareabusmaster::create($request->post());
        }
        return redirect()->action([RouteController::class, 'index'])->with('success','Route Area Bus Master has been Save successfully.');
    }

    public function  route_name_delete($id)
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
    public function  route_delete($id)
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
    public function view_bus($id){
        // $route_s = Routemaster::whereId($id)->get();
        // $maintenance_s = Maintenanceheadmaster::whereId($id)->get();
        $maintenances = Maintenanceheadmaster::orderBy('id','desc')->get();
        $routeareabuss = Routeareabusmaster::orderBy('id','desc')->get();
        $routemasters = Routemaster::orderBy('id','desc')->get();
        $select_main = Routemaster::All();
        $select_main_area = Areamaster::All();
        $bus_s = Routeareabusmaster::select('*')->whereId($id)->first();
        $select_main_bus = Busstop::select('bus_stop_name')->where('area_name','=',$bus_s->area_name)->get();
        // echo '<pre>';
        // print_r($select_main_bus);exit;
        return view('backend.Transport.route_master',compact('select_main_bus','bus_s','routeareabuss','select_main','select_main_area','routemasters','maintenances'));
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
			if (!empty($data[0])) { // Ensure route_name is not empty
				RouteMaster::create([
					'route_name' => trim($data[0]),
					'is_delete'  => 0, // Default active
				]);
				$insertedRows++;
			}
		}

		fclose($handle);

		if ($insertedRows > 0) {
			return redirect()->back()->with('success', "$insertedRows route records imported successfully!");
		} else {
			return redirect()->back()->with('error', 'No valid records were imported.');
		}
	}
	
	public function importRouteAreaBus(Request $request)
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
			if (!empty($data[0]) || !empty($data[1]) || !empty($data[2])) { // Ensure at least one value exists
				RouteAreaBusMaster::create([
					'route_name'    => trim($data[0]) ?? null,
					'area_name'     => trim($data[1]) ?? null,
					'bus_stop_name' => trim($data[2]) ?? null,
					'is_delete'     => 0, // Default active
				]);
				$insertedRows++;
			}
		}

		fclose($handle);

		if ($insertedRows > 0) {
			return redirect()->back()->with('success', "$insertedRows route area bus records imported successfully!");
		} else {
			return redirect()->back()->with('error', 'No valid records were imported.');
		}
	}
	
}
