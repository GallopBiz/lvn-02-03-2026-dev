<?php

namespace App\Http\Controllers;
use DateTime;  // This will allow you to use DateTime without the backslash
use Illuminate\Http\Request;
use App\Models\AddVehial;
use App\Models\CommanModel;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\VehicleImport;
use Carbon\Carbon;

class AddVehical extends Controller
{
    public function index()
    {
        $vehicals = AddVehial::where('is_delete', 0)->get();
        return view('backend.Transport.addvehical', compact('vehicals'));
    }

	public function addvehical(Request $request)
	{
		$request->validate([
			'callno'    => 'required',
			'vehicel'   => 'required',
			'imei'      => 'required',
			'Machine'   => 'required',
			'gps_tracking_url' => 'nullable|url'
		]);

		$AddVehial = new AddVehial;

		$AddVehial->callno          = $request->callno;
		$AddVehial->vehicelno       = $request->vehicel;
		$AddVehial->vehiceltype     = $request->vehiceltype;
		$AddVehial->nature          = $request->Nature;
		$AddVehial->model           = $request->year;
		$AddVehial->purchase        = $request->dp;
		$AddVehial->capacity        = $request->capacity;
		$AddVehial->standard        = $request->StandardAvg;
		$AddVehial->imei            = $request->imei;
		$AddVehial->machine         = $request->Machine;
		$AddVehial->studentrelated  = $request->StudentRelated;
		$AddVehial->scrapped        = $request->Scrapped;

		// RTO Paper Fields (without file)
		$AddVehial->rtopaper        = $request->rtopaper;
		$AddVehial->validfrom       = $request->validfrom;
		$AddVehial->validto         = $request->validto;

		// Fitness Paper Fields (without file)
		$AddVehial->fitnesspaper        = $request->fitnesspaper;
		$AddVehial->fitness_validfrom   = $request->fitness_validfrom;
		$AddVehial->fitness_validto     = $request->fitness_validto;
		$AddVehial->gps_tracking_url = $request->gps_tracking_url;


		$AddVehial->save();

		return redirect('addvehical')->with('success', 'Record inserted successfully');
	}


    public function list()
	{
		$today = Carbon::today();
		$threshold = $today->copy()->addDays(7);

		$Vehicallist = AddVehial::where('is_delete', 0)
			->get()
			->map(function ($vehicle) use ($threshold) {
				if ($vehicle->validto && Carbon::parse($vehicle->validto)->lte($threshold)) {
					$vehicle->highlight = true;
				} else {
					$vehicle->highlight = false;
				}
				return $vehicle;
			});

		return view('backend.Transport.list', compact('Vehicallist'));
	}

    public function view($id)
    {
        $Vehicallist = AddVehial::where('id', $id)->get();
        $vehicals = AddVehial::where('is_delete', 0)->get();

        return view('backend.Transport.addvehical', compact('vehicals', 'Vehicallist'));
    }

    public function store(Request $request)
	{
		$data = [
			'callno'            => $request->callno,
			'vehicelno'         => $request->vehicel,
			'vehiceltype'       => $request->vehiceltype,
			'model'             => $request->year,
			'purchase'          => $request->dp,
			'capacity'          => $request->capacity,
			'standard'          => $request->StandardAvg,
			'imei'              => $request->imei,
			'machine'           => $request->Machine,
			'nature'            => $request->Nature,
			'studentrelated'    => $request->StudentRelated,
			'scrapped'          => $request->Scrapped,

			// RTO Paper Fields (no file)
			'rtopaper'          => $request->rtopaper,
			'validfrom'         => $request->validfrom,
			'validto'           => $request->validto,

			// Fitness Paper Fields (no file)
			'fitnesspaper'        => $request->fitnesspaper,
			'fitness_validfrom'   => $request->fitness_validfrom,
			'fitness_validto'     => $request->fitness_validto,
			'gps_tracking_url' => $request->gps_tracking_url,

		];

		AddVehial::whereId($request->id)->update($data);

		return redirect('addvehical')->with('success', 'Record updated successfully');
	}



    public function busstaff()
    {
        return view('backend.Transport.BusStaff');
    }

    public function addvehical_delete($id)
    {
        $a = explode('-', $id);
        $b = $a[1];        
        $c = $a[0];

        $delete_resp = CommanModel::soft_delete($c, ['id' => $b]);

        if ($delete_resp == 'TRUE') {
            return redirect()->back()->with('success', 'Record successfully removed');
        } else {
            return redirect()->back()->with('error', 'Record not removed');
        }
    }

    // Import Vehicles from CSV/Excel
    public function importView()
    {
        return view('backend.Transport.import');
    }

    public function import(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:csv,txt|max:2048',
    ]);

    $file = $request->file('file');
    $path = $file->getRealPath();

    // Read file
    $handle = fopen($path, "r");
    if ($handle !== FALSE) {
        // Skip header row
        fgetcsv($handle);

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Convert purchase date (dmy to ymd)
            $purchase = $this->convertDateToYMD($data[5]);

            // Insert data into the database
            AddVehial::create([
                'callno'         => $data[0] ?? null,
                'vehicelno'      => $data[1] ?? null,
                'vehiceltype'    => $data[2] ?? null,
                'nature'         => $data[3] ?? null,
                'model'          => $data[4] ?? null,
                'purchase'       => $purchase,
                'capacity'       => $data[6] ?? null,
                'standard'       => $data[7] ?? null,
                'imei'           => $data[8] ?? null,
                'machine'        => $data[9] ?? null,
                'studentrelated' => $data[10] ?? null,
                'scrapped'       => $data[11] ?? null,
                'is_delete'      => 0,
            ]);
        }
        fclose($handle);
    }

    return redirect()->back()->with('success', 'Vehicles imported successfully!');
	}

	/**
	 * Convert dmy format date to ymd format.
	 */
	private function convertDateToYMD($date)
	{
		// Check if the date is in the dmy format
		$date = trim($date); // Remove any extra spaces

		// Attempt to create a DateTime object from dmy format
		$d = DateTime::createFromFormat('d-m-Y', $date);
		
		// If valid, return the date in ymd format (Y-m-d)
		if ($d && $d->format('d-m-Y') === $date) {
			return $d->format('Y-m-d'); // Return date as Y-m-d (for MySQL compatibility)
		}

		// If invalid, return null (or you could return a default value or log the error)
		return null;
	}
	
	public function showImportForm()
    {
        return view('backend.Transport.import_vehicle');
    }
	
}
