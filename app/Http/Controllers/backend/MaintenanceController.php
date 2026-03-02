<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Maintenanceheadmaster;
use App\Models\Maintenancegroupmaster;
use App\Models\CommanModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class MaintenanceController extends Controller
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

    public function index()
    {
        $maintenances = Maintenanceheadmaster::where('is_delete', 0)->get();
        $maintenancegs = Maintenancegroupmaster::where('is_delete', 0)->get();
        $select_main = Maintenancegroupmaster::all();
        return view('backend.Transport.maintenance-head-master', compact('select_main', 'maintenancegs', 'maintenances'));
    }

    public function view($id)
    {
        $maintenance_s = Maintenanceheadmaster::whereId($id)->get();
        $maintenances = Maintenanceheadmaster::orderBy('id', 'desc')->get();
        $maintenancegs = Maintenancegroupmaster::orderBy('id', 'desc')->get();
        $select_main = Maintenancegroupmaster::all();
        return view('backend.Transport.maintenance-head-master', compact('select_main', 'maintenancegs', 'maintenance_s', 'maintenances'));
    }

    public function editg($id)
    {
        $maintenance_groups = Maintenancegroupmaster::whereId($id)->get();
        $maintenancegs = Maintenancegroupmaster::orderBy('id', 'desc')->get();
        $maintenances = Maintenanceheadmaster::orderBy('id', 'desc')->get();
        return view('backend.Transport.maintenance-head-master', compact('maintenance_groups', 'maintenancegs', 'maintenances'));
    }

    public function store(Request $request)
    {
        if (!empty($request->id)) {
            Maintenanceheadmaster::updateOrCreate(
                ['id' => $request->id],
                [
                    'maintenance_group_name' => $request->maintenance_group_name,
                    'maintenance_head_name' => $request->maintenance_head_name
                ]
            );
        } else {
            Maintenanceheadmaster::create($request->post());
        }
        return redirect()->action([MaintenanceController::class, 'index'])->with('success', 'Maintenance Master has been saved successfully.');
    }

    public function storeg(Request $request)
    {
        if (!empty($request->id)) {
            Maintenancegroupmaster::updateOrCreate(
                ['id' => $request->id],
                [
                    'maintenance_group_name' => $request->maintenance_group_name
                ]
            );
        } else {
            Maintenancegroupmaster::create($request->post());
        }
        return redirect()->action([MaintenanceController::class, 'index'])->with('success', 'Maintenance Master has been saved successfully.');
    }

    public function maintenancegroupmaster_delete($id)
    {
        $a = explode('-', $id);
        $b = $a[1];
        $c = $a[0];
        $delete_resp = CommanModel::soft_delete($c, ['id' => $b]);
        if ($delete_resp == 'TRUE') {
            return redirect()->back()->with('success', 'Record successfully removed');
        } elseif ($delete_resp == 'FALSE') {
            return redirect()->back()->with('error', 'Record not removed');
        }
    }

    public function maintenanceheadpmaster_delete($id)
    {
        $a = explode('-', $id);
        $b = $a[1];
        $c = $a[0];
        $delete_resp = CommanModel::soft_delete($c, ['id' => $b]);
        if ($delete_resp == 'TRUE') {
            return redirect()->back()->with('success', 'Record successfully removed');
        } elseif ($delete_resp == 'FALSE') {
            return redirect()->back()->with('error', 'Record not removed');
        }
    }

    public function delete($id)
    {
        $rtopapers = Maintenanceheadmaster::findOrFail($id);
        $rtopapers->delete();
        return redirect()->route('maintenance-head-master')->with('success', 'Bus Stop has been Deleted successfully.');
    }

    public function busMaintenanceForm()
{
    $connection = session()->get('db'); // or cookie('db'), based on your setup
    $vehicles = DB::connection($connection)->table('vehicel')->select('vehicelno')->get();
    $groups = DB::connection($connection)->table('maintenancegroupmaster')->select('id', 'maintenance_group_name')->get();
    $heads = DB::connection($connection)->table('maintenanceheadmaster')->select('id', 'maintenance_head_name')->get();

    // Join maintenance history with group and head for display
    $history = DB::connection($connection)->table('bus_maintenance as bm')
        ->leftJoin('maintenancegroupmaster as gm', 'bm.maintenance_group_id', '=', 'gm.id')
        ->leftJoin('maintenanceheadmaster as hm', 'bm.maintenance_head_id', '=', 'hm.id')
        ->select('bm.*', 'gm.maintenance_group_name as group_name', 'hm.maintenance_head_name as head_name')
        ->orderBy('bm.created_at', 'desc')
        ->get();

    return view('backend.Transport.bus_maintenance_entry', [
        'vehicles' => $vehicles,
        'maintenance_groups' => $groups,
        'maintenance_heads' => $heads,
        'maintenances' => $history,
    ]);
}


    public function storeBusMaintenance(Request $request)
	{
		$data = $request->only([
			'vehicle_no',                // ✅ Use correct field name
			'maintenance_group_id',
			'maintenance_head_id',
			'maintenance_date',         // ✅ Optional: if storing date
			'amount',
			'remarks'                   // ✅ Use correct field name
		]);

		if ($request->hasFile('invoice_file')) {
			$file = $request->file('invoice_file');
			$filename = time() . '_' . $file->getClientOriginalName();
			$file->move(public_path('uploads/invoices'), $filename);
			$data['invoice_file'] = $filename;
		}

		$data['created_at'] = now();

		DB::connection('dynamic')->table('bus_maintenance')->insert($data);

		return redirect()->back()->with('success', 'Maintenance entry saved.');
	}


    public function deleteBusMaintenance($id)
    {
        DB::connection('dynamic')->table('bus_maintenance')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Maintenance entry deleted.');
    }
}

