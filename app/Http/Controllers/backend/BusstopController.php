<?php

namespace App\Http\Controllers\backend;

use Illuminate\Http\Request;
use App\Models\Busstop;
use App\Models\Areamaster;
use App\Http\Controllers\Controller;
use App\Models\CommanModel;


class BusstopController extends Controller
{
    //
    public function index(){
        $busstops = Busstop::where('is_delete',0)->get();
        $select_main = Areamaster::All();
        return view('backend.bus_stop_index', compact('busstops','select_main'));
    }

    public function create(Request $request){
        Busstop::create($request->post());
        return redirect()->route('bus-stop')->with('success','Bus Stop has been created successfully.');
    }

    
    public function view($id){
        $areas = Busstop::whereId($id)->get();
        $busstops = Busstop::orderBy('id','desc')->get();
        $select_main = Areamaster::All();
        return view('backend.bus_stop_index',compact('select_main','busstops','areas'));
    }

    public function store(Request $request){
        $data = [
            'area_name' => $request->area_name,
            'bus_stop_name' => $request->bus_stop_name,
            'latitude' => $request->latitude,
            'langitude' => $request->langitude,
        ];
        Busstop::whereId($request->id)->update($data);
        return redirect()->route('bus-stop')->with('success','Bus Stop has been Updated successfully.');
    }

    public function  busstop_delete(Request $request)
    {
        // print_r($request->all());
        $table = $request->table_name;
        $delete_resp = CommanModel::soft_delete($table,['id'=>$request->delete_id]);
        if($delete_resp=='TRUE'){
            return redirect()->back()->with('success', 'Record successfully removed');
        }elseif($delete_resp=='FALSE'){
            return redirect()->back()->with('error', 'Record not removed');
        }
    }
    
    public function bs_soft_delete($id){
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

    public function delete($id){
        echo $id;
        exit;
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
    $request->validate([
        'file' => 'required|mimes:csv,txt|max:2048',
    ]);

    $file = $request->file('file');
    $path = $file->getRealPath();

    // Open file for reading
    $handle = fopen($path, "r");
    if ($handle !== FALSE) {
        // Skip header row
        fgetcsv($handle);

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Insert data into Busstop table
            Busstop::create([
                'area_name'     => $data[0] ?? null,  // First column: Area Name
                'bus_stop_name' => $data[1] ?? null,  // Second column: Bus Stop Name
                'latitude'      => $data[2] ?? null,  // Third column: Latitude
                'longitude'     => $data[3] ?? null,  // Fourth column: Longitude
                'is_delete'     => 0,                 // Default to active
            ]);
        }
        fclose($handle);
    }

    return redirect()->back()->with('success', 'Bus Stop data imported successfully!');
}

}
