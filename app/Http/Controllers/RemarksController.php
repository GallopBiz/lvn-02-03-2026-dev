<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Remark;

class RemarksController extends Controller
{
    

    public function index(){



        // $stream = Remark::where('is_delete',0)->get();
        $stream = Remark::where('is_delete', 0)->paginate(2);
    //    $stream1 = Remark::paginate(5);
        return view('backend.AcademicsModules.remark',compact('stream'));
    }


    public function create(Request $request)
    {
        $validated = $request->validate([
            'remark' => 'required|string|max:255',
        ]);

        Remark::create([
            'remark' => $validated['remark'],
            'not_show' => $request->has('not_show') ? 'Yes' : 'No',
        ]);
    
        return redirect()->route('remarkmaster')->with('success', 'Remark has been created successfully.');
    }


    public function view($id){
        $stream_master = Remark::whereId($id)->get();
        $stream = Remark::orderBy('id', 'desc')->paginate(5);

        return view('backend.AcademicsModules.remark', compact('stream_master','stream'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer',
            'remark' => 'required|string|max:255',
        ]);

        $data = [
            'remark' => $validated['remark'],
            'not_show' => $request->has('not_show') ? 'Yes' : 'No',
        ];

        Remark::whereKey($validated['id'])->update($data);

        return redirect()->route('remarkmaster')->with('success', 'Remark has been updated successfully.');
    }

    
    public function remarkmaster_delete($id)
    {
        $a = explode('-',$id);
        $b = $a[1];        

        $deleted = Remark::whereKey($b)->update(['is_delete' => 1]);
        if($deleted){
            return redirect()->back()->with('success', 'Record successfully removed');
        }

        return redirect()->back()->with('error', 'Record not removed');
    }

    public function delete($id){
        $stream = Remark::findOrFail($id);
        $stream->delete();
        return redirect()->route('remarkmaster')->with('success','Deleted successfully.');
    }


}
