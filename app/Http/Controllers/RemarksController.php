<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Models\Remark;
use App\Models\CommanModel;

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
        $data = $request->post();
        $data['not_show'] = $request->has('not_show') ? 'Yes' : 'No';
    
    
        // $data['entry_type'] = $request->input('entry_type');
    
        Remark::create($data);
    
        return redirect()->route('remarkmaster')->with('success', 'Remark has been created successfully.');
    }


    public function view($id){
        $stream_master = Remark::whereId($id)->get();
        $stream = Remark::orderBy('id', 'desc')->paginate(5);

        return view('backend.AcademicsModules.remark', compact('stream_master','stream'));
    }

    public function store(Request $request)
    {
        $data = [
            'remark' => $request->remark,
            // 'not_show' => $request->not_show,
        
            
        
            'not_show' => $request->has('not_show') ? 'Yes' : 'No',

            
        ];

        Remark::whereId($request->id)->update($data);

        return redirect()->route('remarkmaster')->with('success', 'Remark has been updated successfully.');
    }

    
    public function remarkmaster_delete($id)
    {
        // echo $id;
        // exit;
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
        $stream = Remark::findOrFail($id);
        $stream->delete();
        return redirect()->route('remarkmaster')->with('success','Deleted successfully.');
    }


}
