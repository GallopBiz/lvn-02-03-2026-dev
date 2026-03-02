<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Areamaster;
use App\Models\CommanModel;
use Illuminate\Support\Facades\Log;

class AreaController extends Controller
{
    public function index()
    {
        $areas = Areamaster::where('is_delete', 0)->get();
        return view('backend.Transport.area_master', compact('areas'));
    }

    public function view($id)
    {
        $area_s = Areamaster::whereId($id)->get();
        $areas = Areamaster::orderBy('id', 'desc')->get();
        return view('backend.Transport.area_master', compact('area_s', 'areas'));
    }

    public function store(Request $request)
    {
        if (!empty($request->id)) {
            Areamaster::updateOrCreate(
                ['id' => $request->id],
                ['area_name' => $request->area_name]
            );
        } else {
            Areamaster::create($request->post());
        }
        return redirect()->action([AreaController::class, 'index'])->with('success', 'Area Master has been saved successfully.');
    }

    public function delete($id)
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

    // Import functionality
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
            // Insert data into Areamaster table
            Areamaster::create([
                'area_name' => $data[0] ?? null, // First column for area_name
                'is_delete' => 0,                // Default to active
            ]);
        }
        fclose($handle);
    }

    return redirect()->back()->with('success', 'Area data imported successfully!');
}
}
